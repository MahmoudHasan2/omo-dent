<?php
/**
 * OMO.DENT — shared helper functions
 * ------------------------------------------------------------------
 * data/content-de.json  = Deutsch (Hauptsprache, bestimmt die Struktur)
 * data/content-en.json  = English   ┐
 * data/content-ar.json  = العربية   ├ nur Übersetzungen, keine Struktur
 * data/content-ku.json  = Kurdî     ┘
 *
 * Listen (Leistungen, FAQs, Galerie, Team) haben feste IDs, damit eine
 * Übersetzung IMMER beim richtigen Eintrag landet — auch wenn im
 * Dashboard Einträge gelöscht, hinzugefügt oder verschoben werden.
 */

define('DATA_DIR', __DIR__ . '/../data');
define('CONTENT_PATH', DATA_DIR . '/content-de.json');

/* =================================================================
   SPRACHEN
   ================================================================= */

function all_langs()       { return ['de', 'en', 'ar', 'ku']; }
function translation_langs(){ return ['en', 'ar', 'ku']; }
/** Sprachen, die im Sprachumschalter der Website angezeigt werden */
function switcher_langs()  { return ['de' => 'DE', 'en' => 'EN', 'ar' => 'AR']; }

function lang_label($code) {
    $map = ['de' => 'Deutsch', 'en' => 'Englisch', 'ar' => 'Arabisch', 'ku' => 'Kurdisch'];
    return $map[$code] ?? $code;
}

function current_lang() {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $supported = all_langs();
    if (isset($_GET['lang']) && in_array($_GET['lang'], $supported, true)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    $lang = $_SESSION['lang'] ?? 'de';
    return in_array($lang, $supported, true) ? $lang : 'de';
}

function is_rtl_lang($lang) {
    return $lang === 'ar';
}

/** lang="…" dir="…" für das <html>-Tag */
function html_attrs() {
    $l = current_lang();
    return 'lang="' . e($l) . '" dir="' . (is_rtl_lang($l) ? 'rtl' : 'ltr') . '"';
}

/** Link zum Sprachwechsel, der alle anderen Parameter (z. B. ?s=3) behält */
function lang_url($code) {
    $params = $_GET;
    $params['lang'] = $code;
    return '?' . http_build_query($params);
}

/* =================================================================
   AUSGABE-HELFER (Escaping + Bidi-Schutz für Arabisch)
   ================================================================= */

function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

/**
 * Text für die Seite ausgeben: escaped, Zeilenumbrüche → <br>.
 * Bei Arabisch (RTL) werden Zahlen/Uhrzeiten/Telefonnummern isoliert,
 * damit sie NICHT gespiegelt werden (z. B. "09:00–13:00" bleibt so).
 * Zeilen ganz ohne arabische Schrift (z. B. "Musterstraße 1")
 * werden komplett als LTR isoliert.
 */
function x($str) {
    $str = str_replace(["\r\n", "\r"], "\n", (string)$str);
    $lines = explode("\n", $str);
    $rtl = is_rtl_lang(current_lang());
    $out = [];
    foreach ($lines as $line) {
        $out[] = $rtl ? bidi_line($line) : e($line);
    }
    return implode('<br>', $out);
}

function bidi_line($line) {
    if (trim($line) === '') return '';
    // Keine arabischen Buchstaben → ganze Zeile LTR isolieren
    if (!preg_match('/[\x{0590}-\x{08FF}\x{FB1D}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $line)) {
        return '<bdi dir="ltr">' . e($line) . '</bdi>';
    }
    // URLs / E-Mails isolieren
    $parts = preg_split('/((?:https?:\/\/|www\.)\S+|[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,})/u', $line, -1, PREG_SPLIT_DELIM_CAPTURE);
    $html = '';
    foreach ($parts as $i => $part) {
        if ($i % 2 === 1) {
            $html .= '<bdi dir="ltr">' . e($part) . '</bdi>';
            continue;
        }
        // Zahlenfolgen (Telefon, Uhrzeit, Datum, Bereiche 3-4, 1–3) isolieren
        $pieces = preg_split('/([+(]?\d(?:[\d\s.,:\/()\-–—+]*\d)?)/u', $part, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($pieces as $j => $piece) {
            $html .= ($j % 2 === 1) ? '<bdi dir="ltr">' . e($piece) . '</bdi>' : e($piece);
        }
    }
    return $html;
}

/** Immer LTR (Telefon, Fax, E-Mail, Straße, PLZ) — in jeder Sprache korrekt */
function ltr($str) {
    if ((string)$str === '') return '';
    return '<bdi dir="ltr" class="ltr">' . e($str) . '</bdi>';
}

function tel_href($phone) {
    $digits = preg_replace('/[^0-9+]/', '', (string)$phone);
    if ($digits !== '' && $digits[0] === '0' && (strlen($digits) < 2 || $digits[1] !== '0')) {
        $digits = '+49' . substr($digits, 1);
    }
    return 'tel:' . $digits;
}

/** Google-Maps-Link in der Sprache des Besuchers */
function map_embed_url($url) {
    $l = current_lang();
    if (preg_match('/([?&])hl=[a-z\-]+/i', $url)) {
        return preg_replace('/([?&])hl=[a-z\-]+/i', '${1}hl=' . $l, $url);
    }
    return $url . (strpos($url, '?') === false ? '?' : '&') . 'hl=' . $l;
}

/** Kleiner <style>-Block für RTL / Zahlen / Rechtstexte — auf jeder Seite */
function i18n_head() {
    return '<style>
bdi.ltr,.ltr{direction:ltr;unicode-bidi:isolate;}
[dir="rtl"] input[type=tel],[dir="rtl"] input[type=email]{direction:ltr;text-align:right;}
[dir="rtl"] .icon-dir{transform:scaleX(-1);}
.legal-content h2{margin-top:2rem;}
.legal-content h3{margin-top:1.4rem;}
.legal-content p{margin:0 0 1rem;}
.legal-content a{color:var(--teal);word-break:break-word;}
.contact-fax{font-size:.9em;opacity:.85;}
</style>';
}

/* =================================================================
   INHALTE LADEN
   ================================================================= */

function read_json_file($path) {
    if (!file_exists($path)) return [];
    $d = json_decode((string)file_get_contents($path), true);
    return is_array($d) ? $d : [];
}

function write_json_file($path, $data) {
    return @file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
}

function new_id($prefix) {
    return $prefix . substr(bin2hex(random_bytes(6)), 0, 8);
}

/** Deutsche Master-Inhalte (mit allen Standardwerten) */
function load_content() {
    static $content = null;
    if ($content === null) {
        $raw = read_json_file(CONTENT_PATH);
        $content = apply_content_defaults($raw, $changed);
        // Einmalige automatische Umstellung (IDs, Rechtstexte) direkt speichern,
        // damit IDs dauerhaft stabil bleiben.
        if ($changed && !empty($raw)) {
            write_json_file(CONTENT_PATH, $content);
        }
    }
    return $content;
}

function load_translation($lang) {
    if (!in_array($lang, translation_langs(), true)) return [];
    return read_json_file(DATA_DIR . '/content-' . $lang . '.json');
}

/** Inhalte in der Sprache des Besuchers */
function load_site_content() {
    static $cache = [];
    $lang = current_lang();
    if (!isset($cache[$lang])) {
        $de = load_content();
        $cache[$lang] = ($lang === 'de') ? $de : localize_content($de, load_translation($lang));
    }
    return $cache[$lang];
}

/* =================================================================
   STANDARDWERTE + AUTOMATISCHE MIGRATION
   ================================================================= */

function apply_content_defaults($content, &$changed = false) {
    $changed = false;
    if (!is_array($content)) $content = [];

    // ---- site ----
    if (!isset($content['site']) || !is_array($content['site'])) $content['site'] = [];
    $siteDefaults = [
        'practice_name' => 'OMO.DENT', 'practice_tagline' => '', 'logo_text_1' => 'OMO.', 'logo_text_2' => 'DENT',
        'doctor_name' => '', 'doctor_title' => '', 'logo_image' => '', 'favicon' => 'pngegg.png',
        'phone' => '', 'fax' => '', 'email' => '', 'website' => '',
        'address_line1' => '', 'address_line2' => '', 'address_line3' => '',
        'hours_line1' => '', 'hours_line2' => '', 'hours_line3' => '',
        'footer_tagline' => '', 'copyright' => '', 'map_embed' => '', 'loader_enabled' => '1',
    ];
    foreach ($siteDefaults as $k => $v) {
        if (!array_key_exists($k, $content['site'])) $content['site'][$k] = $v;
    }
    if ($content['site']['favicon'] === '') $content['site']['favicon'] = 'pngegg.png';
    if (!isset($content['site']['colors']) || !is_array($content['site']['colors'])) $content['site']['colors'] = [];
    $content['site']['colors'] += [
        'primary_blue' => '#1B4F72', 'primary_teal' => '#2E8B78', 'accent_green' => '#3D9B82',
        'background' => '#F0F4F8', 'text_primary' => '#2C3E50', 'text_secondary' => '#5D6D7E', 'surface' => '#FFFFFF',
    ];

    // ---- nav ----
    if (!isset($content['nav']) || !is_array($content['nav'])) $content['nav'] = [];
    $content['nav'] += ['home' => 'Startseite', 'leistungen' => 'Leistungen', 'ueber_mich' => 'Über mich', 'team' => 'Unser Team'];

    // ---- ui (feste Beschriftungen) ----
    if (!isset($content['ui']) || !is_array($content['ui'])) $content['ui'] = [];
    foreach (ui_defaults() as $k => $def) {
        if (!isset($content['ui'][$k]) || trim((string)$content['ui'][$k]) === '') $content['ui'][$k] = $def[0];
    }

    // ---- home ----
    if (!isset($content['home']) || !is_array($content['home'])) $content['home'] = [];
    foreach (['welcome_title','hero_title','hero_text','hero_button','hero_image','intro_eyebrow','intro_title','intro_closing',
              'services_title','services_subtitle','services_link_text','about_preview_title','about_preview_text','about_preview_button',
              'contact_title','contact_subtitle','contact_info_title','form_title','form_subtitle'] as $k) {
        if (!isset($content['home'][$k])) $content['home'][$k] = '';
    }
    $content['home']['features'] = array_values(is_array($content['home']['features'] ?? null) ? $content['home']['features'] : []);
    $content['home']['intro_paragraphs'] = array_values(is_array($content['home']['intro_paragraphs'] ?? null) ? $content['home']['intro_paragraphs'] : []);

    // ---- leistungen ----
    if (!isset($content['leistungen']) || !is_array($content['leistungen'])) $content['leistungen'] = [];
    $content['leistungen'] += ['hero_title' => '', 'hero_text' => '', 'tech_title' => '', 'tech_image' => '', 'tech_image_secondary' => '', 'tech_paragraphs' => [], 'services' => []];
    $content['leistungen']['tech_paragraphs'] = array_values((array)$content['leistungen']['tech_paragraphs']);
    $services = is_array($content['leistungen']['services']) ? $content['leistungen']['services'] : [];
    if (array_keys($services) !== range(0, count($services) - 1) && count($services)) $changed = true;
    $services = array_values($services);
    foreach ($services as &$svc) {
        if (!is_array($svc)) $svc = [];
        if (empty($svc['id'])) { $svc['id'] = new_id('s'); $changed = true; }
        $svc += ['image' => '', 'title' => '', 'subtitle' => '', 'summary' => '', 'what_is' => '', 'approach' => '', 'faqs' => [], 'gallery' => []];
        $svc['faqs'] = array_values(is_array($svc['faqs']) ? $svc['faqs'] : []);
        foreach ($svc['faqs'] as &$fq) {
            $fq += ['q' => '', 'a' => ''];
            if (empty($fq['id'])) { $fq['id'] = new_id('f'); $changed = true; }
        }
        unset($fq);
        $svc['gallery'] = array_values(is_array($svc['gallery']) ? $svc['gallery'] : []);
        foreach ($svc['gallery'] as &$g) {
            $g += ['image' => '', 'caption' => ''];
            if (empty($g['id'])) { $g['id'] = new_id('g'); $changed = true; }
        }
        unset($g);
    }
    unset($svc);
    $content['leistungen']['services'] = $services;

    // ---- ueber_mich ----
    if (!isset($content['ueber_mich']) || !is_array($content['ueber_mich'])) $content['ueber_mich'] = [];
    $content['ueber_mich'] += ['photo' => '', 'intro_title' => '', 'hero_title' => '', 'quote' => '', 'credentials_title' => '', 'credentials' => [], 'features' => [], 'paragraphs' => []];
    foreach (['credentials', 'features', 'paragraphs'] as $k) $content['ueber_mich'][$k] = array_values((array)$content['ueber_mich'][$k]);

    // ---- team ----
    if (!isset($content['team']) || !is_array($content['team'])) $content['team'] = ['hero_title' => 'Unser Team', 'hero_text' => '', 'members' => []];
    $content['team'] += ['hero_title' => '', 'hero_text' => '', 'members' => []];
    $members = is_array($content['team']['members']) ? $content['team']['members'] : [];
    if (array_keys($members) !== range(0, count($members) - 1) && count($members)) $changed = true;
    $members = array_values($members);
    foreach ($members as &$m) {
        $m += ['photo' => '', 'name' => '', 'role' => '', 'description' => ''];
        if (empty($m['id'])) { $m['id'] = new_id('m'); $changed = true; }
    }
    unset($m);
    $content['team']['members'] = $members;

    // ---- pdf_downloads ----
    if (!isset($content['pdf_downloads']) || !is_array($content['pdf_downloads'])) $content['pdf_downloads'] = [];
    $content['pdf_downloads'] += ['button_text' => 'Formulare (PDF)', 'button_bg' => '#2E8B78', 'button_text_color' => '#FFFFFF', 'items' => []];
    $content['pdf_downloads']['items'] = array_values((array)$content['pdf_downloads']['items']);
    while (count($content['pdf_downloads']['items']) < 4) {
        $content['pdf_downloads']['items'][] = ['name' => '', 'icon' => 'file-text', 'file' => ''];
    }

    // ---- Rechtstexte: altes HTML → Abschnitte ----
    foreach (['impressum' => 'Impressum', 'datenschutz' => 'Datenschutzerklärung'] as $sec => $title) {
        if (!isset($content[$sec]) || !is_array($content[$sec])) $content[$sec] = ['title' => $title];
        if (!isset($content[$sec]['title'])) $content[$sec]['title'] = $title;
        if (!isset($content[$sec]['blocks']) || !is_array($content[$sec]['blocks'])) {
            $content[$sec]['blocks'] = html_to_blocks($content[$sec]['html'] ?? '');
            $changed = true;
        }
        if (array_key_exists('html', $content[$sec])) { unset($content[$sec]['html']); $changed = true; }
        $content[$sec]['blocks'] = normalize_blocks($content[$sec]['blocks']);
    }

    return $content;
}

/* =================================================================
   RECHTSTEXTE (Impressum / Datenschutz) als Abschnitte
   Abschnitt = { level: "h2"|"h3", title: "…", lines: ["…", "…"] }
   Eine leere Zeile = Absatz-Abstand.
   ================================================================= */

function normalize_blocks($blocks) {
    $out = [];
    foreach ((array)$blocks as $b) {
        if (!is_array($b)) continue;
        $level = (($b['level'] ?? 'h2') === 'h3') ? 'h3' : 'h2';
        $title = trim((string)($b['title'] ?? ''));
        $lines = [];
        foreach ((array)($b['lines'] ?? []) as $l) $lines[] = rtrim((string)$l);
        // führende/abschließende Leerzeilen entfernen, doppelte zusammenfassen
        $clean = [];
        foreach ($lines as $l) {
            if (trim($l) === '' && (empty($clean) || trim(end($clean)) === '')) continue;
            $clean[] = trim($l) === '' ? '' : $l;
        }
        while (!empty($clean) && trim(end($clean)) === '') array_pop($clean);
        if ($title === '' && empty($clean)) continue;
        $out[] = ['level' => $level, 'title' => $title, 'lines' => $clean];
    }
    return $out;
}

function html_to_blocks($html) {
    $html = (string)$html;
    if (trim(strip_tags($html)) === '') return [];
    $blocks = [];
    $cur = null;
    preg_match_all('/<(h2|h3|p)\b[^>]*>(.*?)<\/\1>/is', $html, $m, PREG_SET_ORDER);
    foreach ($m as $el) {
        $tag = strtolower($el[1]);
        $inner = $el[2];
        if ($tag === 'h2' || $tag === 'h3') {
            if ($cur) $blocks[] = $cur;
            $cur = ['level' => $tag, 'title' => html_entity_decode(trim(strip_tags($inner)), ENT_QUOTES, 'UTF-8'), 'lines' => []];
            continue;
        }
        if (!$cur) $cur = ['level' => 'h2', 'title' => '', 'lines' => []];
        $pieces = preg_split('/<br\s*\/?>/i', $inner);
        $added = false;
        foreach ($pieces as $p) {
            $t = html_entity_decode(strip_tags($p), ENT_QUOTES, 'UTF-8');
            $t = trim(preg_replace('/[ \t\x{00A0}]+/u', ' ', $t));
            if ($t === '') continue;
            if (!$added && !empty($cur['lines'])) $cur['lines'][] = '';
            $cur['lines'][] = $t;
            $added = true;
        }
    }
    if ($cur) $blocks[] = $cur;
    return normalize_blocks($blocks);
}

/** Abschnitte als HTML ausgeben (Links / E-Mails werden automatisch klickbar) */
function render_blocks($blocks) {
    $html = '';
    foreach (normalize_blocks($blocks) as $b) {
        if ($b['title'] !== '') {
            $html .= '<' . $b['level'] . '>' . x($b['title']) . '</' . $b['level'] . '>';
        }
        $para = [];
        $flush = function () use (&$para, &$html) {
            if ($para) { $html .= '<p>' . implode('<br>', $para) . '</p>'; $para = []; }
        };
        foreach ($b['lines'] as $line) {
            if (trim($line) === '') { $flush(); continue; }
            $para[] = linkify(x($line));
        }
        $flush();
    }
    return $html;
}

function linkify($html) {
    // Arbeitet auf bereits escaptem HTML (nur Textknoten/bdi)
    $html = preg_replace_callback('/(?<![\w\/@])((?:https?:\/\/|www\.)[^\s<]+[^\s<.,;:!?)])/u', function ($m) {
        $url = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
        $href = preg_match('/^https?:\/\//i', $url) ? $url : 'https://' . $url;
        return '<a href="' . e($href) . '" target="_blank" rel="noopener">' . $m[1] . '</a>';
    }, $html);
    $html = preg_replace('/(?<![\w.\-])([A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,})/u', '<a href="mailto:$1">$1</a>', $html);
    return $html;
}

/* =================================================================
   FESTE BESCHRIFTUNGEN (UI) — Deutsch als Standard
   [Standardtext, Gruppe, Beschreibung im Dashboard]
   Platzhalter: {practice} {title} {doctor}
   ================================================================= */

function ui_defaults() {
    return [
        // Kopf & Fuß
        'nav_appointment'   => ['Termin', 'Kopf- & Fußzeile', 'Button „Termin" im Menü'],
        'menu_toggle'       => ['Menü öffnen', 'Kopf- & Fußzeile', 'Bildschirmleser-Text Handy-Menü'],
        'footer_contact'    => ['Kontakt', 'Kopf- & Fußzeile', 'Überschrift Kontakt (Fußzeile)'],
        'footer_hours'      => ['Öffnungszeiten', 'Kopf- & Fußzeile', 'Überschrift Öffnungszeiten (Fußzeile)'],
        'footer_privacy'    => ['Datenschutz', 'Kopf- & Fußzeile', 'Link Datenschutz'],
        'footer_imprint'    => ['Impressum', 'Kopf- & Fußzeile', 'Link Impressum'],
        'label_address'     => ['Adresse', 'Kopf- & Fußzeile', 'Bezeichnung Adresse'],
        'label_phone'       => ['Telefon', 'Kopf- & Fußzeile', 'Bezeichnung Telefon'],
        'label_fax'         => ['Fax', 'Kopf- & Fußzeile', 'Bezeichnung Fax'],
        'label_email'       => ['E-Mail', 'Kopf- & Fußzeile', 'Bezeichnung E-Mail'],
        // Startseite
        'hero_img_alt'      => ['Familie beim Zahnarzt', 'Startseite', 'Bildbeschreibung Hero-Bild'],
        'stat_services'     => ["Unsere\nLeistungen", 'Startseite', 'Zahl-Badge am Hero-Bild'],
        'stat_qualifications' => ['Qualifikationen', 'Startseite', 'Zahl-Karte „Qualifikationen"'],
        'stat_personal'     => ['Persönliche Betreuung', 'Startseite', 'Karte „1:1"'],
        'about_eyebrow'     => ['Über {practice}', 'Startseite', 'Kleine Überschrift über „Über mich"-Vorschau'],
        'services_eyebrow'  => ['Unsere Leistungen', 'Startseite', 'Kleine Überschrift über Leistungen-Vorschau'],
        'read_more'         => ['Mehr erfahren', 'Startseite', 'Link „Mehr erfahren" (auch Leistungen-Seite)'],
        'map_title'         => ['{practice} Standort', 'Startseite', 'Titel der Karte'],
        'map_consent_text'  => ['Zum Laden der Karte wird eine Verbindung zu Google hergestellt. Dabei werden u. a. Ihre IP-Adresse an Google (auch in die USA) übermittelt. Mehr dazu in unserer Datenschutzerklärung.', 'Startseite', 'Hinweis vor dem Laden der Google-Karte'],
        'map_consent_btn'   => ['Karte laden', 'Startseite', 'Knopf „Karte laden"'],
        'map_open_link'     => ['Route in Google Maps öffnen', 'Startseite', 'Link zu Google Maps (neues Fenster)'],
        'pdf_modal_title'   => ['Dokumente herunterladen', 'Startseite', 'Titel im PDF-Fenster'],
        'pdf_soon'          => ['Bald verfügbar', 'Startseite', 'Hinweis: PDF noch nicht hochgeladen'],
        'pdf_not_uploaded'  => ['Diese Datei wurde noch nicht hochgeladen', 'Startseite', 'Tooltip: PDF fehlt'],
        'close'             => ['Schließen', 'Startseite', 'Schließen-Knopf'],
        // Kontaktformular
        'form_name'         => ['Vorname / Nachname', 'Kontaktformular', 'Feld Name'],
        'form_first'        => ['Vorname', 'Kontaktformular', 'Platzhalter Vorname'],
        'form_last'         => ['Nachname', 'Kontaktformular', 'Platzhalter Nachname'],
        'form_phone'        => ['Telefon', 'Kontaktformular', 'Feld Telefon'],
        'form_email'        => ['E-Mail-Adresse', 'Kontaktformular', 'Feld E-Mail'],
        'form_email_ph'     => ['ihre.email@beispiel.de', 'Kontaktformular', 'Platzhalter E-Mail'],
        'form_patient'      => ['Sind Sie bereits Patient?', 'Kontaktformular', 'Feld Patient'],
        'form_select'       => ['Option auswählen', 'Kontaktformular', 'Auswahl-Platzhalter'],
        'form_yes'          => ['Ja, ich bin bereits Patient', 'Kontaktformular', 'Auswahl Ja'],
        'form_no'           => ['Nein, ich bin noch kein Patient', 'Kontaktformular', 'Auswahl Nein'],
        'form_file'         => ['Datei hochladen', 'Kontaktformular', 'Feld Datei'],
        'form_file_hint'    => ['Falls Sie uns ein Dokument senden möchten, können Sie es hier hochladen.', 'Kontaktformular', 'Hinweis Datei'],
        'form_file_add'     => ['Datei hinzufügen', 'Kontaktformular', 'Knopf Datei hinzufügen'],
        'form_message'      => ['Nachricht', 'Kontaktformular', 'Feld Nachricht'],
        'form_message_ph'   => ['Wie können wir Ihnen helfen?', 'Kontaktformular', 'Platzhalter Nachricht'],
        'form_privacy'      => ['Ihre Angaben und hochgeladenen Dokumente (z. B. Befunde oder Röntgenbilder) verwenden wir ausschließlich zur Bearbeitung Ihrer Anfrage. Näheres in unserer', 'Kontaktformular', 'Datenschutz-Hinweis über dem Absende-Knopf'],
        'form_privacy_link' => ['Datenschutzerklärung', 'Kontaktformular', 'Link-Text Datenschutzerklärung im Formular'],
        'form_submit'       => ['Nachricht senden', 'Kontaktformular', 'Absende-Knopf'],
        'msg_sending'       => ['Wird gesendet …', 'Kontaktformular', 'Meldung: wird gesendet'],
        'msg_success'       => ['Ihre Anfrage wurde erfolgreich gesendet.', 'Kontaktformular', 'Meldung: erfolgreich'],
        'msg_required'      => ['Bitte füllen Sie alle Pflichtfelder aus.', 'Kontaktformular', 'Meldung: Pflichtfelder'],
        'msg_email'         => ['Bitte geben Sie eine gültige E-Mail-Adresse ein.', 'Kontaktformular', 'Meldung: E-Mail ungültig'],
        'msg_error'         => ['Beim Senden ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut.', 'Kontaktformular', 'Meldung: Fehler'],
        'msg_invalid'       => ['Ungültige Anfrage.', 'Kontaktformular', 'Meldung: ungültige Anfrage'],
        // Leistungen
        'back_services'     => ['Zurück zu Leistungen', 'Leistungen', 'Link zurück'],
        'what_is'           => ['Was ist {title}?', 'Leistungen', 'Überschrift „Was ist …?" ({title} = Name der Leistung)'],
        'our_approach'      => ['Unser Ansatz bei {practice}', 'Leistungen', 'Überschrift „Unser Ansatz"'],
        'faq_title'         => ['Häufige Fragen', 'Leistungen', 'Überschrift FAQ (Detailseite)'],
        'gallery_title'     => ['Galerie', 'Leistungen', 'Überschrift Galerie'],
        'gallery_sub'       => ['Weitere Eindrücke zu {title}.', 'Leistungen', 'Untertitel Galerie'],
        // SEO
        'meta_services'     => ['Zahnmedizin für die ganze Familie bei {practice} — von der Kinderzahnheilkunde bis zur Parodontologie.', 'Seitenbeschreibungen (Google)', 'Beschreibung Leistungen-Seite'],
        'meta_about'        => ['Lernen Sie {doctor}, Ihren Zahnarzt bei {practice}, kennen.', 'Seitenbeschreibungen (Google)', 'Beschreibung Über-mich-Seite'],
        'meta_team'         => ['Lernen Sie das Praxisteam von {practice} kennen.', 'Seitenbeschreibungen (Google)', 'Beschreibung Team-Seite'],
    ];
}

/** Beschriftung in der aktuellen Sprache (Rohtext, noch NICHT escaped) */
function ui($key, $vars = []) {
    $c = load_site_content();
    $txt = $c['ui'][$key] ?? (ui_defaults()[$key][0] ?? $key);
    $vars += ['practice' => $c['site']['practice_name'] ?? ''];
    foreach ($vars as $k => $v) $txt = str_replace('{' . $k . '}', (string)$v, $txt);
    return $txt;
}

/* =================================================================
   ÜBERSETZBARE FELDER — eine einzige Liste für
   Dashboard, Speichern, Website-Anzeige und „Fehlt"-Prüfung.
   key     = Pfad in content-xx.json (mit IDs)
   de_path = Pfad in content-de.json (mit Positionen)
   ================================================================= */

function translatable_fields($de) {
    $g = [];
    $f = function ($key, $dePath, $label, $type = 'text') use ($de) {
        return ['key' => $key, 'de_path' => $dePath, 'label' => $label, 'type' => $type, 'de' => arr_get($de, $dePath)];
    };

    $g[] = ['id' => 'allgemein', 'title' => 'Allgemein & Öffnungszeiten', 'fields' => [
        $f('site.practice_tagline', 'site.practice_tagline', 'Slogan (unter dem Logo)'),
        $f('site.doctor_title', 'site.doctor_title', 'Titel des Arztes'),
        $f('site.address_line3', 'site.address_line3', 'Land'),
        $f('site.hours_line1', 'site.hours_line1', 'Öffnungszeiten Zeile 1'),
        $f('site.hours_line2', 'site.hours_line2', 'Öffnungszeiten Zeile 2'),
        $f('site.hours_line3', 'site.hours_line3', 'Öffnungszeiten Zeile 3'),
        $f('site.footer_tagline', 'site.footer_tagline', 'Footer-Slogan'),
        $f('site.copyright', 'site.copyright', 'Copyright-Zeile'),
        $f('nav.home', 'nav.home', 'Menü: Startseite'),
        $f('nav.leistungen', 'nav.leistungen', 'Menü: Leistungen'),
        $f('nav.ueber_mich', 'nav.ueber_mich', 'Menü: Über mich'),
        $f('nav.team', 'nav.team', 'Menü: Unser Team'),
    ]];

    // UI-Beschriftungen nach Gruppe
    $uiGroups = [];
    foreach (ui_defaults() as $k => $def) {
        $uiGroups[$def[1]][] = $f('ui.' . $k, 'ui.' . $k, $def[2], strpos($def[0], "\n") !== false || mb_strlen($def[0]) > 60 ? 'textarea' : 'text');
    }
    foreach ($uiGroups as $title => $fields) {
        $g[] = ['id' => 'ui-' . substr(md5($title), 0, 6), 'title' => 'Beschriftungen — ' . $title, 'fields' => $fields];
    }

    // Startseite
    $home = [
        $f('home.welcome_title', 'home.welcome_title', 'Hero: Begrüßungs-Badge'),
        $f('home.hero_title', 'home.hero_title', 'Hero: Überschrift'),
        $f('home.hero_text', 'home.hero_text', 'Hero: Text', 'textarea'),
        $f('home.hero_button', 'home.hero_button', 'Hero: Button-Text'),
    ];
    foreach ($de['home']['features'] as $i => $ft) {
        $home[] = $f("home.features.$i.title", "home.features.$i.title", 'Merkmal ' . ($i + 1) . ': Titel');
        $home[] = $f("home.features.$i.desc", "home.features.$i.desc", 'Merkmal ' . ($i + 1) . ': Beschreibung', 'textarea');
    }
    array_push($home,
        $f('home.intro_eyebrow', 'home.intro_eyebrow', 'Philosophie: kleine Überschrift'),
        $f('home.intro_title', 'home.intro_title', 'Philosophie: Überschrift'),
        $f('home.intro_paragraphs', 'home.intro_paragraphs', 'Philosophie: Absätze', 'paragraphs'),
        $f('home.intro_closing', 'home.intro_closing', 'Philosophie: Schlusssatz'),
        $f('home.services_title', 'home.services_title', 'Leistungen-Vorschau: Titel'),
        $f('home.services_subtitle', 'home.services_subtitle', 'Leistungen-Vorschau: Untertitel', 'textarea'),
        $f('home.services_link_text', 'home.services_link_text', 'Leistungen-Vorschau: Link-Text'),
        $f('home.about_preview_title', 'home.about_preview_title', '„Über mich"-Vorschau: Titel'),
        $f('home.about_preview_text', 'home.about_preview_text', '„Über mich"-Vorschau: Text', 'textarea'),
        $f('home.about_preview_button', 'home.about_preview_button', '„Über mich"-Vorschau: Button'),
        $f('home.contact_title', 'home.contact_title', 'Kontakt: Titel'),
        $f('home.contact_subtitle', 'home.contact_subtitle', 'Kontakt: Untertitel', 'textarea'),
        $f('home.contact_info_title', 'home.contact_info_title', 'Kontakt: „So erreichen Sie uns"'),
        $f('home.form_title', 'home.form_title', 'Formular: Titel'),
        $f('home.form_subtitle', 'home.form_subtitle', 'Formular: Untertitel', 'textarea')
    );
    $g[] = ['id' => 'startseite', 'title' => 'Startseite', 'fields' => $home];

    $pdf = [$f('pdf_downloads.button_text', 'pdf_downloads.button_text', 'Button-Text')];
    foreach ($de['pdf_downloads']['items'] as $i => $it) {
        $pdf[] = $f("pdf_downloads.items.$i.name", "pdf_downloads.items.$i.name", 'Dokument ' . ($i + 1) . ': Name');
    }
    $g[] = ['id' => 'pdf', 'title' => 'PDF-Downloads', 'fields' => $pdf];

    // Leistungen
    $g[] = ['id' => 'leistungen', 'title' => 'Leistungen — Seite', 'fields' => [
        $f('leistungen.hero_title', 'leistungen.hero_title', 'Kopfbereich: Überschrift'),
        $f('leistungen.hero_text', 'leistungen.hero_text', 'Kopfbereich: Text', 'textarea'),
        $f('leistungen.tech_title', 'leistungen.tech_title', 'Technik: Titel'),
        $f('leistungen.tech_paragraphs', 'leistungen.tech_paragraphs', 'Technik: Absätze', 'paragraphs'),
    ]];
    foreach ($de['leistungen']['services'] as $si => $svc) {
        $id = $svc['id'];
        $b = "leistungen.services.$id";
        $d = "leistungen.services.$si";
        $fields = [
            $f("$b.title", "$d.title", 'Titel'),
            $f("$b.subtitle", "$d.subtitle", 'Untertitel'),
            $f("$b.summary", "$d.summary", 'Kurzbeschreibung', 'textarea'),
            $f("$b.what_is", "$d.what_is", '„Was ist …?"-Text', 'textarea'),
            $f("$b.approach", "$d.approach", '„Unser Ansatz"-Text', 'textarea'),
        ];
        foreach ($svc['faqs'] as $fi => $faq) {
            $fields[] = $f("$b.faqs.{$faq['id']}.q", "$d.faqs.$fi.q", 'Frage ' . ($fi + 1));
            $fields[] = $f("$b.faqs.{$faq['id']}.a", "$d.faqs.$fi.a", 'Antwort ' . ($fi + 1), 'textarea');
        }
        foreach ($svc['gallery'] as $gi => $gal) {
            if (trim((string)$gal['caption']) === '') continue;
            $fields[] = $f("$b.gallery.{$gal['id']}.caption", "$d.gallery.$gi.caption", 'Galerie-Bild ' . ($gi + 1) . ': Beschreibung', 'textarea');
        }
        $g[] = ['id' => 'svc-' . $id, 'title' => 'Leistung — ' . ($svc['title'] !== '' ? $svc['title'] : ('#' . ($si + 1))), 'fields' => $fields];
    }

    // Über mich
    $um = [
        $f('ueber_mich.intro_title', 'ueber_mich.intro_title', 'Badge-Text'),
        $f('ueber_mich.hero_title', 'ueber_mich.hero_title', 'Überschrift'),
        $f('ueber_mich.paragraphs', 'ueber_mich.paragraphs', 'Biografie-Absätze', 'paragraphs'),
        $f('ueber_mich.quote', 'ueber_mich.quote', 'Zitat', 'textarea'),
        $f('ueber_mich.credentials_title', 'ueber_mich.credentials_title', 'Titel „Fortbildung & Schwerpunkte"'),
    ];
    foreach ($de['ueber_mich']['credentials'] as $ci => $cr) {
        $um[] = $f("ueber_mich.credentials.$ci", "ueber_mich.credentials.$ci", 'Qualifikation ' . ($ci + 1));
    }
    foreach ($de['ueber_mich']['features'] as $i => $ft) {
        $um[] = $f("ueber_mich.features.$i.title", "ueber_mich.features.$i.title", 'Merkmal ' . ($i + 1) . ': Titel');
        $um[] = $f("ueber_mich.features.$i.desc", "ueber_mich.features.$i.desc", 'Merkmal ' . ($i + 1) . ': Beschreibung', 'textarea');
    }
    $g[] = ['id' => 'uebermich', 'title' => 'Über mich', 'fields' => $um];

    // Team
    $tm = [
        $f('team.hero_title', 'team.hero_title', 'Überschrift'),
        $f('team.hero_text', 'team.hero_text', 'Einleitungstext', 'textarea'),
    ];
    foreach ($de['team']['members'] as $mi => $m) {
        $who = $m['name'] !== '' ? $m['name'] : ('Mitglied ' . ($mi + 1));
        if (trim((string)$m['role']) !== '') $tm[] = $f("team.members.{$m['id']}.role", "team.members.$mi.role", $who . ': Rolle');
        if (trim((string)$m['description']) !== '') $tm[] = $f("team.members.{$m['id']}.description", "team.members.$mi.description", $who . ': Beschreibung', 'textarea');
    }
    $g[] = ['id' => 'team', 'title' => 'Unser Team', 'fields' => $tm];

    // Rechtliches
    $g[] = ['id' => 'impressum', 'title' => 'Impressum', 'fields' => [
        $f('impressum.title', 'impressum.title', 'Seitentitel'),
        $f('impressum.blocks', 'impressum.blocks', 'Inhalt', 'blocks'),
    ]];
    $g[] = ['id' => 'datenschutz', 'title' => 'Datenschutz', 'fields' => [
        $f('datenschutz.title', 'datenschutz.title', 'Seitentitel'),
        $f('datenschutz.blocks', 'datenschutz.blocks', 'Inhalt', 'blocks'),
    ]];

    // Leere deutsche Felder brauchen keine Übersetzung
    foreach ($g as &$grp) {
        $grp['fields'] = array_values(array_filter($grp['fields'], function ($fl) { return is_filled($fl['de']); }));
    }
    unset($grp);
    return array_values(array_filter($g, function ($grp) { return !empty($grp['fields']); }));
}

function is_filled($v) {
    if (is_array($v)) {
        foreach ($v as $item) if (is_filled($item)) return true;
        return false;
    }
    return trim((string)$v) !== '';
}

function arr_get($arr, $path) {
    $cur = $arr;
    foreach (explode('.', $path) as $k) {
        if (is_array($cur) && array_key_exists($k, $cur)) $cur = $cur[$k];
        else return null;
    }
    return $cur;
}

function arr_set(&$arr, $path, $value) {
    $keys = explode('.', $path);
    $cur = &$arr;
    foreach ($keys as $k) {
        if (!is_array($cur)) $cur = [];
        if (!array_key_exists($k, $cur)) $cur[$k] = [];
        $cur = &$cur[$k];
    }
    $cur = $value;
}

/** Deutsche Inhalte + Übersetzung → Inhalte in der Zielsprache */
function localize_content($de, $tr) {
    $c = $de;
    foreach (translatable_fields($de) as $grp) {
        foreach ($grp['fields'] as $fl) {
            $v = arr_get($tr, $fl['key']);
            if ($fl['type'] === 'blocks') {
                $v = normalize_blocks(is_array($v) ? $v : []);
                if (!empty($v)) arr_set($c, $fl['de_path'], $v);
            } elseif ($fl['type'] === 'paragraphs') {
                if (is_array($v)) $v = array_values(array_filter(array_map('trim', $v), 'strlen'));
                if (is_filled($v)) arr_set($c, $fl['de_path'], $v);
            } elseif (is_filled($v)) {
                arr_set($c, $fl['de_path'], (string)$v);
            }
        }
    }
    return $c;
}

/** Anzahl fehlender Übersetzungen für eine Sprache */
function translation_missing_count($lang, $de = null) {
    $de = $de ?? load_content();
    $tr = load_translation($lang);
    $n = 0;
    foreach (translatable_fields($de) as $grp) {
        foreach ($grp['fields'] as $fl) {
            if (!is_filled(arr_get($tr, $fl['key']))) $n++;
        }
    }
    return $n;
}

function split_paragraphs($text) {
    $text = str_replace(["\r\n", "\r"], "\n", (string)$text);
    $parts = preg_split('/\n\s*\n/', $text);
    return array_values(array_filter(array_map('trim', $parts), function ($p) { return $p !== ''; }));
}

/* =================================================================
   SONSTIGES
   ================================================================= */

function favicon_mime($path) {
    $ext = strtolower(pathinfo((string)$path, PATHINFO_EXTENSION));
    $map = [
        'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
        'svg' => 'image/svg+xml', 'gif' => 'image/gif', 'webp' => 'image/webp',
        'ico' => 'image/x-icon',
    ];
    return $map[$ext] ?? 'image/png';
}

function color_overrides_style($colors) {
    $map = [
        '--blue'   => $colors['primary_blue']   ?? '#1B4F72',
        '--teal'   => $colors['primary_teal']   ?? '#2E8B78',
        '--green'  => $colors['accent_green']   ?? '#3D9B82',
        '--bg'     => $colors['background']     ?? '#F0F4F8',
        '--surface'=> $colors['surface']        ?? '#FFFFFF',
        '--text'   => $colors['text_primary']   ?? '#2C3E50',
        '--text-2' => $colors['text_secondary'] ?? '#5D6D7E',
    ];
    $out = "<style>:root{";
    foreach ($map as $var => $hex) {
        $out .= $var . ':' . e($hex) . ';';
    }
    $out .= "}</style>";
    return $out . i18n_head();
}

function current_page() {
    return basename($_SERVER['SCRIPT_NAME']);
}

function nav_active($page) {
    return current_page() === $page ? ' active' : '';
}

function icon_svg($name, $size = 28) {
    $s = (int)$size;
    $icons = [
        'users'    => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'award'    => '<circle cx="12" cy="8" r="5"/><path d="M8.5 12.5L7 22l5-3 5 3-1.5-9.5"/>',
        'heart'    => '<path d="M12 21s-7.5-4.6-10-9.3C.6 8.3 2.6 5 6 5c2 0 3.4 1 4.5 2.5C11.6 6 13 5 15 5c3.4 0 5.4 3.3 4 6.7C19.5 16.4 12 21 12 21z"/>',
        'sparkles' => '<path d="M12 2l1.6 5.2L19 9l-5.4 1.8L12 16l-1.6-5.2L5 9l5.4-1.8L12 2z"/><path d="M19 14l.7 2.3L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/>',
        'shield'   => '<path d="M12 2l8 4v6c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-4z"/>',
        'map-pin'  => '<path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 1 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
        'phone'    => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.7a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6 6l1.1-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.5 2.7.6a2 2 0 0 1 1.7 2z"/>',
        'printer'  => '<polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>',
        'mail'     => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/>',
        'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>',
        'upload'   => '<path d="M12 16V4M6 10l6-6 6 6"/><path d="M4 20h16"/>',
        'x'        => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
        'chevron-down' => '<polyline points="6 9 12 15 18 9"/>',
        'menu'     => '<line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>',
        'arrow-right' => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
        'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
        'images'   => '<rect x="3" y="3" width="14" height="14" rx="2"/><path d="M3 13l3.5-3.5a1.5 1.5 0 0 1 2.12 0L13 14"/><circle cx="8.5" cy="7.5" r="1.2"/><path d="M17 7h2a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2v-1"/>',
        'download' => '<path d="M12 16V4M6 10l6 6 6-6"/><path d="M4 20h16"/>',
        'file-text' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/>',
        'file'     => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>',
        'clipboard' => '<rect x="8" y="2" width="8" height="4" rx="1"/><path d="M9 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3"/><line x1="8" y1="11" x2="16" y2="11"/><line x1="8" y1="15" x2="16" y2="15"/>',
        'book-open' => '<path d="M2 4h6a4 4 0 0 1 4 4v12a3 3 0 0 0-3-3H2z"/><path d="M22 4h-6a4 4 0 0 0-4 4v12a3 3 0 0 1 3-3h7z"/>',
    ];
    $path = $icons[$name] ?? $icons['sparkles'];
    // Pfeile zeigen bei Arabisch automatisch in die richtige Richtung
    $cls = ($name === 'arrow-right' || $name === 'arrow-left') ? ' class="icon-dir"' : '';
    return '<svg' . $cls . ' width="' . $s . '" height="' . $s . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
}

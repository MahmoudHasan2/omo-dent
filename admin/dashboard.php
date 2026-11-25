<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/functions.php';
$c = load_content();

$messagesFile = __DIR__ . '/../data/messages.json';
$messages = [];
if (file_exists($messagesFile)) {
    $decoded = json_decode(file_get_contents($messagesFile), true);
    if (is_array($decoded)) $messages = $decoded;
}
$unreadCount = count(array_filter($messages, function ($m) { return empty($m['read']); }));

$missingTr = [];
foreach (translation_langs() as $tl) { $missingTr[$tl] = translation_missing_count($tl, $c); }
$missingVisible = ($missingTr['en'] ?? 0) + ($missingTr['ar'] ?? 0);
$uiDefs = ui_defaults();
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard – OMO.DENT</title>
<link rel="stylesheet" href="assets/admin.css?v=<?php echo @filemtime(__DIR__ . '/assets/admin.css'); ?>">
</head>
<body>
<div class="dash">
  <aside class="sidebar">
    <h2>OMO.DENT</h2>
    <p class="sub">Angemeldet als <?php echo e($_SESSION['admin_username']); ?></p>

    <button class="tab-btn active" data-tab="allgemein">⚙️ Allgemein</button>
    <button class="tab-btn" data-tab="startseite">🏠 Startseite</button>
    <button class="tab-btn" data-tab="leistungen">🦷 Leistungen</button>
    <button class="tab-btn" data-tab="uebermich">👤 Über mich</button>
    <button class="tab-btn" data-tab="team">👥 Unser Team</button>
    <button class="tab-btn" data-tab="pdfdownloads">📎 PDF-Downloads</button>
    <button class="tab-btn" data-tab="beschriftungen">🔤 Beschriftungen</button>
    <button class="tab-btn" data-tab="datenschutz">📄 Datenschutz</button>
    <button class="tab-btn" data-tab="impressum">📄 Impressum</button>
    <button class="tab-btn" data-tab="nachrichten">✉️ Nachrichten <?php if ($unreadCount): ?><span class="badge unread"><?php echo (int)$unreadCount; ?></span><?php endif; ?></button>

    <div class="logout">
      <a href="../index.php" target="_blank" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;margin-bottom:.6rem;">🔗 Website ansehen</a>
      <a href="translations.php" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;margin-bottom:.6rem;">🌐 Übersetzungen <?php if ($missingVisible): ?><span class="badge missing"><?php echo (int)$missingVisible; ?></span><?php endif; ?></a>
            <a href="passwort.php" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;margin-bottom:.6rem;">🔑 Passwort ändern</a>
      <a href="logout.php" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;">Abmelden</a>
    </div>
  </aside>

  <main class="main">
    <?php if ($missingVisible): ?>
      <div class="card warn-card">
        <strong>⚠ Übersetzungen unvollständig:</strong>
        <?php foreach (['en', 'ar'] as $tl): if (!$missingTr[$tl]) continue; ?>
          <?php echo lang_label($tl); ?>: <?php echo (int)$missingTr[$tl]; ?> Felder fehlen &nbsp;
        <?php endforeach; ?>
        — fehlende Felder werden auf der Website sonst auf Deutsch angezeigt.
        <a href="translations.php?lang=<?php echo $missingTr['en'] ? 'en' : 'ar'; ?>&only_missing=1" class="btn btn-sm" style="margin-inline-start:.5rem;">Jetzt übersetzen</a>
      </div>
    <?php endif; ?>

    <!-- ================= ALLGEMEIN ================= -->
    <section class="panel active" id="panel-allgemein">
      <h1>Allgemeine Einstellungen</h1>
      <p class="page-sub">Praxisname, Arzt, Kontaktdaten, Öffnungszeiten, Kartenlink und Farbwelt.</p>
      <form class="ajax-form" data-section="site">
        <div class="card">
          <h3>Praxis & Logo-Text</h3>
          <div class="row">
            <div class="field"><label>Praxisname</label><input type="text" name="fields[practice_name]" value="<?php echo e($c['site']['practice_name']); ?>"></div>
            <div class="field"><label>Slogan (unter dem Logo)</label><input type="text" name="fields[practice_tagline]" value="<?php echo e($c['site']['practice_tagline']); ?>"></div>
          </div>
          <div class="row">
            <div class="field"><label>Logo-Text Teil 1 (blau)</label><input type="text" name="fields[logo_text_1]" value="<?php echo e($c['site']['logo_text_1']); ?>"></div>
            <div class="field"><label>Logo-Text Teil 2 (grün)</label><input type="text" name="fields[logo_text_2]" value="<?php echo e($c['site']['logo_text_2']); ?>"></div>
          </div>
          <div class="row">
            <div class="field"><label>Name des Arztes</label><input type="text" name="fields[doctor_name]" value="<?php echo e($c['site']['doctor_name']); ?>"></div>
            <div class="field"><label>Titel / Berufsbezeichnung</label><input type="text" name="fields[doctor_title]" value="<?php echo e($c['site']['doctor_title']); ?>"></div>
          </div>
        </div>

        <div class="card">
          <h3>Logo & Browser-Icon</h3>
          <div class="field">
            <label>Eigenes Logo (ersetzt das Standard-Logo im Header, auf allen Seiten)</label>
            <p style="font-size:.78rem;color:var(--text-dim);margin:-.3rem 0 .6rem;">Leer lassen, um das eingebaute OMO.DENT-Logo zu verwenden.</p>
            <div class="upload-row">
              <img class="img-preview" src="<?php echo e($c['site']['logo_image']); ?>" id="preview-logo-image" style="background:var(--bg);">
              <div>
                <input type="text" name="fields[logo_image]" value="<?php echo e($c['site']['logo_image']); ?>" id="logo-image-input" style="margin-bottom:.5rem;width:280px;max-width:55vw;" placeholder="Bild-URL (leer = Standard-Logo)">
                <br>
                <label class="btn btn-outline btn-sm" style="cursor:pointer;">Logo hochladen
                  <input type="file" accept="image/*" class="upload-input" data-target="preview-logo-image" style="display:none;">
                </label>
                <button type="button" class="btn btn-outline btn-sm" id="reset-logo-image">Standard-Logo verwenden</button>
              </div>
            </div>
          </div>

          <div class="field" style="margin-top:1.5rem;">
            <label>Browser-Tab-Icon (Favicon)</label>
            <p style="font-size:.78rem;color:var(--text-dim);margin:-.3rem 0 .6rem;">Erscheint als kleines Symbol im Browser-Tab, auf allen Seiten.</p>
            <div class="upload-row">
              <img class="img-preview" src="<?php echo e($c['site']['favicon']); ?>" id="preview-favicon" style="width:44px;height:44px;background:var(--bg);">
              <div>
                <input type="text" name="fields[favicon]" value="<?php echo e($c['site']['favicon']); ?>" style="margin-bottom:.5rem;width:280px;max-width:55vw;">
                <br>
                <label class="btn btn-outline btn-sm" style="cursor:pointer;">Icon hochladen
                  <input type="file" accept="image/*" class="upload-input" data-target="preview-favicon" style="display:none;">
                </label>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <h3>Kontakt</h3>
          <div class="row">
            <div class="field"><label>Telefon</label><input type="text" name="fields[phone]" value="<?php echo e($c['site']['phone']); ?>"></div>
            <div class="field"><label>Fax <span class="hint-inline">(leer lassen = wird nicht angezeigt)</span></label><input type="text" name="fields[fax]" value="<?php echo e($c['site']['fax']); ?>"></div>
          </div>
          <div class="field"><label>E-Mail</label><input type="text" name="fields[email]" value="<?php echo e($c['site']['email']); ?>"></div>
          <div class="field"><label>Website</label><input type="text" name="fields[website]" value="<?php echo e($c['site']['website']); ?>"></div>
          <div class="field"><label>Adresse Zeile 1 (Straße)</label><input type="text" name="fields[address_line1]" value="<?php echo e($c['site']['address_line1']); ?>"></div>
          <div class="row">
            <div class="field"><label>Adresse Zeile 2 (PLZ Ort)</label><input type="text" name="fields[address_line2]" value="<?php echo e($c['site']['address_line2']); ?>"></div>
            <div class="field"><label>Adresse Zeile 3 (Land)</label><input type="text" name="fields[address_line3]" value="<?php echo e($c['site']['address_line3']); ?>"></div>
          </div>
        </div>

        <div class="card">
          <h3>Öffnungszeiten</h3>
          <div class="field"><label>Zeile 1</label><input type="text" name="fields[hours_line1]" value="<?php echo e($c['site']['hours_line1']); ?>"></div>
          <div class="field"><label>Zeile 2</label><input type="text" name="fields[hours_line2]" value="<?php echo e($c['site']['hours_line2']); ?>"></div>
          <div class="field"><label>Zeile 3</label><input type="text" name="fields[hours_line3]" value="<?php echo e($c['site']['hours_line3']); ?>"></div>
        </div>

        <div class="card">
          <h3>Fußzeile & Karte</h3>
          <div class="field"><label>Footer-Slogan</label><input type="text" name="fields[footer_tagline]" value="<?php echo e($c['site']['footer_tagline']); ?>"></div>
          <div class="field"><label>Copyright-Zeile</label><input type="text" name="fields[copyright]" value="<?php echo e($c['site']['copyright']); ?>"></div>
          <div class="field"><label>Google-Maps Einbettungslink (hl=de für Deutsch)</label><textarea name="fields[map_embed]"><?php echo e($c['site']['map_embed']); ?></textarea></div>
        </div>

        <div class="card">
          <h3>Farbwelt</h3>
          <div class="row">
            <div class="field"><label>Primärblau</label><input type="color" name="fields[colors][primary_blue]" value="<?php echo e($c['site']['colors']['primary_blue']); ?>" style="height:2.6rem;padding:.25rem;"></div>
            <div class="field"><label>Primärgrün (Teal)</label><input type="color" name="fields[colors][primary_teal]" value="<?php echo e($c['site']['colors']['primary_teal']); ?>" style="height:2.6rem;padding:.25rem;"></div>
          </div>
          <div class="row">
            <div class="field"><label>Akzentgrün</label><input type="color" name="fields[colors][accent_green]" value="<?php echo e($c['site']['colors']['accent_green']); ?>" style="height:2.6rem;padding:.25rem;"></div>
            <div class="field"><label>Hintergrund</label><input type="color" name="fields[colors][background]" value="<?php echo e($c['site']['colors']['background']); ?>" style="height:2.6rem;padding:.25rem;"></div>
          </div>
          <div class="row">
            <div class="field"><label>Text (primär)</label><input type="color" name="fields[colors][text_primary]" value="<?php echo e($c['site']['colors']['text_primary']); ?>" style="height:2.6rem;padding:.25rem;"></div>
            <div class="field"><label>Text (sekundär)</label><input type="color" name="fields[colors][text_secondary]" value="<?php echo e($c['site']['colors']['text_secondary']); ?>" style="height:2.6rem;padding:.25rem;"></div>
          </div>
          <p style="font-size:.78rem;color:var(--text-dim);">Hinweis: Diese Farben steuern Logo, Header, Footer und Icon-Farben. Das Haupt-Farbschema (Buttons, Karten) wird über <code>assets/css/style.css</code> gesteuert.</p>
          <input type="hidden" name="fields[colors][surface]" value="<?php echo e($c['site']['colors']['surface']); ?>">
        </div>

        <div class="card">
          <h3>Navigation (Menü-Beschriftungen)</h3>
          <div class="row">
            <div class="field"><label>Startseite</label><input type="text" data-nav-field="home" value="<?php echo e($c['nav']['home']); ?>"></div>
            <div class="field"><label>Leistungen</label><input type="text" data-nav-field="leistungen" value="<?php echo e($c['nav']['leistungen']); ?>"></div>
          </div>
          <div class="row">
            <div class="field"><label>Über mich</label><input type="text" data-nav-field="ueber_mich" value="<?php echo e($c['nav']['ueber_mich']); ?>"></div>
            <div class="field"><label>Unser Team</label><input type="text" data-nav-field="team" value="<?php echo e($c['nav']['team']); ?>"></div>
          </div>
        </div>

        <div class="card">
          <h3>Ladebildschirm (Loading Screen)</h3>
          <p style="font-size:.78rem;color:var(--text-dim);margin:-.3rem 0 .8rem;">Steuert, ob beim Öffnen der Seite kurz das Logo mit Ladebalken angezeigt wird.</p>
          <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer;font-weight:600;font-size:.9rem;color:var(--text);">
            <input type="hidden" name="fields[loader_enabled]" value="0">
            <input type="checkbox" name="fields[loader_enabled]" value="1" style="width:18px;height:18px;" <?php echo !empty($c['site']['loader_enabled']) ? 'checked' : ''; ?>>
            Ladebildschirm anzeigen
          </label>
        </div>

        <div class="save-bar">
          <button type="submit" class="btn">💾 Speichern</button>
          <span class="save-status">Gespeichert ✓</span>
        </div>
      </form>
    </section>

    <!-- ================= STARTSEITE ================= -->
    <section class="panel" id="panel-startseite">
      <h1>Startseite</h1>
      <p class="page-sub">Hero-Bereich, Merkmale, Vorschau-Bereiche und Kontakttexte.</p>
      <form class="ajax-form" data-section="home">
        <div class="card">
          <h3>Hero-Bereich</h3>
          <div class="field"><label>Begrüßungs-Badge</label><input type="text" name="fields[welcome_title]" value="<?php echo e($c['home']['welcome_title']); ?>"></div>
          <div class="field"><label>Überschrift (H1)</label><textarea name="fields[hero_title]"><?php echo e($c['home']['hero_title']); ?></textarea></div>
          <div class="field"><label>Beschreibungstext</label><textarea name="fields[hero_text]"><?php echo e($c['home']['hero_text']); ?></textarea></div>
          <div class="field"><label>Button-Text</label><input type="text" name="fields[hero_button]" value="<?php echo e($c['home']['hero_button']); ?>"></div>
          <div class="upload-row">
            <img class="img-preview" src="<?php echo e($c['home']['hero_image']); ?>" id="preview-home-hero">
            <div>
              <input type="text" name="fields[hero_image]" value="<?php echo e($c['home']['hero_image']); ?>" style="margin-bottom:.5rem;width:320px;max-width:60vw;">
              <br>
              <label class="btn btn-outline btn-sm" style="cursor:pointer;">Bild hochladen
                <input type="file" accept="image/*" class="upload-input" data-target="preview-home-hero" style="display:none;">
              </label>
            </div>
          </div>
        </div>

        <div class="card">
          <h3>Merkmale (3 Karten)</h3>
          <?php foreach ($c['home']['features'] as $i => $f): ?>
            <div class="repeat-item">
              <div class="item-head"><span>Karte <?php echo $i+1; ?></span></div>
              <div class="field"><label>Symbol</label>
                <select name="fields[features][<?php echo $i; ?>][icon]">
                  <?php foreach (['users','award','heart','sparkles','shield'] as $ic): ?>
                    <option value="<?php echo $ic; ?>" <?php echo $f['icon']===$ic?'selected':''; ?>><?php echo $ic; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="field"><label>Titel</label><input type="text" name="fields[features][<?php echo $i; ?>][title]" value="<?php echo e($f['title']); ?>"></div>
              <div class="field"><label>Beschreibung</label><textarea name="fields[features][<?php echo $i; ?>][desc]"><?php echo e($f['desc']); ?></textarea></div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="card">
          <h3>Philosophie-Abschnitt (unter dem Laufband)</h3>
          <div class="field"><label>Eyebrow-Label</label><input type="text" name="fields[intro_eyebrow]" value="<?php echo e($c['home']['intro_eyebrow'] ?? ''); ?>"></div>
          <div class="field"><label>Überschrift</label><input type="text" name="fields[intro_title]" value="<?php echo e($c['home']['intro_title'] ?? ''); ?>"></div>
          <div class="field"><label>Absätze (durch Leerzeile getrennt)</label>
            <textarea name="fields[intro_paragraphs_text]" style="min-height:150px;"><?php echo e(implode("\n\n", $c['home']['intro_paragraphs'] ?? [])); ?></textarea>
          </div>
          <div class="field"><label>Schlusssatz (hervorgehoben)</label><input type="text" name="fields[intro_closing]" value="<?php echo e($c['home']['intro_closing'] ?? ''); ?>"></div>
        </div>

        <div class="card">
          <h3>Leistungen-Vorschau</h3>
          <div class="field"><label>Titel</label><input type="text" name="fields[services_title]" value="<?php echo e($c['home']['services_title']); ?>"></div>
          <div class="field"><label>Untertitel</label><input type="text" name="fields[services_subtitle]" value="<?php echo e($c['home']['services_subtitle']); ?>"></div>
          <div class="field"><label>Link-Text</label><input type="text" name="fields[services_link_text]" value="<?php echo e($c['home']['services_link_text']); ?>"></div>
        </div>

        <div class="card">
          <h3>„Über mich"-Vorschau</h3>
          <div class="field"><label>Titel</label><input type="text" name="fields[about_preview_title]" value="<?php echo e($c['home']['about_preview_title']); ?>"></div>
          <div class="field"><label>Text</label><textarea name="fields[about_preview_text]"><?php echo e($c['home']['about_preview_text']); ?></textarea></div>
          <div class="field"><label>Button-Text</label><input type="text" name="fields[about_preview_button]" value="<?php echo e($c['home']['about_preview_button']); ?>"></div>
        </div>

        <div class="card">
          <h3>Kontaktbereich</h3>
          <div class="field"><label>Titel</label><input type="text" name="fields[contact_title]" value="<?php echo e($c['home']['contact_title']); ?>"></div>
          <div class="field"><label>Untertitel</label><input type="text" name="fields[contact_subtitle]" value="<?php echo e($c['home']['contact_subtitle']); ?>"></div>
          <div class="field"><label>Titel „So erreichen Sie uns"</label><input type="text" name="fields[contact_info_title]" value="<?php echo e($c['home']['contact_info_title']); ?>"></div>
          <div class="field"><label>Formular-Titel</label><input type="text" name="fields[form_title]" value="<?php echo e($c['home']['form_title']); ?>"></div>
          <div class="field"><label>Formular-Untertitel</label><input type="text" name="fields[form_subtitle]" value="<?php echo e($c['home']['form_subtitle']); ?>"></div>
        </div>

        <div class="save-bar">
          <button type="submit" class="btn">💾 Speichern</button>
          <span class="save-status">Gespeichert ✓</span>
        </div>
      </form>
    </section>

    <!-- ================= LEISTUNGEN ================= -->
    <section class="panel" id="panel-leistungen">
      <h1>Leistungen-Seite</h1>
      <p class="page-sub">Alle Leistungen mit Bild, Titel, beliebig vielen FAQ-Fragen und Galerie-Bildern.</p>
      <form class="ajax-form" data-section="leistungen">
        <div class="card">
          <h3>Kopfbereich</h3>
          <div class="field"><label>Überschrift</label><input type="text" name="fields[hero_title]" value="<?php echo e($c['leistungen']['hero_title']); ?>"></div>
          <div class="field"><label>Text</label><textarea name="fields[hero_text]"><?php echo e($c['leistungen']['hero_text']); ?></textarea></div>
        </div>

        <div class="card" id="services-container" data-prefix="fields[services]" data-next-index="<?php echo count($c['leistungen']['services']); ?>">
          <h3>Leistungen (Karten mit FAQ &amp; Galerie)</h3>
          <?php foreach ($c['leistungen']['services'] as $si => $service): ?>
            <div class="repeat-item">
              <div class="item-head"><span>Leistung <?php echo $si+1; ?></span><button type="button" class="btn btn-outline btn-sm remove-item">Entfernen</button></div>
              <div class="upload-row">
                <img class="img-preview" src="<?php echo e($service['image']); ?>" id="preview-service-<?php echo $si; ?>">
                <div>
                  <input type="text" name="fields[services][<?php echo $si; ?>][image]" value="<?php echo e($service['image']); ?>" style="margin-bottom:.5rem;width:260px;max-width:55vw;">
                  <br>
                  <label class="btn btn-outline btn-sm" style="cursor:pointer;">Bild hochladen
                    <input type="file" accept="image/*" class="upload-input" data-target="preview-service-<?php echo $si; ?>" style="display:none;">
                  </label>
                </div>
              </div>
              <input type="hidden" name="fields[services][<?php echo $si; ?>][id]" value="<?php echo e($service['id']); ?>">
              <div class="field"><label>Titel</label><input type="text" name="fields[services][<?php echo $si; ?>][title]" value="<?php echo e($service['title']); ?>"></div>
              <div class="field"><label>Untertitel (kurzer Slogan)</label><input type="text" name="fields[services][<?php echo $si; ?>][subtitle]" value="<?php echo e($service['subtitle'] ?? ''); ?>"></div>
              <div class="field"><label>Kurzbeschreibung (für Startseite/Übersicht)</label><textarea name="fields[services][<?php echo $si; ?>][summary]"><?php echo e($service['summary'] ?? ''); ?></textarea></div>
              <div class="field"><label>„Was ist <?php echo e($service['title']); ?>?"-Text</label><textarea name="fields[services][<?php echo $si; ?>][what_is]"><?php echo e($service['what_is'] ?? ''); ?></textarea></div>
              <div class="field"><label>„Unser Ansatz bei OMO.DENT"</label><textarea name="fields[services][<?php echo $si; ?>][approach]"><?php echo e($service['approach'] ?? ''); ?></textarea></div>

              <div class="faq-sub faqs-container" data-sidx="<?php echo $si; ?>" data-next-index="<?php echo count($service['faqs'] ?? []); ?>">
                <label style="display:block;font-size:.85rem;color:var(--text-dim);margin-bottom:.6rem;">Häufige Fragen (FAQ)</label>
                <?php foreach ($service['faqs'] as $fi => $faq): ?>
                  <div class="repeat-item faq-item-admin">
                    <div class="item-head"><span>Frage <?php echo $fi+1; ?></span><button type="button" class="btn btn-outline btn-sm remove-item">Entfernen</button></div>
                    <input type="hidden" name="fields[services][<?php echo $si; ?>][faqs][<?php echo $fi; ?>][id]" value="<?php echo e($faq['id']); ?>">
                    <div class="field"><label>Frage</label><input type="text" name="fields[services][<?php echo $si; ?>][faqs][<?php echo $fi; ?>][q]" value="<?php echo e($faq['q']); ?>"></div>
                    <div class="field"><label>Antwort</label><textarea name="fields[services][<?php echo $si; ?>][faqs][<?php echo $fi; ?>][a]"><?php echo e($faq['a']); ?></textarea></div>
                  </div>
                <?php endforeach; ?>
                <button type="button" class="btn btn-outline btn-sm add-faq" data-sidx="<?php echo $si; ?>">+ Frage hinzufügen</button>
              </div>

              <div class="faq-sub gallery-container" data-sidx="<?php echo $si; ?>" data-next-index="<?php echo count($service['gallery'] ?? []); ?>">
                <label style="display:block;font-size:.85rem;color:var(--text-dim);margin-bottom:.6rem;">Galerie (Bilder für die eigene Galerie dieser Leistung)</label>
                <?php foreach (($service['gallery'] ?? []) as $gi => $img): ?>
                  <div class="repeat-item gallery-item">
                    <div class="item-head"><span>Galerie-Bild <?php echo $gi+1; ?></span><button type="button" class="btn btn-outline btn-sm remove-item">Entfernen</button></div>
                    <div class="upload-row">
                      <img class="img-preview" src="<?php echo e($img['image']); ?>" id="preview-gallery-<?php echo $si; ?>-<?php echo $gi; ?>">
                      <div>
                        <input type="text" name="fields[services][<?php echo $si; ?>][gallery][<?php echo $gi; ?>][image]" value="<?php echo e($img['image']); ?>" style="margin-bottom:.5rem;width:240px;max-width:55vw;" placeholder="Bild-URL">
                        <br>
                        <label class="btn btn-outline btn-sm" style="cursor:pointer;">Bild hochladen
                          <input type="file" accept="image/*" class="upload-input" data-target="preview-gallery-<?php echo $si; ?>-<?php echo $gi; ?>" style="display:none;">
                        </label>
                      </div>
                    </div>
                    <input type="hidden" name="fields[services][<?php echo $si; ?>][gallery][<?php echo $gi; ?>][id]" value="<?php echo e($img['id']); ?>">
                    <div class="field"><label>Beschreibung unter dem Bild</label><textarea name="fields[services][<?php echo $si; ?>][gallery][<?php echo $gi; ?>][caption]"><?php echo e($img['caption'] ?? ''); ?></textarea></div>
                  </div>
                <?php endforeach; ?>
                <button type="button" class="btn btn-outline btn-sm add-gallery-image" data-sidx="<?php echo $si; ?>">+ Bild zur Galerie hinzufügen</button>
              </div>
            </div>
          <?php endforeach; ?>
          <button type="button" class="btn btn-outline btn-sm" id="add-service">+ Leistung hinzufügen</button>
        </div>

        <div class="card">
          <h3>Technik-Bereich</h3>
          <div class="field"><label>Titel</label><input type="text" name="fields[tech_title]" value="<?php echo e($c['leistungen']['tech_title']); ?>"></div>
          <div class="field"><label>Bild 1</label></div>
          <div class="upload-row">
            <img class="img-preview" src="<?php echo e($c['leistungen']['tech_image']); ?>" id="preview-tech-image">
            <div>
              <input type="text" name="fields[tech_image]" value="<?php echo e($c['leistungen']['tech_image']); ?>" style="margin-bottom:.5rem;width:280px;max-width:55vw;">
              <br>
              <label class="btn btn-outline btn-sm" style="cursor:pointer;">Bild hochladen
                <input type="file" accept="image/*" class="upload-input" data-target="preview-tech-image" style="display:none;">
              </label>
            </div>
          </div>
          <div class="field"><label>Bild 2 (zusätzlich, optional)</label></div>
          <div class="upload-row">
            <img class="img-preview" src="<?php echo e($c['leistungen']['tech_image_secondary'] ?? ''); ?>" id="preview-tech-image-2">
            <div>
              <input type="text" name="fields[tech_image_secondary]" value="<?php echo e($c['leistungen']['tech_image_secondary'] ?? ''); ?>" style="margin-bottom:.5rem;width:280px;max-width:55vw;" placeholder="Bild-URL (leer lassen = kein zweites Bild)">
              <br>
              <label class="btn btn-outline btn-sm" style="cursor:pointer;">Bild hochladen
                <input type="file" accept="image/*" class="upload-input" data-target="preview-tech-image-2" style="display:none;">
              </label>
            </div>
          </div>
          <div class="field"><label>Absätze (durch Leerzeile getrennt)</label>
            <textarea name="fields[tech_paragraphs_text]" style="min-height:150px;"><?php echo e(implode("\n\n", $c['leistungen']['tech_paragraphs'])); ?></textarea>
          </div>
        </div>

        <div class="save-bar">
          <button type="submit" class="btn">💾 Speichern</button>
          <span class="save-status">Gespeichert ✓</span>
        </div>
      </form>
    </section>

    <!-- ================= ÜBER MICH ================= -->
    <section class="panel" id="panel-uebermich">
      <h1>Über mich</h1>
      <p class="page-sub">Foto, Biografie, Qualifikationen und Zitat des Arztes.</p>
      <form class="ajax-form" data-section="ueber_mich">
        <div class="card">
          <h3>Foto</h3>
          <div class="upload-row">
            <img class="img-preview" src="<?php echo e($c['ueber_mich']['photo']); ?>" id="preview-doctor-photo" style="width:90px;height:110px;object-fit:cover;">
            <div>
              <input type="text" name="fields[photo]" value="<?php echo e($c['ueber_mich']['photo']); ?>" style="margin-bottom:.5rem;width:320px;max-width:60vw;" placeholder="Bild-URL (leer = Platzhalter wird angezeigt)">
              <br>
              <label class="btn btn-outline btn-sm" style="cursor:pointer;">Foto hochladen
                <input type="file" accept="image/*" class="upload-input" data-target="preview-doctor-photo" style="display:none;">
              </label>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="field"><label>Badge-Text</label><input type="text" name="fields[intro_title]" value="<?php echo e($c['ueber_mich']['intro_title']); ?>"></div>
          <div class="field"><label>Überschrift</label><input type="text" name="fields[hero_title]" value="<?php echo e($c['ueber_mich']['hero_title']); ?>"></div>
          <div class="field"><label>Biografie-Absätze (durch Leerzeile getrennt)</label>
            <textarea name="fields[paragraphs_text]" style="min-height:220px;"><?php echo e(implode("\n\n", $c['ueber_mich']['paragraphs'])); ?></textarea>
          </div>
          <div class="field"><label>Zitat (hervorgehoben)</label><input type="text" name="fields[quote]" value="<?php echo e($c['ueber_mich']['quote']); ?>"></div>
        </div>

        <div class="card" id="credentials-container" data-next-index="<?php echo count($c['ueber_mich']['credentials']); ?>">
          <h3>Ausbildung & Qualifikation</h3>
          <div class="field"><label>Überschrift dieses Abschnitts</label><input type="text" name="fields[credentials_title]" value="<?php echo e($c['ueber_mich']['credentials_title']); ?>"></div>
          <?php foreach ($c['ueber_mich']['credentials'] as $ci => $cred): ?>
            <div class="repeat-item credential-item">
              <div class="item-head"><span>Eintrag <?php echo $ci+1; ?></span><button type="button" class="btn btn-outline btn-sm remove-item">Entfernen</button></div>
              <input type="text" name="fields[credentials][<?php echo $ci; ?>]" value="<?php echo e($cred); ?>">
            </div>
          <?php endforeach; ?>
          <button type="button" class="btn btn-outline btn-sm" id="add-credential">+ Eintrag hinzufügen</button>
        </div>

        <div class="card">
          <h3>Merkmale (3 Karten unten auf der Seite)</h3>
          <?php foreach ($c['ueber_mich']['features'] as $i => $f): ?>
            <div class="repeat-item">
              <div class="item-head"><span>Karte <?php echo $i+1; ?></span></div>
              <div class="field"><label>Symbol</label>
                <select name="fields[features][<?php echo $i; ?>][icon]">
                  <?php foreach (['users','award','heart','sparkles','shield'] as $ic): ?>
                    <option value="<?php echo $ic; ?>" <?php echo $f['icon']===$ic?'selected':''; ?>><?php echo $ic; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="field"><label>Titel</label><input type="text" name="fields[features][<?php echo $i; ?>][title]" value="<?php echo e($f['title']); ?>"></div>
              <div class="field"><label>Beschreibung</label><textarea name="fields[features][<?php echo $i; ?>][desc]"><?php echo e($f['desc']); ?></textarea></div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="save-bar">
          <button type="submit" class="btn">💾 Speichern</button>
          <span class="save-status">Gespeichert ✓</span>
        </div>
      </form>
    </section>

    <!-- ================= TEAM ================= -->
    <section class="panel" id="panel-team">
      <h1>Unser Team</h1>
      <p class="page-sub">Kopfbereich und Teammitglieder der Team-Seite. Der Zahnarzt sollte als erster Eintrag stehen.</p>
      <form class="ajax-form" data-section="team">
        <div class="card">
          <h3>Kopfbereich</h3>
          <div class="field"><label>Überschrift</label><input type="text" name="fields[hero_title]" value="<?php echo e($c['team']['hero_title']); ?>"></div>
          <div class="field"><label>Einleitungstext</label><textarea name="fields[hero_text]"><?php echo e($c['team']['hero_text']); ?></textarea></div>
        </div>

        <div class="card" id="team-members-container" data-next-index="<?php echo count($c['team']['members']); ?>">
          <h3>Teammitglieder</h3>
          <?php foreach ($c['team']['members'] as $mi => $member): ?>
            <div class="repeat-item">
              <div class="item-head"><span><?php echo $mi === 0 ? 'Zahnarzt / Zahnärztin' : 'Mitglied ' . $mi; ?></span><button type="button" class="btn btn-outline btn-sm remove-item">Entfernen</button></div>
              <div class="upload-row">
                <img class="img-preview" src="<?php echo e($member['photo']); ?>" id="preview-team-<?php echo $mi; ?>">
                <div>
                  <input type="text" name="fields[members][<?php echo $mi; ?>][photo]" value="<?php echo e($member['photo']); ?>" style="margin-bottom:.5rem;width:260px;max-width:55vw;" placeholder="Bild-URL">
                  <br>
                  <label class="btn btn-outline btn-sm" style="cursor:pointer;">Foto hochladen
                    <input type="file" accept="image/*" class="upload-input" data-target="preview-team-<?php echo $mi; ?>" style="display:none;">
                  </label>
                </div>
              </div>
              <div class="row">
                <input type="hidden" name="fields[members][<?php echo $mi; ?>][id]" value="<?php echo e($member['id']); ?>">
                <div class="field"><label>Name (leer lassen, falls nicht öffentlich genannt)</label><input type="text" name="fields[members][<?php echo $mi; ?>][name]" value="<?php echo e($member['name']); ?>"></div>
                <div class="field"><label>Rolle / Position</label><input type="text" name="fields[members][<?php echo $mi; ?>][role]" value="<?php echo e($member['role']); ?>"></div>
              </div>
              <div class="field"><label>Beschreibung</label><textarea name="fields[members][<?php echo $mi; ?>][description]"><?php echo e($member['description']); ?></textarea></div>
            </div>
          <?php endforeach; ?>
          <button type="button" class="btn btn-outline btn-sm" id="add-team-member">+ Teammitglied hinzufügen</button>
        </div>

        <div class="save-bar">
          <button type="submit" class="btn">💾 Speichern</button>
          <span class="save-status">Gespeichert ✓</span>
        </div>
      </form>
    </section>

    <!-- ================= PDF-DOWNLOADS ================= -->
    <section class="panel" id="panel-pdfdownloads">
      <h1>PDF-Downloads (Startseite)</h1>
      <p class="page-sub">Der Button im Hero-Bereich öffnet ein Fenster mit 4 herunterladbaren PDF-Dokumenten.</p>
      <form class="ajax-form" data-section="pdf_downloads">
        <div class="card">
          <h3>Button</h3>
          <div class="field"><label>Button-Text</label><input type="text" name="fields[button_text]" value="<?php echo e($c['pdf_downloads']['button_text']); ?>"></div>
          <div class="row">
            <div class="field"><label>Button-Hintergrundfarbe</label><input type="color" name="fields[button_bg]" value="<?php echo e($c['pdf_downloads']['button_bg']); ?>" style="height:2.6rem;padding:.25rem;"></div>
            <div class="field"><label>Button-Textfarbe</label><input type="color" name="fields[button_text_color]" value="<?php echo e($c['pdf_downloads']['button_text_color']); ?>" style="height:2.6rem;padding:.25rem;"></div>
          </div>
        </div>

        <div class="card">
          <h3>Die 4 Dokumente</h3>
          <?php foreach ($c['pdf_downloads']['items'] as $pi => $item): ?>
            <div class="repeat-item">
              <div class="item-head"><span>Dokument <?php echo $pi+1; ?></span></div>
              <div class="row">
                <div class="field"><label>Name (unter dem Symbol)</label><input type="text" name="fields[items][<?php echo $pi; ?>][name]" value="<?php echo e($item['name']); ?>"></div>
                <div class="field"><label>Symbol</label>
                  <select name="fields[items][<?php echo $pi; ?>][icon]">
                    <?php foreach (['file-text'=>'Dokument','file'=>'Blatt','clipboard'=>'Klemmbrett','book-open'=>'Buch','images'=>'Bilder','download'=>'Download'] as $val=>$label): ?>
                      <option value="<?php echo $val; ?>" <?php echo $item['icon']===$val?'selected':''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="field">
                <label>PDF-Datei</label>
                <div class="upload-row">
                  <span class="pdf-file-status" id="pdf-status-<?php echo $pi; ?>" style="font-size:.82rem;color:var(--text-dim);">
                    <?php echo !empty($item['file']) ? '📄 ' . e(basename($item['file'])) : 'Noch keine Datei hochgeladen'; ?>
                  </span>
                </div>
                <input type="hidden" name="fields[items][<?php echo $pi; ?>][file]" id="pdf-file-input-<?php echo $pi; ?>" value="<?php echo e($item['file']); ?>">
                <label class="btn btn-outline btn-sm" style="cursor:pointer;">PDF hochladen / ersetzen
                  <input type="file" accept="application/pdf" class="upload-pdf-input" data-status="pdf-status-<?php echo $pi; ?>" data-target="pdf-file-input-<?php echo $pi; ?>" style="display:none;">
                </label>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="save-bar">
          <button type="submit" class="btn">💾 Speichern</button>
          <span class="save-status">Gespeichert ✓</span>
        </div>
      </form>
    </section>

    <!-- ================= BESCHRIFTUNGEN ================= -->
    <section class="panel" id="panel-beschriftungen">
      <h1>Beschriftungen</h1>
      <p class="page-sub">Feste Texte der Website (Knöpfe, Formular, Überschriften, Meldungen). Platzhalter in geschweiften Klammern wie <code>{practice}</code> oder <code>{title}</code> werden automatisch ersetzt — bitte stehen lassen. Übersetzungen dazu unter „🌐 Übersetzungen".</p>
      <form class="ajax-form" data-section="ui">
        <?php $uiByGroup = []; foreach ($uiDefs as $k => $def) { $uiByGroup[$def[1]][$k] = $def; } ?>
        <?php foreach ($uiByGroup as $grp => $items): ?>
          <div class="card">
            <h3><?php echo e($grp); ?></h3>
            <?php foreach ($items as $k => $def): $val = $c['ui'][$k] ?? $def[0]; ?>
              <div class="field"><label><?php echo e($def[2]); ?></label>
                <?php if (strpos($def[0], "\n") !== false || mb_strlen($def[0]) > 60): ?>
                  <textarea name="fields[<?php echo e($k); ?>]" style="min-height:60px;"><?php echo e($val); ?></textarea>
                <?php else: ?>
                  <input type="text" name="fields[<?php echo e($k); ?>]" value="<?php echo e($val); ?>">
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
        <div class="save-bar">
          <button type="submit" class="btn">💾 Speichern</button>
          <span class="save-status">Gespeichert ✓</span>
        </div>
      </form>
    </section>

    <!-- ================= DATENSCHUTZ ================= -->
    <section class="panel" id="panel-datenschutz">
      <h1>Datenschutzerklärung</h1>
      <p class="page-sub">Jeder Abschnitt hat eine Überschrift und darunter beliebig viele Zeilen. Jedes Feld = eine Zeile auf der Website. Ein leeres Feld = Abstand (neuer Absatz). <strong>Enter</strong> im Feld = neue Zeile darunter.</p>
      <form class="ajax-form" data-section="datenschutz">
        <div class="card">
          <div class="field"><label>Seitentitel</label><input type="text" name="fields[title]" value="<?php echo e($c['datenschutz']['title']); ?>"></div>
        </div>
        <div class="blocks-editor" data-dir="ltr"></div>
        <textarea name="fields[blocks_json]" class="blocks-json" hidden><?php echo e(json_encode($c['datenschutz']['blocks'], JSON_UNESCAPED_UNICODE)); ?></textarea>
        <div class="save-bar">
          <button type="submit" class="btn">💾 Speichern</button>
          <span class="save-status">Gespeichert ✓</span>
        </div>
      </form>
    </section>

    <!-- ================= IMPRESSUM ================= -->
    <section class="panel" id="panel-impressum">
      <h1>Impressum</h1>
      <p class="page-sub">Jeder Abschnitt hat eine Überschrift und darunter beliebig viele Zeilen. Jedes Feld = eine Zeile auf der Website. Ein leeres Feld = Abstand (neuer Absatz). <strong>Enter</strong> im Feld = neue Zeile darunter.</p>
      <form class="ajax-form" data-section="impressum">
        <div class="card">
          <div class="field"><label>Seitentitel</label><input type="text" name="fields[title]" value="<?php echo e($c['impressum']['title']); ?>"></div>
        </div>
        <div class="blocks-editor" data-dir="ltr"></div>
        <textarea name="fields[blocks_json]" class="blocks-json" hidden><?php echo e(json_encode($c['impressum']['blocks'], JSON_UNESCAPED_UNICODE)); ?></textarea>
        <div class="save-bar">
          <button type="submit" class="btn">💾 Speichern</button>
          <span class="save-status">Gespeichert ✓</span>
        </div>
      </form>
    </section>

    <!-- ================= NACHRICHTEN ================= -->
    <section class="panel" id="panel-nachrichten">
      <h1>Nachrichten</h1>
      <p class="page-sub">Alle über das Kontaktformular eingegangenen Anfragen.</p>
      <div class="card">
        <?php if (empty($messages)): ?>
          <p style="color:var(--text-dim);">Noch keine Nachrichten eingegangen.</p>
        <?php else: ?>
          <table class="msg-table">
            <thead><tr><th>Datum</th><th>Name</th><th>Kontakt</th><th>Patient?</th><th>Nachricht</th><th>Status</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($messages as $m): ?>
                <tr>
                  <td><?php echo e($m['date'] ?? ''); ?></td>
                  <td><?php echo e(($m['firstName'] ?? '') . ' ' . ($m['lastName'] ?? '')); ?></td>
                  <td><?php echo e($m['phone'] ?? ''); ?><br><?php echo e($m['email'] ?? ''); ?></td>
                  <td><?php echo e($m['isPatient'] ?? ''); ?></td>
                  <td style="max-width:280px;"><?php echo nl2br(e($m['message'] ?? '')); ?></td>
                  <td><span class="badge <?php echo !empty($m['read']) ? 'read' : 'unread'; ?>"><?php echo !empty($m['read']) ? 'Gelesen' : 'Neu'; ?></span></td>
                  <td>
                    <form method="post" action="message_action.php" style="display:inline;">
                      <input type="hidden" name="id" value="<?php echo e($m['id']); ?>">
                      <button type="submit" name="action" value="toggle_read" class="btn btn-outline btn-sm">✓</button>
                      <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm" onclick="return confirm('Nachricht wirklich löschen?');">🗑</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </section>

  </main>
</div>

<!-- Hidden templates -->
<template id="tpl-service">
<div class="repeat-item">
  <div class="item-head"><span>Neue Leistung</span><button type="button" class="btn btn-outline btn-sm remove-item">Entfernen</button></div>
  <div class="upload-row">
    <img class="img-preview" src="" id="preview-service-__IDX__">
    <div>
      <input type="text" name="fields[services][__IDX__][image]" value="" style="margin-bottom:.5rem;width:260px;max-width:55vw;" placeholder="Bild-URL">
      <br>
      <label class="btn btn-outline btn-sm" style="cursor:pointer;">Bild hochladen
        <input type="file" accept="image/*" class="upload-input" data-target="preview-service-__IDX__" style="display:none;">
      </label>
    </div>
  </div>
  <input type="hidden" name="fields[services][__IDX__][id]" value="">
  <div class="field"><label>Titel</label><input type="text" name="fields[services][__IDX__][title]" value=""></div>
  <div class="field"><label>Untertitel (kurzer Slogan)</label><input type="text" name="fields[services][__IDX__][subtitle]" value=""></div>
  <div class="field"><label>Kurzbeschreibung (für Startseite/Übersicht)</label><textarea name="fields[services][__IDX__][summary]"></textarea></div>
  <div class="field"><label>„Was ist das?"</label><textarea name="fields[services][__IDX__][what_is]"></textarea></div>
  <div class="field"><label>„Unser Ansatz bei OMO.DENT"</label><textarea name="fields[services][__IDX__][approach]"></textarea></div>
  <div class="faq-sub faqs-container" data-sidx="__IDX__" data-next-index="0">
    <label style="display:block;font-size:.85rem;color:var(--text-dim);margin-bottom:.6rem;">Häufige Fragen (FAQ)</label>
    <button type="button" class="btn btn-outline btn-sm add-faq" data-sidx="__IDX__">+ Frage hinzufügen</button>
  </div>
  <div class="faq-sub gallery-container" data-sidx="__IDX__" data-next-index="0">
    <label style="display:block;font-size:.85rem;color:var(--text-dim);margin-bottom:.6rem;">Galerie</label>
    <button type="button" class="btn btn-outline btn-sm add-gallery-image" data-sidx="__IDX__">+ Bild zur Galerie hinzufügen</button>
  </div>
</div>
</template>

<template id="tpl-faq">
<div class="repeat-item faq-item-admin">
  <div class="item-head"><span>Neue Frage</span><button type="button" class="btn btn-outline btn-sm remove-item">Entfernen</button></div>
  <input type="hidden" name="fields[services][__SIDX__][faqs][__FIDX__][id]" value="">
  <div class="field"><label>Frage</label><input type="text" name="fields[services][__SIDX__][faqs][__FIDX__][q]" value=""></div>
  <div class="field"><label>Antwort</label><textarea name="fields[services][__SIDX__][faqs][__FIDX__][a]"></textarea></div>
</div>
</template>

<template id="tpl-gallery-image">
<div class="repeat-item gallery-item">
  <div class="item-head"><span>Neues Galerie-Bild</span><button type="button" class="btn btn-outline btn-sm remove-item">Entfernen</button></div>
  <div class="upload-row">
    <img class="img-preview" src="" id="preview-gallery-__SIDX__-__GIDX__">
    <div>
      <input type="text" name="fields[services][__SIDX__][gallery][__GIDX__][image]" value="" style="margin-bottom:.5rem;width:240px;max-width:55vw;" placeholder="Bild-URL">
      <br>
      <label class="btn btn-outline btn-sm" style="cursor:pointer;">Bild hochladen
        <input type="file" accept="image/*" class="upload-input" data-target="preview-gallery-__SIDX__-__GIDX__" style="display:none;">
      </label>
    </div>
  </div>
  <input type="hidden" name="fields[services][__SIDX__][gallery][__GIDX__][id]" value="">
  <div class="field"><label>Beschreibung unter dem Bild</label><textarea name="fields[services][__SIDX__][gallery][__GIDX__][caption]"></textarea></div>
</div>
</template>

<template id="tpl-credential">
<div class="repeat-item credential-item">
  <div class="item-head"><span>Neuer Eintrag</span><button type="button" class="btn btn-outline btn-sm remove-item">Entfernen</button></div>
  <input type="text" name="fields[credentials][__IDX__]" value="">
</div>
</template>

<template id="tpl-team-member">
<div class="repeat-item">
  <div class="item-head"><span>Neues Mitglied</span><button type="button" class="btn btn-outline btn-sm remove-item">Entfernen</button></div>
  <div class="upload-row">
    <img class="img-preview" src="" id="preview-team-__IDX__">
    <div>
      <input type="text" name="fields[members][__IDX__][photo]" value="" style="margin-bottom:.5rem;width:260px;max-width:55vw;" placeholder="Bild-URL">
      <br>
      <label class="btn btn-outline btn-sm" style="cursor:pointer;">Foto hochladen
        <input type="file" accept="image/*" class="upload-input" data-target="preview-team-__IDX__" style="display:none;">
      </label>
    </div>
  </div>
  <div class="row">
    <input type="hidden" name="fields[members][__IDX__][id]" value="">
    <div class="field"><label>Name (leer lassen, falls nicht öffentlich genannt)</label><input type="text" name="fields[members][__IDX__][name]" value=""></div>
    <div class="field"><label>Rolle / Position</label><input type="text" name="fields[members][__IDX__][role]" value=""></div>
  </div>
  <div class="field"><label>Beschreibung</label><textarea name="fields[members][__IDX__][description]"></textarea></div>
</div>
</template>

<script src="assets/blocks-editor.js?v=<?php echo @filemtime(__DIR__ . '/assets/blocks-editor.js'); ?>"></script>
<script src="assets/admin.js?v=<?php echo @filemtime(__DIR__ . '/assets/admin.js'); ?>"></script>
</body>
</html>
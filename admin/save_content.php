<?php
require_once __DIR__ . '/includes/auth.php';
header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Nicht angemeldet.']);
    exit;
}

require_once __DIR__ . '/../includes/functions.php';

$allowedSections = ['site', 'nav', 'ui', 'home', 'leistungen', 'ueber_mich', 'team', 'datenschutz', 'impressum', 'pdf_downloads'];
$section = $_POST['section'] ?? '';

if (!in_array($section, $allowedSections, true)) {
    echo json_encode(['success' => false, 'message' => 'Unbekannter Bereich.']);
    exit;
}

$fields = $_POST['fields'] ?? [];
if (!is_array($fields)) {
    echo json_encode(['success' => false, 'message' => 'Ungültige Daten.']);
    exit;
}

function clean_recursive($value) {
    if (is_array($value)) {
        return array_map('clean_recursive', $value);
    }
    return trim(str_replace(["\r\n", "\r"], "\n", (string)$value));
}
$fields = clean_recursive($fields);

/* ---------- Absätze (Leerzeile = neuer Absatz) ---------- */
if ($section === 'leistungen' && isset($fields['tech_paragraphs_text'])) {
    $fields['tech_paragraphs'] = split_paragraphs($fields['tech_paragraphs_text']);
    unset($fields['tech_paragraphs_text']);
}
if ($section === 'home' && isset($fields['intro_paragraphs_text'])) {
    $fields['intro_paragraphs'] = split_paragraphs($fields['intro_paragraphs_text']);
    unset($fields['intro_paragraphs_text']);
}
if ($section === 'ueber_mich' && isset($fields['paragraphs_text'])) {
    $fields['paragraphs'] = split_paragraphs($fields['paragraphs_text']);
    unset($fields['paragraphs_text']);
}
if ($section === 'ueber_mich' && isset($fields['credentials']) && is_array($fields['credentials'])) {
    $fields['credentials'] = array_values(array_filter($fields['credentials'], function ($c) { return trim($c) !== ''; }));
}

/* ---------- Listen: Reihenfolge + feste IDs ---------- */
function ensure_ids($list, $prefix) {
    $list = array_values(is_array($list) ? $list : []);
    foreach ($list as &$item) {
        if (!is_array($item)) $item = [];
        if (empty($item['id']) || !preg_match('/^[a-z][a-z0-9]{3,20}$/', $item['id'])) $item['id'] = new_id($prefix);
    }
    unset($item);
    return $list;
}
if ($section === 'leistungen') {
    $fields['services'] = ensure_ids($fields['services'] ?? [], 's');
    foreach ($fields['services'] as &$svc) {
        $svc['faqs'] = ensure_ids($svc['faqs'] ?? [], 'f');
        $svc['gallery'] = ensure_ids($svc['gallery'] ?? [], 'g');
    }
    unset($svc);
}
if ($section === 'team') {
    $fields['members'] = ensure_ids($fields['members'] ?? [], 'm');
}

/* ---------- Rechtstexte: Abschnitte ---------- */
if ($section === 'impressum' || $section === 'datenschutz') {
    $blocks = json_decode($fields['blocks_json'] ?? '[]', true);
    $fields = [
        'title'  => $fields['title'] ?? '',
        'blocks' => normalize_blocks(is_array($blocks) ? $blocks : []),
    ];
}

/* ---------- Beschriftungen: nur bekannte Schlüssel ---------- */
if ($section === 'ui') {
    $clean = [];
    foreach (ui_defaults() as $k => $def) {
        $clean[$k] = (isset($fields[$k]) && $fields[$k] !== '') ? $fields[$k] : $def[0];
    }
    $fields = $clean;
}

$content = load_content();
if ($section === 'site') {
    // Felder, die nicht im Formular stehen, behalten
    $fields = array_replace($content['site'], $fields);
}
if ($section === 'nav') {
    $fields = array_replace($content['nav'], $fields);
}
$content[$section] = $fields;

$ok = write_json_file(CONTENT_PATH, $content);

if ($ok === false) {
    echo json_encode(['success' => false, 'message' => 'Speichern fehlgeschlagen (Schreibrechte prüfen).']);
    exit;
}

echo json_encode(['success' => true]);

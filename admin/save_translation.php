<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/functions.php';

$lang = $_POST['lang'] ?? '';
if (!in_array($lang, translation_langs(), true)) {
    die('Ungültige Sprache.');
}

$posted = $_POST['tr'] ?? [];
if (!is_array($posted)) $posted = [];

$de = load_content();
$existing = load_translation($lang);
$out = [];

foreach (translatable_fields($de) as $grp) {
    foreach ($grp['fields'] as $fl) {
        $k = $fl['key'];
        if (!array_key_exists($k, $posted)) {
            // nicht mitgeschickt → vorhandenen Wert behalten
            $old = arr_get($existing, $k);
            if ($old !== null) arr_set($out, $k, $old);
            continue;
        }
        $raw = str_replace(["\r\n", "\r"], "\n", (string)$posted[$k]);
        switch ($fl['type']) {
            case 'blocks':
                $b = json_decode($raw, true);
                $val = normalize_blocks(is_array($b) ? $b : []);
                break;
            case 'paragraphs':
                $val = split_paragraphs($raw);
                break;
            default:
                $val = trim($raw);
        }
        if (is_filled($val)) arr_set($out, $k, $val);
    }
}

$ok = write_json_file(DATA_DIR . '/content-' . $lang . '.json', $out);

header('Location: translations.php?lang=' . urlencode($lang) . ($ok !== false ? '&saved=1' : '&error=1'));
exit;

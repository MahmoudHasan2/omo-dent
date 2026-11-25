<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/functions.php';

/**
 * Übersetzungen
 * ------------------------------------------------------------------
 * Zeigt JEDES Textfeld der Website, das auf Deutsch vorhanden ist —
 * inklusive aller festen Beschriftungen (Knöpfe, Formular, Meldungen),
 * Impressum und Datenschutz. Fehlende Übersetzungen sind rot markiert.
 */

$lang = $_GET['lang'] ?? 'en';
if (!in_array($lang, translation_langs(), true)) $lang = 'en';
$rtl = is_rtl_lang($lang);
$dirAttr = $rtl ? 'rtl' : 'ltr';

$de = load_content();
$tr = load_translation($lang);
$groups = translatable_fields($de);

$total = 0; $done = 0; $groupStats = [];
foreach ($groups as $grp) {
    $miss = 0;
    foreach ($grp['fields'] as $fl) {
        $total++;
        if (is_filled(arr_get($tr, $fl['key']))) $done++; else $miss++;
    }
    $groupStats[$grp['id']] = $miss;
}
$pct = $total ? round($done / $total * 100) : 100;
$langMissing = [];
foreach (translation_langs() as $tl) $langMissing[$tl] = ($tl === $lang) ? ($total - $done) : translation_missing_count($tl, $de);

function tr_text_value($v) { return is_array($v) ? '' : (string)$v; }
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Übersetzungen – <?php echo e($de['site']['practice_name']); ?> Dashboard</title>
<link rel="stylesheet" href="assets/admin.css?v=<?php echo @filemtime(__DIR__ . '/assets/admin.css'); ?>">
</head>
<body class="<?php echo isset($_GET['only_missing']) ? 'only-missing' : ''; ?>">
<div class="dash">
  <aside class="sidebar">
    <h2><?php echo e($de['site']['practice_name']); ?></h2>
    <p class="sub">Übersetzungen</p>
    <a href="dashboard.php" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;margin-bottom:1rem;">← Zurück zum Dashboard</a>

    <label style="display:flex;gap:.5rem;align-items:center;font-size:.82rem;margin:0 0 1rem .3rem;cursor:pointer;">
      <input type="checkbox" id="only-missing" <?php echo isset($_GET['only_missing']) ? 'checked' : ''; ?>> Nur fehlende anzeigen
    </label>

    <nav class="tr-nav">
      <?php foreach ($groups as $grp): ?>
        <a href="#<?php echo e($grp['id']); ?>">
          <span><?php echo e($grp['title']); ?></span>
          <?php if ($groupStats[$grp['id']]): ?><span class="cnt"><?php echo (int)$groupStats[$grp['id']]; ?> fehlt</span><?php else: ?><span class="ok">✓</span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="logout">
      <a href="logout.php" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;">Abmelden</a>
    </div>
  </aside>

  <main class="main" style="max-width:1050px;">
    <h1>Übersetzungen — <?php echo e(lang_label($lang)); ?></h1>

    <div class="tr-top">
      <?php foreach (translation_langs() as $tl): ?>
        <a class="tr-lang <?php echo $tl === $lang ? 'active' : ''; ?>" href="translations.php?lang=<?php echo e($tl); ?>">
          🌐 <?php echo e(lang_label($tl)); ?>
          <?php if ($langMissing[$tl]): ?><span class="badge missing"><?php echo (int)$langMissing[$tl]; ?></span><?php else: ?><span class="badge read">✓</span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>

    <p class="page-sub" style="margin-bottom:.3rem;">
      <strong><?php echo (int)$done; ?> von <?php echo (int)$total; ?></strong> Textfeldern übersetzt (<?php echo (int)$pct; ?> %).
      Rot markierte Felder fehlen noch — sie würden auf der Website sonst auf Deutsch erscheinen.
      Name, Telefon, Fax, E-Mail, Adresse, Bilder und Farben kommen automatisch aus der deutschen Version.
    </p>
    <div class="tr-progress"><span style="width:<?php echo (int)$pct; ?>%;"></span></div>

    <?php if (isset($_GET['saved'])): ?>
      <div class="card" style="border-color:var(--success);background:rgba(61,155,130,.08);">
        <p style="margin:0;color:var(--success);font-weight:700;">✓ <?php echo e(lang_label($lang)); ?>-Übersetzung gespeichert.</p>
      </div>
    <?php elseif (isset($_GET['error'])): ?>
      <div class="card warn-card"><strong>Speichern fehlgeschlagen</strong> — bitte Schreibrechte des Ordners <code>data/</code> prüfen.</div>
    <?php endif; ?>

    <form method="post" action="save_translation.php" id="tr-form">
      <input type="hidden" name="lang" value="<?php echo e($lang); ?>">
      <input type="hidden" name="field_count" value="<?php echo (int)$total; ?>">

      <?php foreach ($groups as $grp): ?>
        <div class="card <?php echo $groupStats[$grp['id']] ? '' : 'tr-complete'; ?>" id="<?php echo e($grp['id']); ?>">
          <h3><?php echo e($grp['title']); ?></h3>
          <?php foreach ($grp['fields'] as $fl):
            $val = arr_get($tr, $fl['key']);
            $missing = !is_filled($val);
            $name = 'tr[' . $fl['key'] . ']';
          ?>
            <div class="tr-field <?php echo $missing ? 'is-missing' : ''; ?>">
              <div class="tr-label"><span><?php echo e($fl['label']); ?></span><?php if ($missing): ?><span class="tr-flag">● fehlt</span><?php endif; ?></div>

              <?php if ($fl['type'] === 'blocks'): $edId = 'ed-' . str_replace('.', '-', $fl['key']); ?>
                <details class="tr-legal-src">
                  <summary>Deutschen Originaltext anzeigen</summary>
                  <div class="legal-preview"><?php echo render_blocks($fl['de']); ?></div>
                </details>
                <button type="button" class="btn btn-outline btn-sm" style="margin-bottom:.8rem;" data-copy-blocks="<?php echo e($edId); ?>" data-source="<?php echo e(json_encode($fl['de'], JSON_UNESCAPED_UNICODE)); ?>">⇣ Deutsche Abschnitte als Vorlage übernehmen</button>
                <?php if ($rtl): ?><p class="rtl-note">ℹ Im Eingabefeld können Telefonnummern neben arabischem Text vertauscht aussehen — das ist nur die Eingabeansicht. Auf der Website werden alle Zahlen automatisch in der richtigen Reihenfolge angezeigt.</p><?php endif; ?>
                <div class="blocks-editor" id="<?php echo e($edId); ?>" data-dir="<?php echo $dirAttr; ?>"></div>
                <textarea class="blocks-json" name="<?php echo e($name); ?>" hidden><?php echo e(json_encode(is_array($val) ? $val : [], JSON_UNESCAPED_UNICODE)); ?></textarea>

              <?php elseif ($fl['type'] === 'paragraphs'): ?>
                <div class="tr-src"><b>Deutsch</b><?php echo e(implode("\n\n", (array)$fl['de'])); ?></div>
                <textarea name="<?php echo e($name); ?>" dir="<?php echo $dirAttr; ?>" style="min-height:160px;" placeholder="Absätze durch eine Leerzeile trennen"><?php echo e(implode("\n\n", is_array($val) ? $val : [])); ?></textarea>
                <p style="font-size:.72rem;color:var(--text-dim);margin:.3rem 0 0;">Leerzeile = neuer Absatz.</p>

              <?php elseif ($fl['type'] === 'textarea'): ?>
                <div class="tr-src"><b>Deutsch</b><?php echo e($fl['de']); ?></div>
                <textarea name="<?php echo e($name); ?>" dir="<?php echo $dirAttr; ?>"><?php echo e(tr_text_value($val)); ?></textarea>

              <?php else: ?>
                <div class="tr-src"><b>Deutsch</b><?php echo e($fl['de']); ?></div>
                <input type="text" name="<?php echo e($name); ?>" dir="<?php echo $dirAttr; ?>" value="<?php echo e(tr_text_value($val)); ?>">
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>

      <div class="save-bar">
        <button type="submit" class="btn">💾 <?php echo e(lang_label($lang)); ?> speichern</button>
        <span style="font-size:.8rem;color:var(--text-dim);">Platzhalter wie <code>{practice}</code> oder <code>{title}</code> bitte unverändert übernehmen.</span>
      </div>
    </form>
  </main>
</div>
<script src="assets/blocks-editor.js?v=<?php echo @filemtime(__DIR__ . '/assets/blocks-editor.js'); ?>"></script>
<script>
document.getElementById('only-missing').addEventListener('change', function () {
  document.body.classList.toggle('only-missing', this.checked);
});
// Rot-Markierung live entfernen, sobald etwas eingetragen wird
document.querySelectorAll('.tr-field input, .tr-field textarea').forEach(function (inp) {
  var box = inp.closest('.tr-field');
  var check = function () {
    var v = inp.value.trim();
    var filled = inp.classList.contains('blocks-json') ? (v !== '' && v !== '[]') : v !== '';
    box.classList.toggle('is-missing', !filled);
    var flag = box.querySelector('.tr-flag');
    if (flag) flag.style.display = filled ? 'none' : '';
  };
  inp.addEventListener('input', check);
  inp.addEventListener('change', check);
});
</script>
</body>
</html>

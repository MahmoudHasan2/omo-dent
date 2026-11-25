<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$user = get_admin_user();
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) {
        $error = 'Sitzung abgelaufen. Bitte Seite neu laden und erneut versuchen.';
    } else {
        $error = change_admin_password(
            (string)($_POST['current'] ?? ''),
            (string)($_POST['new'] ?? ''),
            (string)($_POST['confirm'] ?? '')
        );
        $success = ($error === '');
    }
}

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Passwort ändern – Dashboard</title>
<link rel="stylesheet" href="assets/admin.css?v=<?php echo @filemtime(__DIR__ . '/assets/admin.css'); ?>">
<style>
.pw-wrap{max-width:520px;}
.pw-field{position:relative;}
.pw-field input{padding-inline-end:3rem !important;}
.pw-toggle{position:absolute;inset-inline-end:.5rem;top:50%;transform:translateY(-50%);border:none;background:transparent;cursor:pointer;font-size:1.05rem;color:var(--text-dim);padding:.3rem;}
.pw-meter{height:6px;border-radius:99px;background:var(--bg-alt);overflow:hidden;margin-top:.45rem;}
.pw-meter span{display:block;height:100%;width:0;transition:width .25s ease,background .25s ease;}
.pw-hint{font-size:.75rem;color:var(--text-dim);margin-top:.3rem;}
.pw-match{font-size:.75rem;margin-top:.3rem;}
.alert-ok{border-color:var(--success);background:rgba(61,155,130,.08);color:var(--success);font-weight:700;}
.alert-err{border-color:rgba(200,80,80,.4);background:rgba(200,80,80,.07);color:#a33;font-weight:700;}
</style>
</head>
<body>
<div class="dash">
  <aside class="sidebar">
    <h2>Dashboard</h2>
    <p class="sub">Angemeldet als <?php echo h($_SESSION['admin_username'] ?? ($user['username'] ?? 'admin')); ?></p>
    <a href="dashboard.php" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;margin-bottom:.6rem;">← Zurück zum Dashboard</a>
    <div class="logout">
      <a href="logout.php" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;">Abmelden</a>
    </div>
  </aside>

  <main class="main">
    <div class="pw-wrap">
      <h1>🔑 Passwort ändern</h1>
      <p class="page-sub">Aus Sicherheitsgründen wird zuerst das aktuelle Passwort abgefragt.</p>

      <?php if ($success): ?>
        <div class="card alert-ok">✓ Das Passwort wurde erfolgreich geändert. Beim nächsten Login gilt das neue Passwort.</div>
      <?php elseif ($error): ?>
        <div class="card alert-err">✕ <?php echo h($error); ?></div>
      <?php endif; ?>

      <form method="post" class="card" autocomplete="off" id="pw-form">
        <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
        <input type="text" name="username" value="<?php echo h($user['username'] ?? 'admin'); ?>" autocomplete="username" hidden>

        <div class="field">
          <label for="current">Aktuelles Passwort</label>
          <div class="pw-field">
            <input type="password" id="current" name="current" autocomplete="current-password" required>
            <button type="button" class="pw-toggle" data-for="current" title="Anzeigen">👁</button>
          </div>
        </div>

        <div class="field">
          <label for="new">Neues Passwort</label>
          <div class="pw-field">
            <input type="password" id="new" name="new" autocomplete="new-password" minlength="8" required>
            <button type="button" class="pw-toggle" data-for="new" title="Anzeigen">👁</button>
          </div>
          <div class="pw-meter"><span id="pw-bar"></span></div>
          <div class="pw-hint" id="pw-label">Mindestens 8 Zeichen — am besten mit Groß-/Kleinbuchstaben, Zahlen und Sonderzeichen.</div>
        </div>

        <div class="field">
          <label for="confirm">Neues Passwort wiederholen</label>
          <div class="pw-field">
            <input type="password" id="confirm" name="confirm" autocomplete="new-password" minlength="8" required>
            <button type="button" class="pw-toggle" data-for="confirm" title="Anzeigen">👁</button>
          </div>
          <div class="pw-match" id="pw-match"></div>
        </div>

        <button type="submit" class="btn" id="pw-submit">💾 Passwort speichern</button>
      </form>

      <?php if (!empty($user['password_changed'])): ?>
        <p class="page-sub" style="font-size:.8rem;">Zuletzt geändert: <?php echo h(date('d.m.Y, H:i', strtotime($user['password_changed']))); ?> Uhr</p>
      <?php endif; ?>
    </div>
  </main>
</div>
<script>
document.querySelectorAll('.pw-toggle').forEach(function (b) {
  b.addEventListener('click', function () {
    var i = document.getElementById(b.dataset.for);
    i.type = i.type === 'password' ? 'text' : 'password';
    b.textContent = i.type === 'password' ? '👁' : '🙈';
  });
});
var nw = document.getElementById('new'), cf = document.getElementById('confirm');
var bar = document.getElementById('pw-bar'), lbl = document.getElementById('pw-label'), match = document.getElementById('pw-match');
function strength(p) {
  var s = 0;
  if (p.length >= 8) s++;
  if (p.length >= 12) s++;
  if (/[a-z]/.test(p) && /[A-Z]/.test(p)) s++;
  if (/\d/.test(p)) s++;
  if (/[^A-Za-z0-9]/.test(p)) s++;
  return s;
}
function update() {
  var p = nw.value, s = strength(p);
  var levels = [['0%', '#c85050', 'Mindestens 8 Zeichen.'], ['25%', '#c85050', 'Sehr schwach'], ['45%', '#e0913a', 'Schwach'], ['65%', '#d4b83a', 'Mittel'], ['85%', '#3D9B82', 'Stark'], ['100%', '#2E8B78', 'Sehr stark']];
  var l = p.length < 8 ? levels[0] : levels[s];
  bar.style.width = p ? (p.length < 8 ? '12%' : l[0]) : '0';
  bar.style.background = l[1];
  lbl.textContent = p ? (p.length < 8 ? 'Noch ' + (8 - p.length) + ' Zeichen bis zum Minimum.' : 'Stärke: ' + l[2]) : 'Mindestens 8 Zeichen — am besten mit Groß-/Kleinbuchstaben, Zahlen und Sonderzeichen.';
  if (cf.value) {
    var ok = cf.value === p;
    match.textContent = ok ? '✓ Passwörter stimmen überein' : '✕ Passwörter stimmen nicht überein';
    match.style.color = ok ? 'var(--success)' : 'var(--danger)';
  } else match.textContent = '';
}
nw.addEventListener('input', update); cf.addEventListener('input', update);
document.getElementById('pw-form').addEventListener('submit', function (e) {
  if (nw.value.length < 8 || nw.value !== cf.value) { e.preventDefault(); update(); }
});
</script>
</body>
</html>
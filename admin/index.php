<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    if (attempt_login($u, $p)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Benutzername oder Passwort ist falsch.';
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard-Login – OMO.DENT</title>
<link rel="stylesheet" href="assets/admin.css?v=<?php echo @filemtime(__DIR__ . '/assets/admin.css'); ?>">
</head>
<body>
  <div class="login-wrap">
    <div class="login-card">
      <h1>OMO.DENT Dashboard</h1>
      <p class="sub">Melden Sie sich an, um die Website zu bearbeiten.</p>
      <?php if ($error): ?>
        <div class="login-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>
      <form method="post" novalidate>
        <label for="username">Benutzername</label>
        <input type="text" id="username" name="username" autocomplete="username" required>
        <label for="password">Passwort</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>
        <button type="submit">Anmelden</button>
      </form>
    </div>
  </div>
</body>
</html>

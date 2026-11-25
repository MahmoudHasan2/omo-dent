<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// الملف الحقيقي: برا مجلد الموقع، ما بينلمس بالرفع
$privateDir = dirname($_SERVER['DOCUMENT_ROOT']) . '/private';
define('USERS_PATH', $privateDir . '/users.json');

// النسخة الأصلية اللي بترفعها مع المشروع (بتنستخدم مرة وحدة بس)
define('USERS_SEED', __DIR__ . '/../../data/users.json');

function ensure_users_file() {
    if (file_exists(USERS_PATH)) return;
    $dir = dirname(USERS_PATH);
    if (!is_dir($dir)) @mkdir($dir, 0750, true);
    if (file_exists(USERS_SEED)) @copy(USERS_SEED, USERS_PATH);
}

function get_admin_user() {
    ensure_users_file();
    $json = @file_get_contents(USERS_PATH);
    $data = json_decode($json, true);
    return is_array($data) ? $data : null;
}

function is_logged_in() {
    return !empty($_SESSION['admin_logged_in']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

function attempt_login($username, $password) {
    $user = get_admin_user();
    if (!$user) return false;
    if ($username === $user['username'] && password_verify($password, $user['password_hash'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        session_regenerate_id(true);
        return true;
    }
    return false;
}

/* ---------- Schutz gegen fremde Formular-Absendungen ---------- */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check($token) {
    return !empty($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Passwort ändern.
 * Gibt '' bei Erfolg zurück, sonst eine Fehlermeldung.
 */
function change_admin_password($current, $new, $confirm) {
    $user = get_admin_user();
    if (!$user) {
        return 'Benutzerdatei nicht gefunden.';
    }
    if (!password_verify($current, $user['password_hash'])) {
        return 'Das aktuelle Passwort ist falsch.';
    }
    if (mb_strlen($new) < 8) {
        return 'Das neue Passwort muss mindestens 8 Zeichen lang sein.';
    }
    if ($new !== $confirm) {
        return 'Die beiden neuen Passwörter stimmen nicht überein.';
    }
    if (password_verify($new, $user['password_hash'])) {
        return 'Das neue Passwort muss sich vom aktuellen unterscheiden.';
    }

    $user['password_hash'] = password_hash($new, PASSWORD_DEFAULT);
    $user['password_changed'] = date('Y-m-d H:i:s');

    $ok = @file_put_contents(USERS_PATH, json_encode($user, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    if ($ok === false) {
        return 'Speichern fehlgeschlagen — bitte Schreibrechte des Ordners private/ prüfen.';
    }

    session_regenerate_id(true);
    return '';
}
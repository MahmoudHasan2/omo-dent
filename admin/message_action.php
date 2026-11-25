<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$messagesFile = __DIR__ . '/../data/messages.json';
$messages = [];
if (file_exists($messagesFile)) {
    $decoded = json_decode(file_get_contents($messagesFile), true);
    if (is_array($decoded)) $messages = $decoded;
}

$id = $_POST['id'] ?? '';
$action = $_POST['action'] ?? '';

if ($id !== '') {
    if ($action === 'delete') {
        // Hochgeladene Anhänge (Gesundheitsdaten) mit löschen
        foreach ($messages as $m) {
            if (($m['id'] ?? '') !== $id) continue;
            foreach ((array)($m['attachments'] ?? []) as $rel) {
                $file = realpath(__DIR__ . '/../' . $rel);
                $dir  = realpath(__DIR__ . '/../uploads/messages');
                if ($file && $dir && strpos($file, $dir . DIRECTORY_SEPARATOR) === 0) @unlink($file);
            }
        }
        $messages = array_values(array_filter($messages, function ($m) use ($id) {
            return ($m['id'] ?? '') !== $id;
        }));
    } elseif ($action === 'toggle_read') {
        foreach ($messages as &$m) {
            if (($m['id'] ?? '') === $id) {
                $m['read'] = empty($m['read']);
            }
        }
        unset($m);
    }
    file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

header('Location: dashboard.php#nachrichten');
exit;

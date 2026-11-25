<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/includes/functions.php';

function respond($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, ui('msg_invalid'));
}

$firstName  = trim($_POST['firstName'] ?? '');
$lastName   = trim($_POST['lastName'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$email      = trim($_POST['email'] ?? '');
$isPatient  = trim($_POST['isPatient'] ?? '');
$message    = trim($_POST['message'] ?? '');

if ($firstName === '' || $lastName === '' || $phone === '' || $email === '' || $isPatient === '' || $message === '') {
    respond(false, ui('msg_required'));
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, ui('msg_email'));
}

$content = load_content();
$to = 'info@omo-zahnarzt.de';
$practiceName = $content['site']['practice_name'] ?? 'Zahnarztpraxis';

$attachmentPaths = [];
$uploadDir = __DIR__ . '/uploads/messages/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if (!empty($_FILES['attachments']) && is_array($_FILES['attachments']['name'])) {
    foreach ($_FILES['attachments']['name'] as $i => $name) {
        if ($_FILES['attachments']['error'][$i] !== UPLOAD_ERR_OK || $name === '') continue;
        $safeName = preg_replace('/[^A-Za-z0-9_.\-]/', '_', $name);
        $unique = date('Ymd_His') . '_' . substr(md5(uniqid('', true)), 0, 6) . '_' . $safeName;
        $dest = $uploadDir . $unique;
        if (move_uploaded_file($_FILES['attachments']['tmp_name'][$i], $dest)) {
            $attachmentPaths[] = 'uploads/messages/' . $unique;
        }
    }
}

$messagesFile = __DIR__ . '/data/messages.json';
$messages = [];
if (file_exists($messagesFile)) {
    $decoded = json_decode(file_get_contents($messagesFile), true);
    if (is_array($decoded)) $messages = $decoded;
}
$entry = [
    'id' => uniqid('msg_', true),
    'date' => date('Y-m-d H:i:s'),
    'firstName' => $firstName,
    'lastName' => $lastName,
    'phone' => $phone,
    'email' => $email,
    'isPatient' => $isPatient,
    'message' => $message,
    'attachments' => $attachmentPaths,
    'read' => false,
];
array_unshift($messages, $entry);
file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$subject = 'Neue Kontaktanfrage von ' . $firstName . ' ' . $lastName;
$bodyLines = [
    'Neue Nachricht über das Kontaktformular der Website:', '',
    'Name: ' . $firstName . ' ' . $lastName,
    'Telefon: ' . $phone,
    'E-Mail: ' . $email,
    'Bereits Patient: ' . $isPatient,
    'Sprache der Website: ' . lang_label(current_lang()), '',
    'Nachricht:', $message,
];
if (!empty($attachmentPaths)) {
    $bodyLines[] = '';
    $bodyLines[] = 'Angehängte Dateien: ' . count($attachmentPaths);
}
$textBody = implode("\r\n", $bodyLines);

$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

if (empty($attachmentPaths)) {
    // ---- Keine Anhänge: normale Text-Mail ----
    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $headers[] = 'From: ' . $practiceName . ' Website <' . $to . '>';
    $headers[] = 'Reply-To: ' . $email;
    $headers[] = 'X-Mailer: PHP/' . phpversion();
    @mail($to, $encodedSubject, $textBody, implode("\r\n", $headers));
} else {
    // ---- Mit Anhängen: multipart/mixed E-Mail mit echten Dateianhängen ----
    $boundary = md5(uniqid('', true));

    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'From: ' . $practiceName . ' Website <' . $to . '>';
    $headers[] = 'Reply-To: ' . $email;
    $headers[] = 'X-Mailer: PHP/' . phpversion();
    $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';

    $mimeBody  = '--' . $boundary . "\r\n";
    $mimeBody .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $mimeBody .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $mimeBody .= $textBody . "\r\n\r\n";

    foreach ($attachmentPaths as $relPath) {
        $fullPath = __DIR__ . '/' . $relPath;
        if (!is_file($fullPath)) continue;

        $fileName = basename($fullPath);
        $fileData = chunk_split(base64_encode(file_get_contents($fullPath)));
        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';

        $mimeBody .= '--' . $boundary . "\r\n";
        $mimeBody .= 'Content-Type: ' . $mimeType . '; name="' . $fileName . '"' . "\r\n";
        $mimeBody .= "Content-Transfer-Encoding: base64\r\n";
        $mimeBody .= 'Content-Disposition: attachment; filename="' . $fileName . '"' . "\r\n\r\n";
        $mimeBody .= $fileData . "\r\n\r\n";
    }
    $mimeBody .= '--' . $boundary . '--';

    @mail($to, $encodedSubject, $mimeBody, implode("\r\n", $headers));
}

respond(true, ui('msg_success'));
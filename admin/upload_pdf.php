<?php
require_once __DIR__ . '/includes/auth.php';
header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Nicht angemeldet.']);
    exit;
}

if (empty($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Keine gültige Datei hochgeladen.']);
    exit;
}

$file = $_FILES['pdf'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if ($ext !== 'pdf') {
    echo json_encode(['success' => false, 'message' => 'Nur PDF-Dateien sind erlaubt.']);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
if ($mime !== 'application/pdf') {
    echo json_encode(['success' => false, 'message' => 'Ungültiger Dateityp — nur PDF erlaubt.']);
    exit;
}

$uploadDir = __DIR__ . '/../uploads/pdfs/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$safeBase = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
$fileName = $safeBase . '_' . substr(md5(uniqid('', true)), 0, 8) . '.pdf';
$destPath = $uploadDir . $fileName;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    echo json_encode(['success' => false, 'message' => 'Datei konnte nicht gespeichert werden.']);
    exit;
}

echo json_encode(['success' => true, 'url' => 'uploads/pdfs/' . $fileName, 'name' => $file['name']]);

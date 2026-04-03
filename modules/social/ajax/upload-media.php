<?php
require_once __DIR__ . '/../includes/_bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') socialJsonResponse(['success' => false], 405);
verifyCsrf();
if (!isset($_FILES['media'])) socialJsonResponse(['success' => false, 'error' => 'Fichier manquant'], 422);

$file = $_FILES['media'];
if ((int) $file['size'] > 50 * 1024 * 1024) socialJsonResponse(['success' => false, 'error' => 'Taille max 50Mo'], 422);
$allowed = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4'];
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
if (!in_array($mime, $allowed, true)) socialJsonResponse(['success' => false, 'error' => 'Type invalide'], 422);

$ext = pathinfo((string) $file['name'], PATHINFO_EXTENSION);
$safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo((string) $file['name'], PATHINFO_FILENAME));
$storageRoot = dirname(__DIR__, 3) . '/storage/social_uploads';
if (!is_dir($storageRoot)) mkdir($storageRoot, 0755, true);
$dest = $storageRoot . '/' . date('Ymd_His') . '_' . $safeName . '.' . strtolower($ext);
move_uploaded_file($file['tmp_name'], $dest);

$stmt = db()->prepare('INSERT INTO social_medias (user_id, nom_fichier, chemin, type, taille, created_at) VALUES (:u,:n,:c,:t,:s,NOW())');
$type = str_starts_with((string) $mime, 'video/') ? 'video' : (str_ends_with((string) $mime, 'gif') ? 'gif' : 'image');
$stmt->execute([':u' => socialUserId(), ':n' => basename($dest), ':c' => $dest, ':t' => $type, ':s' => (int) $file['size']]);
socialJsonResponse(['success' => true, 'media_id' => (int) db()->lastInsertId(), 'url' => '/storage/social_uploads/' . basename($dest)]);

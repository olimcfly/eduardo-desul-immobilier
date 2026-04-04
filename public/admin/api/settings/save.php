<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../core/bootstrap.php';
Auth::requireAuth();
require_once ROOT_PATH . '/includes/settings.php';

header('Content-Type: application/json');

$section = preg_replace('/[^a-z_]/', '', $_POST['section'] ?? '');
$allowed = ['profil', 'site', 'zone', 'api', 'notif', 'smtp', 'securite'];

if (!in_array($section, $allowed, true)) {
    echo json_encode(['success' => false, 'error' => 'Section invalide.']);
    exit;
}

$data = $_POST[$section] ?? [];
if (!is_array($data)) {
    echo json_encode(['success' => false, 'error' => 'Données invalides.']);
    exit;
}

foreach ($data as $key => $value) {
    $fullKey = $section . '_' . preg_replace('/[^a-z0-9_]/', '', (string)$key);
    setting_set($fullKey, (string)$value);
}

echo json_encode(['success' => true, 'message' => 'Paramètres enregistrés.']);

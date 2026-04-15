<?php
declare(strict_types=1);
ob_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/../core/bootstrap.php';
ob_clean();

header('Content-Type: application/json; charset=utf-8');

Auth::requireAuth();
require_once ROOT_PATH . '/includes/settings.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée.']);
    exit;
}

$section = preg_replace('/[^a-z_]/', '', $_POST['section'] ?? '');
$allowed = ['profil', 'site', 'zone', 'api', 'notif', 'smtp', 'securite'];

if (!in_array($section, $allowed, true)) {
    echo json_encode(['success' => false, 'error' => 'Section invalide.']);
    exit;
}

$saved = 0;
foreach ($_POST as $rawKey => $value) {
    if ($rawKey === 'section' || $rawKey === 'csrf_token') {
        continue;
    }
    $key = preg_replace('/[^a-z0-9_]/', '', strtolower((string) $rawKey));
    if ($key === '' || !str_starts_with($key, $section . '_')) {
        continue;
    }
    saveSetting($key, (string) $value);
    $saved++;
}

echo json_encode(['success' => true, 'message' => 'Paramètres enregistrés (' . $saved . ' clé(s)).', 'saved' => $saved]);

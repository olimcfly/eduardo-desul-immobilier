<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../includes/settings.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée.']);
    exit;
}

$section = preg_replace('/[^a-z]/', '', (string)($_POST['section'] ?? ''));

$allowedBySection = [
    'profil' => ['profil_nom', 'profil_email', 'profil_ville', 'profil_photo'],
    'api' => ['api_openai', 'api_google_maps', 'api_gmb_client_id', 'api_fb_access_token', 'api_cloudinary_key'],
];

$allowed = $allowedBySection[$section] ?? [];
if ($allowed === []) {
    echo json_encode(['success' => true, 'message' => 'Rien à enregistrer pour cette section.']);
    exit;
}

$payload = [];
foreach ($allowed as $key) {
    $payload[$key] = trim((string)($_POST[$key] ?? ''));
}

if ($section === 'profil' && $payload['profil_email'] !== '' && !filter_var($payload['profil_email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Adresse email invalide.']);
    exit;
}

$ok = saveSettingsBatch($payload);

if (!$ok) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur lors de la sauvegarde des paramètres.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Paramètres enregistrés avec succès.']);

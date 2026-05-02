<?php
header('Content-Type: application/json; charset=utf-8');

$user = Auth::user();
if (!$user || ($user['role'] ?? '') !== 'superadmin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Accès refusé.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

$setting_key = trim((string) ($_POST['site_development_mode'] ?? null));
if ($setting_key === null || !in_array($setting_key, ['0', '1'], true)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Paramètre invalide.']);
    exit;
}

try {
    $pdo = db();
    $stmt = $pdo->prepare('INSERT INTO settings (user_id, setting_key, setting_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    $stmt->execute([(int) $user['id'], 'site_development_mode', $setting_key]);

    echo json_encode(['ok' => true, 'message' => 'Paramètre mis à jour.']);
} catch (Throwable $e) {
    http_response_code(500);
    error_log('update_setting: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'message' => 'Erreur lors de la mise à jour.']);
}
exit;

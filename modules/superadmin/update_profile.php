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

$name = trim((string) ($_POST['name'] ?? ''));
if ($name === '' || mb_strlen($name) > 100) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Nom invalide (1–100 caractères).']);
    exit;
}

try {
    $stmt = db()->prepare('UPDATE users SET name = ? WHERE id = ? AND role = "superadmin"');
    $stmt->execute([$name, (int) $user['id']]);
    if ($stmt->rowCount() === 0) {
        echo json_encode(['ok' => true, 'message' => 'Aucun changement détecté.']);
        exit;
    }

    echo json_encode(['ok' => true, 'message' => 'Nom mis à jour.']);
} catch (Throwable $e) {
    http_response_code(500);
    error_log('update_profile: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'message' => 'Erreur lors de la mise à jour.']);
}
exit;

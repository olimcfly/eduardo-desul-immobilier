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

$userId   = (int) ($_POST['user_id'] ?? 0);
$isActive = (int) ($_POST['is_active'] ?? 0) === 1 ? 1 : 0;

if ($userId <= 0) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Utilisateur invalide.']);
    exit;
}

try {
    // Sécurité : on ne peut pas désactiver un superadmin
    $stmt = db()->prepare('UPDATE users SET is_active = ? WHERE id = ? AND role != "superadmin"');
    $stmt->execute([$isActive, $userId]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'message' => 'Utilisateur introuvable ou non modifiable.']);
        exit;
    }

    echo json_encode(['ok' => true, 'is_active' => $isActive]);
} catch (Throwable $e) {
    http_response_code(500);
    error_log('toggle_user: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'message' => 'Erreur lors de la mise à jour.']);
}
exit;

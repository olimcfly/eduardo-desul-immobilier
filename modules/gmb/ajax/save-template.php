<?php
require_once '../../../core/bootstrap.php';
require_once '../includes/GmbService.php';

header('Content-Type: application/json');
function gmb_json(bool $success, string $message, array $data = [], int $status = 200): void {
    http_response_code($status);
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}
if (!Auth::check()) { gmb_json(false, 'Non autorisé', [], 401); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { gmb_json(false, 'Méthode non autorisée', [], 405); }
if (!verifyCsrf((string) ($_POST['csrf_token'] ?? ''))) { gmb_json(false, 'Token CSRF invalide.', [], 403); }

try {
    $service = new GmbService((int) Auth::user()['id']);
    $ok = $service->saveTemplate($_POST);
    gmb_json($ok, $ok ? 'Template sauvegardé.' : 'Erreur template');
} catch (Throwable $e) {
    gmb_json(false, $e->getMessage() ?: 'Erreur template', [], 422);
}

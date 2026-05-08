<?php
declare(strict_types=1);
/**
 * Même logique que l'ancien delete listings — bootstrap depuis admin/api/.
 */
ob_start();
require_once dirname(__DIR__, 3) . '/core/bootstrap.php';
ob_clean();

header('Content-Type: application/json; charset=utf-8');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$payload = [];
$raw = file_get_contents('php://input') ?: '';
$d = json_decode($raw, true);
if (is_array($d)) {
    $payload = $d;
}

if (!hash_equals((string) ($_SESSION['csrf_token'] ?? ''), (string) ($payload['csrf_token'] ?? ''))) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Session expirée, rechargez la page.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$id = isset($payload['id']) ? (int) $payload['id'] : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Identifiant invalide.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = db();

try {
    $del = $pdo->prepare('DELETE FROM biens WHERE id = :id');
    $del->execute([':id' => $id]);

    if ($del->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Bien introuvable ou déjà supprimé.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Annonce supprimée.', 'id' => $id], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('[listings delete] ' . $e->getMessage());
    $msg = (defined('APP_DEBUG') && APP_DEBUG) ? $e->getMessage() : 'Impossible de supprimer (liens vers ce bien ailleurs en base).';

    echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
}

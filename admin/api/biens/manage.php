<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

function biensManageJson(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function biensManageInput(): array
{
    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);

    return is_array($data) ? $data : [];
}

if (!Auth::check()) {
    biensManageJson(['success' => false, 'message' => 'Authentification requise.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    biensManageJson(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
}

$input = biensManageInput();
if (!hash_equals(csrfToken(), (string) ($input['csrf_token'] ?? ''))) {
    biensManageJson(['success' => false, 'message' => 'Token CSRF invalide.'], 403);
}

$action = (string) ($input['action'] ?? '');
$id = (int) ($input['id'] ?? 0);
$pdo = db();

if ($id <= 0) {
    biensManageJson(['success' => false, 'message' => 'Bien invalide.'], 422);
}

try {
    if ($action === 'status') {
        $status = (string) ($input['status'] ?? '');
        $allowed = ['actif', 'pending', 'vendu', 'archive'];
        if (!in_array($status, $allowed, true)) {
            biensManageJson(['success' => false, 'message' => 'Statut invalide.'], 422);
        }

        $stmt = $pdo->prepare('UPDATE biens SET statut = :statut, updated_at = NOW() WHERE id = :id LIMIT 1');
        $stmt->execute([':statut' => $status, ':id' => $id]);
        biensManageJson(['success' => true, 'message' => 'Statut mis à jour.']);
    }

    if ($action === 'sort') {
        $sort = (int) ($input['sort_order'] ?? 0);
        $stmt = $pdo->prepare('UPDATE biens SET sort_order = :sort_order, updated_at = NOW() WHERE id = :id LIMIT 1');
        $stmt->execute([':sort_order' => $sort, ':id' => $id]);
        biensManageJson(['success' => true, 'message' => 'Ordre mis à jour.']);
    }

    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM bien_photos WHERE bien_id = :id');
        $stmt->execute([':id' => $id]);
        $stmt = $pdo->prepare('DELETE FROM biens WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        biensManageJson(['success' => true, 'message' => 'Bien supprimé.']);
    }

    biensManageJson(['success' => false, 'message' => 'Action inconnue.'], 400);
} catch (Throwable $e) {
    error_log('[biens-manage] ' . $e->getMessage());
    biensManageJson(['success' => false, 'message' => 'Erreur pendant la mise à jour.'], 500);
}

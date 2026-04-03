<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/core/bootstrap.php';
require_once dirname(__DIR__) . '/services/KeywordTracker.php';

Auth::requireAuth('/admin/login');
header('Content-Type: application/json; charset=utf-8');

$userId = (int)(Auth::user()['id'] ?? 0);
$tracker = new KeywordTracker(db(), $userId);
$action = (string)($_REQUEST['action'] ?? 'refresh');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
    }

    if ($action === 'save') {
        $id = $tracker->upsertKeyword($_POST);
        echo json_encode(['success' => true, 'id' => $id]);
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        echo json_encode(['success' => $tracker->deleteKeyword($id)]);
        exit;
    }

    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    $stmt = db()->prepare('SELECT keyword, target_url FROM seo_keywords WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        throw new RuntimeException('Mot-clé introuvable.');
    }

    $position = $tracker->checkPosition((string)$row['keyword'], (string)$row['target_url']);
    $update = db()->prepare('UPDATE seo_keywords SET previous_position = current_position, current_position = ?, last_checked_at = NOW() WHERE id = ? AND user_id = ?');
    $update->execute([$position, $id, $userId]);
    $tracker->saveHistory($id, $position);

    echo json_encode(['success' => true, 'position' => $position]);
} catch (Throwable $e) {
    error_log('[' . date('Y-m-d H:i:s') . '] keyword: ' . $e->getMessage() . PHP_EOL, 3, dirname(__DIR__, 3) . '/logs/seo.log');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

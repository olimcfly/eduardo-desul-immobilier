<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../modules/gmb/includes/GmbService.php';

$pdo = db();
$stmt = $pdo->query('SELECT DISTINCT user_id FROM gmb_sync_jobs WHERE status IN ("pending","running") ORDER BY user_id ASC');
$userIds = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];

$totalProcessed = 0;
foreach ($userIds as $userId) {
    $service = new GmbService((int) $userId);
    $totalProcessed += $service->processSyncQueue(10);
}

echo json_encode([
    'ok' => true,
    'processed' => $totalProcessed,
    'users' => count($userIds),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;

<?php
require_once __DIR__ . '/../modules/social/includes/_bootstrap.php';
$pdo = db();
$users = $pdo->query('SELECT DISTINCT user_id FROM social_posts')->fetchAll(PDO::FETCH_COLUMN);
$service = new SocialService();
foreach ($users as $userId) {
    $service->syncStats((int) $userId);
}
echo sprintf("[%s] Sync complete for %d user(s)\n", date('Y-m-d H:i:s'), count($users));

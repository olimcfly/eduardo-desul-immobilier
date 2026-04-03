<?php
require_once __DIR__ . '/../includes/_bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') socialJsonResponse(['success' => false], 405);
verifyCsrf();
$postId = (int) ($_POST['post_id'] ?? 0);
$stmt = db()->prepare('DELETE FROM social_posts WHERE id = :id AND user_id = :u');
$ok = $stmt->execute([':id' => $postId, ':u' => socialUserId()]);
socialJsonResponse(['success' => (bool) $ok]);

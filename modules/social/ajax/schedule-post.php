<?php
require_once __DIR__ . '/../includes/_bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') socialJsonResponse(['success' => false], 405);
verifyCsrf();
$postId = (int) ($_POST['post_id'] ?? 0);
$datetime = (string) ($_POST['datetime'] ?? '');
if (strtotime($datetime) <= time()) socialJsonResponse(['success' => false, 'error' => 'Date future obligatoire'], 422);

$conflictStmt = db()->prepare('SELECT COUNT(*) FROM social_posts WHERE user_id = :u AND planifie_at = :d AND statut = "planifie"');
$conflictStmt->execute([':u' => socialUserId(), ':d' => date('Y-m-d H:i:s', strtotime($datetime))]);
if ((int) $conflictStmt->fetchColumn() > 0) socialJsonResponse(['success' => false, 'error' => 'Conflit de créneau'], 409);

$ok = (new SocialService())->schedulePost($postId, date('Y-m-d H:i:s', strtotime($datetime)), (array) ($_POST['reseaux'] ?? []));
socialJsonResponse(['success' => $ok, 'planifie_at' => date('Y-m-d H:i:s', strtotime($datetime))]);

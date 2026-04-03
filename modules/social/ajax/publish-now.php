<?php
require_once __DIR__ . '/../includes/_bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') socialJsonResponse(['success' => false], 405);
verifyCsrf();
$postId = (int) ($_POST['post_id'] ?? 0);
$reseaux = $_POST['reseaux'] ?? [];
$reseaux = is_array($reseaux) ? $reseaux : [];
$results = (new SocialService())->publishPost($postId, $reseaux);
socialJsonResponse(['success' => in_array(true, $results, true), 'results' => $results]);

<?php
require_once __DIR__ . '/../includes/_bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') socialJsonResponse(['success' => false], 405);
verifyCsrf();
$reseau = (string) ($_POST['reseau'] ?? 'facebook');
$categorie = (string) ($_POST['categorie'] ?? 'autre');
$context = json_decode((string) ($_POST['context'] ?? '{}'), true) ?: [];
$generator = new ContentGenerator(socialUserId());
$contenu = $generator->generatePost($reseau, $categorie, $context);
$hashtags = $generator->suggestHashtags($contenu, $reseau, 20);
socialJsonResponse(['success' => true, 'contenu' => $contenu, 'hashtags' => $hashtags]);

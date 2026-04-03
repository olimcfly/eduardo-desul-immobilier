<?php
require_once __DIR__ . '/../includes/_bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') socialJsonResponse(['success' => false, 'error' => 'Méthode invalide'], 405);
verifyCsrf();

$userId = socialUserId();
$contenu = trim((string) ($_POST['contenu'] ?? ''));
$reseaux = $_POST['reseaux'] ?? ['facebook'];
$reseaux = is_array($reseaux) ? array_values(array_unique(array_filter($reseaux))) : ['facebook'];
if ($contenu === '') socialJsonResponse(['success' => false, 'error' => 'Contenu obligatoire'], 422);

$postId = (int) ($_POST['post_id'] ?? 0);
$pdo = db();
if ($postId > 0) {
    $stmt = $pdo->prepare('UPDATE social_posts SET titre=:t, contenu=:c, reseaux=:r, type_post=:tp, statut="brouillon", updated_at=NOW() WHERE id=:id AND user_id=:u');
    $ok = $stmt->execute([':t' => trim((string) ($_POST['titre'] ?? '')), ':c' => $contenu, ':r' => json_encode($reseaux), ':tp' => (string) ($_POST['type_post'] ?? 'post'), ':id' => $postId, ':u' => $userId]);
} else {
    $stmt = $pdo->prepare('INSERT INTO social_posts (user_id, titre, contenu, reseaux, type_post, statut, tags, medias, categorie, created_at) VALUES (:u,:t,:c,:r,:tp,"brouillon",:tags,:m,:cat,NOW())');
    $ok = $stmt->execute([':u' => $userId, ':t' => trim((string) ($_POST['titre'] ?? '')), ':c' => $contenu, ':r' => json_encode($reseaux), ':tp' => (string) ($_POST['type_post'] ?? 'post'), ':tags' => json_encode(explode(' ', trim((string) ($_POST['hashtags'] ?? '')))), ':m' => json_encode([]), ':cat' => (string) ($_POST['categorie'] ?? 'autre')]);
    $postId = $ok ? (int) $pdo->lastInsertId() : 0;
}

socialJsonResponse(['success' => (bool) $ok, 'post_id' => $postId]);

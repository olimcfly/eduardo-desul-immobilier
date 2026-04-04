<?php
require_once __DIR__ . '/../../../config/database.php';

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $pdo->prepare("DELETE FROM blog_articles WHERE id=?")->execute([$id]);
}
header('Location: ../accueil.php?success=deleted');
exit;

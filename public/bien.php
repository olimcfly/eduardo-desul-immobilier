<?php
/**
 * Redirection permanente depuis l'ancien format bien.php?id=X
 * vers la nouvelle URL /bien/{slug}
 */

declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    $pdo  = db();
    $stmt = $pdo->prepare("SELECT slug FROM biens WHERE id = :id AND statut != 'Archivé' LIMIT 1");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && !empty($row['slug'])) {
        header('Location: /bien/' . rawurlencode($row['slug']), true, 301);
        exit;
    }
}

// Bien introuvable → 301 vers la liste
header('Location: /biens', true, 301);
exit;

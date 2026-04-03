<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';

header('Content-Type: application/json');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

try {
    $userId = (int)$_SESSION['user_id'];
    $ville = trim((string)($_POST['ville'] ?? ''));
    $slug = trim((string)($_POST['slug'] ?? ''));

    if ($ville === '' || $slug === '') {
        throw new InvalidArgumentException('ville et slug sont obligatoires');
    }

    $stmt = db()->prepare('INSERT INTO seo_fiches_villes (user_id, ville, slug, code_postal, titre_seo, meta_desc, contenu, h1, prix_m2, nb_habitants, published, last_updated, created_at) VALUES (:user_id, :ville, :slug, :code_postal, :titre_seo, :meta_desc, :contenu, :h1, :prix_m2, :nb_habitants, :published, NOW(), NOW()) ON DUPLICATE KEY UPDATE ville = VALUES(ville), code_postal = VALUES(code_postal), titre_seo = VALUES(titre_seo), meta_desc = VALUES(meta_desc), contenu = VALUES(contenu), h1 = VALUES(h1), prix_m2 = VALUES(prix_m2), nb_habitants = VALUES(nb_habitants), published = VALUES(published), last_updated = NOW()');

    $stmt->execute([
        'user_id' => $userId,
        'ville' => mb_substr($ville, 0, 100),
        'slug' => mb_substr($slug, 0, 100),
        'code_postal' => $_POST['code_postal'] ?? null,
        'titre_seo' => $_POST['titre_seo'] ?? null,
        'meta_desc' => $_POST['meta_desc'] ?? null,
        'contenu' => $_POST['contenu'] ?? null,
        'h1' => $_POST['h1'] ?? null,
        'prix_m2' => ($_POST['prix_m2'] ?? '') !== '' ? (float)$_POST['prix_m2'] : null,
        'nb_habitants' => ($_POST['nb_habitants'] ?? '') !== '' ? (int)$_POST['nb_habitants'] : null,
        'published' => !empty($_POST['published']) ? 1 : 0,
    ]);

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

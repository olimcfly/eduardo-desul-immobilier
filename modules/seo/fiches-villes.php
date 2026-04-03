<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/bootstrap.php';

if (!Auth::check()) {
    header('Location: /admin/login.php');
    exit;
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM seo_fiches_villes WHERE user_id = :user_id ORDER BY created_at DESC');
$stmt->execute(['user_id' => $userId]);
$fiches = $stmt->fetchAll() ?: [];
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>Fiches villes</title><link rel="stylesheet" href="/modules/seo/assets/seo.css"></head>
<body><div class="seo-wrap"><h1>Fiches villes</h1>
<form id="fiche-ville-form">
<input name="ville" placeholder="Bordeaux" required>
<input name="code_postal" placeholder="33000">
<input name="slug" placeholder="bordeaux" required>
<button type="submit">Enregistrer</button>
</form>
<ul><?php foreach ($fiches as $fiche): ?><li><?= htmlspecialchars((string)$fiche['ville']) ?> (<?= htmlspecialchars((string)$fiche['slug']) ?>) — <?= (int)$fiche['published'] ? 'Publié' : 'Brouillon' ?></li><?php endforeach; ?></ul>
</div><script src="/modules/seo/assets/seo.js"></script></body></html>

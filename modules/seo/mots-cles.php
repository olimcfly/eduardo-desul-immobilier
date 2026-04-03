<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/includes/KeywordTracker.php';

if (!Auth::check()) {
    header('Location: /admin/login.php');
    exit;
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$tracker = new KeywordTracker(db(), $userId);
$keywords = $tracker->listKeywords();
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>Mots-clés SEO</title><link rel="stylesheet" href="/modules/seo/assets/seo.css"></head>
<body>
<div class="seo-wrap">
    <h1>Mots-clés suivis</h1>
    <form id="keyword-form">
        <input name="keyword" placeholder="Ex: estimation appartement bordeaux" required>
        <input name="target_url" placeholder="/estimation-bordeaux">
        <button type="submit">Ajouter</button>
    </form>
    <table><thead><tr><th>Mot-clé</th><th>URL cible</th><th>Position</th><th>Action</th></tr></thead><tbody>
    <?php foreach ($keywords as $keyword): ?>
        <tr>
            <td><?= htmlspecialchars((string)$keyword['keyword']) ?></td>
            <td><?= htmlspecialchars((string)($keyword['target_url'] ?? '-')) ?></td>
            <td><?= $keyword['position'] !== null ? (int)$keyword['position'] : '-' ?></td>
            <td><button type="button" class="check-position" data-id="<?= (int)$keyword['id'] ?>">Vérifier</button></td>
        </tr>
    <?php endforeach; ?>
    </tbody></table>
</div>
<script src="/modules/seo/assets/seo.js"></script>
</body></html>

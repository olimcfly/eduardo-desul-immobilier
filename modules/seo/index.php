<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/includes/SeoService.php';

if (!Auth::check()) {
    header('Location: /admin/login.php');
    exit;
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$service = new SeoService(db(), $userId);
$stats = $service->getHubStats();
$advisor = $service->getAdvisorIdentity();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hub SEO</title>
    <link rel="stylesheet" href="/modules/seo/assets/seo.css">
</head>
<body>
<div class="seo-wrap">
    <h1>Hub SEO — <?= htmlspecialchars($advisor['name']) ?></h1>
    <p>Zone principale : <strong><?= htmlspecialchars($advisor['zone']) ?></strong></p>

    <div class="seo-grid">
        <a class="seo-card" href="/modules/seo/mots-cles.php"><h3>Mots-clés</h3><p><?= (int)$stats['keywords_total'] ?> suivis · <?= (int)$stats['keywords_top10'] ?> en top 10</p></a>
        <a class="seo-card" href="/modules/seo/fiches-villes.php"><h3>Fiches villes</h3><p><?= (int)$stats['fiches_published'] ?>/<?= (int)$stats['fiches_total'] ?> publiées</p></a>
        <a class="seo-card" href="/modules/seo/sitemap.php"><h3>Sitemap</h3><p><?= (int)$stats['sitemap_included'] ?> URL incluses</p></a>
        <a class="seo-card" href="/modules/seo/performance.php"><h3>Performance</h3><p>Score SEO moyen: <?= $stats['audit_avg_seo'] !== null ? (int)$stats['audit_avg_seo'] . '/100' : 'N/A' ?></p></a>
    </div>
</div>
<script src="/modules/seo/assets/seo.js"></script>
</body>
</html>

<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/includes/SitemapGenerator.php';

if (!Auth::check()) {
    header('Location: /admin/login.php');
    exit;
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$generator = new SitemapGenerator(db(), $userId);
$urls = $generator->listUrls();
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>Sitemap SEO</title><link rel="stylesheet" href="/modules/seo/assets/seo.css"></head>
<body><div class="seo-wrap"><h1>Sitemap XML</h1>
<button id="generate-sitemap" type="button">Générer le sitemap</button>
<ul><?php foreach ($urls as $url): ?><li><?= htmlspecialchars((string)$url['url']) ?> — <?= (int)$url['included'] ? 'Incluse' : 'Exclue' ?></li><?php endforeach; ?></ul>
</div><script src="/modules/seo/assets/seo.js"></script></body></html>

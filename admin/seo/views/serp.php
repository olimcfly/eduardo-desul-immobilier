<?php
declare(strict_types=1);

$selectedKeyword = (string)($_GET['keyword'] ?? 'immobilier aix-en-provence');
$serpResults = $seoService->getSerpSimulation($selectedKeyword);

require __DIR__ . '/_layout_top.php';
?>
<section class="card" data-view="serp">
    <form method="get" class="inline-form">
        <input type="hidden" name="action" value="serp">
        <input type="text" name="keyword" value="<?= seo_h($selectedKeyword) ?>" aria-label="Mot-clé SERP">
        <button type="submit" class="btn-primary">Simuler</button>
    </form>
</section>

<section class="card serp-list">
    <?php foreach ($serpResults as $result): ?>
        <div class="serp-result">
            <div class="result-header">
                <img src="<?= seo_h($result['favicon_url'] ?? 'https://www.google.com/favicon.ico') ?>" class="favicon" alt="Favicon">
                <div>
                    <div class="result-title"><?= seo_h($result['title']) ?></div>
                    <div class="result-url"><?= seo_h($result['url']) ?></div>
                </div>
            </div>
            <div class="result-desc"><?= seo_h($result['meta_description']) ?></div>
            <div class="result-actions">
                <button class="result-btn" data-action="analyze">Analyser</button>
                <button class="result-btn" data-action="compare">Comparer</button>
            </div>
        </div>
    <?php endforeach; ?>
</section>

<section class="card">
    <h3>Opportunités d'optimisation</h3>
    <ul>
        <li>Optimiser le title avec le mot-clé principal en début de phrase.</li>
        <li>Ajouter 2 liens internes vers des guides complémentaires.</li>
        <li>Structurer le contenu concurrentiel avec H2/H3 orientés FAQ locale.</li>
    </ul>
</section>
<?php require __DIR__ . '/_layout_bottom.php'; ?>

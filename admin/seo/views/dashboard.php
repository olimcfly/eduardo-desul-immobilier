<?php
declare(strict_types=1);

$filters = [
    'status' => $_GET['status'] ?? '',
    'persona_id' => $_GET['persona_id'] ?? '',
    'region' => $_GET['region'] ?? '',
    'consciousness_level' => $_GET['consciousness_level'] ?? '',
];
$page = (int)($_GET['page'] ?? 1);
$data = $blogService->getDashboardData($filters, $page);
$personas = $blogService->getPersonas();

require __DIR__ . '/_layout_top.php';
?>
<section class="card">
    <form class="filters-grid" method="get" aria-label="Filtres dashboard">
        <input type="hidden" name="action" value="dashboard">
        <select name="status">
            <option value="">Tous les statuts</option>
            <?php foreach (['draft', 'published', 'review'] as $status): ?>
                <option value="<?= seo_h($status) ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= seo_h(ucfirst($status)) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="persona_id">
            <option value="">Tous les personas</option>
            <?php foreach ($personas as $persona): ?>
                <option value="<?= (int)$persona['id'] ?>" <?= (string)$filters['persona_id'] === (string)$persona['id'] ? 'selected' : '' ?>><?= seo_h($persona['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="region" placeholder="Région" value="<?= seo_h($filters['region']) ?>">
        <select name="consciousness_level">
            <option value="">Niveau de conscience</option>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <option value="<?= $i ?>" <?= (string)$filters['consciousness_level'] === (string)$i ? 'selected' : '' ?>>Niveau <?= $i ?></option>
            <?php endfor; ?>
        </select>
        <button class="btn-primary" type="submit">Filtrer</button>
    </form>
</section>

<section class="stats-grid">
    <article class="card">
        <h3>Trafic total</h3>
        <p class="big-number"><?= number_format((int)$data['stats']['traffic']['current'], 0, ',', ' ') ?></p>
        <small>Évolution: <?= $data['stats']['traffic']['delta_percent'] >= 0 ? '⬆️' : '⬇️' ?> <?= seo_h((string)$data['stats']['traffic']['delta_percent']) ?>%</small>
    </article>
    <article class="card">
        <h3>Répartition positions</h3>
        <div class="mini-chart bars">
            <?php foreach ($data['stats']['positions'] as $bucket): ?>
                <div class="bar" style="--value: <?= (int)$bucket['article_count'] ?>">
                    <span><?= seo_h($bucket['position_bucket']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
    <article class="card">
        <h3>Golden Ratio</h3>
        <div class="mini-chart pie">
            <?php foreach ($data['stats']['golden_ratios'] as $ratio): ?>
                <div class="badge"><?= seo_h($ratio['ratio_bucket']) ?> (<?= (int)$ratio['ratio_count'] ?>)</div>
            <?php endforeach; ?>
        </div>
    </article>
</section>

<section class="card">
    <div class="table-actions">
        <a class="btn-secondary" href="?action=dashboard&export=csv">Exporter CSV</a>
        <a class="btn-ghost" href="?action=dashboard&export=pdf">Exporter PDF</a>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
            <tr>
                <th>Titre / slug</th>
                <th>Position</th>
                <th>Trafic</th>
                <th>Mots-clés</th>
                <th>Golden Ratio</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($data['articles'] as $article): ?>
                <tr>
                    <td>
                        <a href="?action=editor&id=<?= (int)$article['id'] ?>"><strong><?= seo_h($article['title']) ?></strong></a>
                        <div class="muted">/<?= seo_h($article['slug']) ?></div>
                    </td>
                    <td><?= (int)$article['position'] ?> <?= (int)$article['position_trend'] >= 0 ? '⬆️' : '⬇️' ?></td>
                    <td><span class="mini-chart">📈 <?= number_format((int)$article['monthly_traffic'], 0, ',', ' ') ?></span></td>
                    <td><span class="badge"><?= seo_h($article['main_keyword'] ?? '-') ?></span></td>
                    <td><?= number_format((float)($article['golden_ratio'] ?? 0), 2, ',', ' ') ?></td>
                    <td>
                        <a class="btn-ghost" href="?action=editor&id=<?= (int)$article['id'] ?>">Éditer</a>
                        <button class="btn-secondary" type="button">Publier</button>
                        <button class="btn-secondary" type="button">Archiver</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/_layout_bottom.php'; ?>

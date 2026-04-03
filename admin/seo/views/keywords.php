<?php
declare(strict_types=1);

$filters = [
    'status' => $_GET['status'] ?? '',
    'intent' => $_GET['intent'] ?? '',
    'volume_min' => $_GET['volume_min'] ?? '',
    'volume_max' => $_GET['volume_max'] ?? '',
    'competition_band' => $_GET['competition_band'] ?? '',
];
$keywords = $seoService->getKeywords($filters);
$opportunities = $seoService->getKeywordOpportunities();

require __DIR__ . '/_layout_top.php';
?>
<section class="card">
    <form method="get" class="filters-grid" aria-label="Filtres mots-clés">
        <input type="hidden" name="action" value="keywords">
        <input type="number" name="volume_min" placeholder="Volume min" value="<?= seo_h((string)$filters['volume_min']) ?>">
        <input type="number" name="volume_max" placeholder="Volume max" value="<?= seo_h((string)$filters['volume_max']) ?>">
        <select name="competition_band">
            <option value="">Concurrence</option>
            <option value="low">Faible</option>
            <option value="mid">Moyenne</option>
            <option value="high">Élevée</option>
        </select>
        <select name="intent">
            <option value="">Intention</option>
            <option value="informational">🔍 Informationnelle</option>
            <option value="commercial">💰 Commerciale</option>
            <option value="transactional">🛒 Transactionnelle</option>
        </select>
        <button type="submit" class="btn-primary">Appliquer</button>
    </form>
</section>

<section class="card">
    <h3>Opportunités (Golden Ratio &gt; 1.5)</h3>
    <div class="opps-grid">
        <?php foreach ($opportunities as $opp): ?>
            <div class="badge"><?= seo_h($opp['keyword']) ?> · GR <?= number_format((float)$opp['golden_ratio'], 2, ',', ' ') ?></div>
        <?php endforeach; ?>
    </div>
</section>

<section class="card">
    <div class="table-actions">
        <a class="btn-secondary" href="?action=keywords&export=csv">Exporter CSV</a>
        <a class="btn-ghost" href="?action=keywords&import=csv">Importer CSV</a>
    </div>
    <table class="table">
        <thead>
        <tr>
            <th>Mot-clé</th><th>Volume</th><th>Concurrence</th><th>Golden Ratio</th><th>Intention</th><th>Statut</th><th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($keywords as $keyword): ?>
            <tr>
                <td><a href="?action=serp&keyword=<?= urlencode((string)$keyword['keyword']) ?>"><?= seo_h($keyword['keyword']) ?></a></td>
                <td><?= (int)$keyword['search_volume'] ?></td>
                <td><?= (int)$keyword['competition'] ?></td>
                <td><?= number_format((float)$keyword['golden_ratio'], 2, ',', ' ') ?></td>
                <td><?= seo_h((string)$keyword['search_intent']) ?></td>
                <td><span class="badge"><?= seo_h((string)$keyword['status']) ?></span></td>
                <td>
                    <button class="btn-secondary" type="button">Valider</button>
                    <button class="btn-ghost" type="button">Rejeter</button>
                    <button class="btn-primary" type="button">Ajouter à un article</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require __DIR__ . '/_layout_bottom.php'; ?>

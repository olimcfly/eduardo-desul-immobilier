<?php
$userId = (int)(Auth::user()['id'] ?? 0);
$tracker = new KeywordTracker(db(), $userId);
$filter = isset($_GET['filter']) ? (string)$_GET['filter'] : 'all';
$keywords = $tracker->listKeywords($filter);
$total = count($keywords);
$top10 = count(array_filter($keywords, static fn($k) => (int)($k['current_position'] ?? 999) <= 10));
$top3 = count(array_filter($keywords, static fn($k) => (int)($k['current_position'] ?? 999) <= 3));
$avgPos = $tracker->getAveragePosition($userId);
?>
<section class="seo-section">
    <div class="seo-breadcrumb"><a href="/admin?module=seo">Accueil</a> &gt; SEO &gt; Mots-clés</div>
    <h2>Mots-clés</h2>

    <div class="kpi-grid">
        <div class="kpi"><strong><?= $total ?></strong><span>Total suivis</span></div>
        <div class="kpi"><strong><?= $top10 ?></strong><span>En Top 10</span></div>
        <div class="kpi"><strong><?= $top3 ?></strong><span>En Top 3</span></div>
        <div class="kpi"><strong><?= number_format($avgPos, 1, ',', ' ') ?></strong><span>Position moyenne</span></div>
    </div>

    <div class="seo-toolbar">
        <form method="post" action="/modules/seo/ajax/check-keyword.php?action=save" class="inline-form">
            <?= csrfField() ?>
            <input type="hidden" name="id" value="0">
            <input type="text" name="keyword" placeholder="Mot-clé" maxlength="190" required>
            <input type="url" name="target_url" placeholder="URL cible" required>
            <input type="number" name="estimated_volume" placeholder="Volume" min="0">
            <input type="number" name="difficulty" placeholder="Difficulté" min="0" max="100">
            <button type="submit">Ajouter</button>
        </form>

        <div class="actions">
            <a href="?module=seo&action=keywords&filter=all">Tous</a>
            <a href="?module=seo&action=keywords&filter=top3">Top 3</a>
            <a href="?module=seo&action=keywords&filter=top10">Top 10</a>
            <a href="?module=seo&action=keywords&filter=out">Hors classement</a>
            <button type="button" onclick="exportKeywordsCSV()">Export CSV</button>
        </div>
    </div>

    <div class="table-wrap">
        <table class="keywords-table" id="keywords-table">
            <thead>
            <tr>
                <th>Keyword + URL cible</th><th>Position</th><th>Badge</th><th>Volume</th><th>Difficulté</th><th>Dernière vérif</th><th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($keywords as $row): ?>
                <?php
                $currentPos = $row['current_position'] !== null ? (int)$row['current_position'] : null;
                $evo = (int)($row['evolution'] ?? 0);
                $badge = $currentPos !== null && $currentPos <= 3 ? 'Top 3' : ($currentPos !== null && $currentPos <= 10 ? 'Top 10' : 'Hors classement');
                ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars((string)$row['keyword']) ?></strong><br>
                        <small><?= htmlspecialchars((string)$row['target_url']) ?></small>
                    </td>
                    <td>
                        <?= $currentPos ?? 'N/A' ?>
                        <span><?= $evo > 0 ? '↑' : ($evo < 0 ? '↓' : '=') ?></span>
                    </td>
                    <td><span class="pill"><?= htmlspecialchars($badge) ?></span></td>
                    <td><?= (int)$row['estimated_volume'] ?></td>
                    <td><div class="difficulty"><i style="width:<?= (int)$row['difficulty'] ?>%"></i></div></td>
                    <td><?= $row['last_checked_at'] ? htmlspecialchars((string)$row['last_checked_at']) : 'Jamais' ?></td>
                    <td class="row-actions">
                        <button type="button" data-id="<?= (int)$row['id'] ?>" onclick="checkKeywordPosition(<?= (int)$row['id'] ?>)">Refresh</button>
                        <a href="javascript:void(0)" onclick="deleteKeyword(<?= (int)$row['id'] ?>)">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="chart-card">
        <canvas id="keywordEvolutionChart" height="110"></canvas>
    </div>
</section>

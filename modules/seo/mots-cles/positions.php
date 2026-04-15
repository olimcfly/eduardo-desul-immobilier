<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/SeoKeywordPilotService.php';

$userId = (int)(Auth::user()['id'] ?? 0);
$keywordService = new SeoKeywordPilotService(db(), $userId);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$keyword = $keywordService->findKeyword($id);

if (!$keyword) {
    flash('error', 'Mot-clé introuvable.');
    redirect('/admin?module=seo&action=keywords');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    if (isset($_POST['save_position'])) {
        $position = $_POST['position_value'] === '' ? null : (int)$_POST['position_value'];
        $keywordService->recordPosition($id, $position, (string)($_POST['source'] ?? 'manual'), (string)($_POST['notes'] ?? ''));
        flash('success', 'Position enregistrée.');
        redirect('/admin?module=seo&action=keyword_positions&id=' . $id);
    }

    if (isset($_POST['mock_position'])) {
        $mock = random_int(4, 30);
        $keywordService->recordPosition($id, $mock, 'mock', 'Simulation initiale');
        flash('success', 'Position mock ajoutée (#' . $mock . ').');
        redirect('/admin?module=seo&action=keyword_positions&id=' . $id);
    }
}

$history = $keywordService->getPositionHistory($id, 50);
$flash = getFlash();
?>
<section class="seo-section seo-keyword-positions">
    <div class="seo-breadcrumb"><a href="/admin?module=seo">Accueil</a> &gt; SEO &gt; <a href="/admin?module=seo&action=keywords">Mots-clés</a> &gt; Historique</div>

    <h2>Historique des positions</h2>
    <p><strong><?= e((string)$keyword['keyword']) ?></strong> · <?= e((string)($keyword['city_name'] ?: 'zone non précisée')) ?></p>

    <?php if ($flash): ?>
        <div class="seo-flash seo-flash-<?= e((string)$flash['type']) ?>"><?= e((string)$flash['message']) ?></div>
    <?php endif; ?>

    <div class="kpi-grid">
        <div class="kpi"><span>Position actuelle</span><strong><?= $keyword['current_position'] ? '#' . (int)$keyword['current_position'] : '—' ?></strong></div>
        <div class="kpi"><span>Position précédente</span><strong><?= $keyword['previous_position'] ? '#' . (int)$keyword['previous_position'] : '—' ?></strong></div>
        <div class="kpi"><span>Delta</span><strong><?= ($keyword['current_position'] && $keyword['previous_position']) ? ((int)$keyword['previous_position'] - (int)$keyword['current_position']) : '—' ?></strong></div>
        <div class="kpi"><span>Dernier check</span><strong><?= e((string)($keyword['last_checked_at'] ?: '—')) ?></strong></div>
    </div>

    <div class="grid-two">
        <form method="post" class="chart-card seo-form-stack">
            <?= csrfField() ?>
            <h3>Saisie manuelle</h3>
            <label>Position Google</label>
            <input type="number" name="position_value" min="1" max="100" placeholder="ex: 12">

            <label>Source</label>
            <select name="source">
                <option value="manual">manual</option>
                <option value="mock">mock</option>
                <option value="api_google">api_google</option>
                <option value="api_provider">api_provider</option>
            </select>

            <label>Notes</label>
            <input type="text" name="notes" maxlength="255" placeholder="ex: check manuel du matin">

            <div class="actions">
                <button type="submit" name="save_position">Enregistrer</button>
                <button type="submit" name="mock_position">Ajouter une position mock</button>
            </div>
        </form>

        <div class="chart-card">
            <h3>Architecture tracking</h3>
            <p>Le module est prêt pour un provider externe : toute position est historisée dans <code>seo_keyword_positions</code> avec source et notes.</p>
            <p>API interne disponible : <code>/modules/seo/mots-cles/api.php</code>.</p>
            <a class="seo-secondary" href="/admin?module=seo&action=keyword_edit&id=<?= (int)$keyword['id'] ?>">Modifier le mot-clé</a>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
            <tr><th>Date</th><th>Position</th><th>Source</th><th>Notes</th></tr>
            </thead>
            <tbody>
            <?php if (!$history): ?>
                <tr><td colspan="4" class="seo-muted">Aucun historique pour le moment.</td></tr>
            <?php else: ?>
                <?php foreach ($history as $row): ?>
                    <tr>
                        <td><?= e((string)$row['checked_at']) ?></td>
                        <td><?= $row['position_value'] ? '#' . (int)$row['position_value'] : '—' ?></td>
                        <td><span class="pill"><?= e((string)$row['source']) ?></span></td>
                        <td><?= e((string)($row['notes'] ?: '—')) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/SeoKeywordPilotService.php';

$userId = (int)(Auth::user()['id'] ?? 0);
$keywordService = new SeoKeywordPilotService(db(), $userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    if (isset($_POST['delete_keyword'])) {
        $keywordService->deleteKeyword((int)$_POST['keyword_id']);
        flash('success', 'Mot-clé supprimé.');
        redirect('/admin?module=seo&action=keywords');
    }
}

$filters = [
    'city' => (string)($_GET['city'] ?? ''),
    'intent' => (string)($_GET['intent'] ?? ''),
    'status' => (string)($_GET['status'] ?? ''),
    'top10' => (string)($_GET['top10'] ?? ''),
    'trend' => (string)($_GET['trend'] ?? ''),
];

$data = $keywordService->getDashboard($filters);
$stats = $data['stats'];
$keywords = $data['keywords'];
$opportunities = $data['opportunities'];
$filterValues = $data['filters'];
$flash = getFlash();

$fmtDate = static function (?string $date): string {
    if ($date === null || $date === '') {
        return '—';
    }

    try {
        return (new DateTimeImmutable($date))->format('d/m/Y H:i');
    } catch (Throwable) {
        return '—';
    }
};
?>
<section class="seo-section seo-keywords-pilot">
    <div class="seo-breadcrumb"><a href="/admin?module=seo">Accueil</a> &gt; SEO &gt; Mots-clés</div>
    <div class="seo-headline">
        <h2>Mots-clés locaux</h2>
        <p>Réponse immédiate : êtes-vous visible quand un propriétaire cherche dans votre zone ?</p>
        <a class="seo-primary" href="/admin?module=seo&action=keyword_edit">+ Ajouter un mot-clé</a>
    </div>

    <?php if ($flash): ?>
        <div class="seo-flash seo-flash-<?= e((string)$flash['type']) ?>"><?= e((string)$flash['message']) ?></div>
    <?php endif; ?>

    <div class="kpi-grid seo-kpi-grid-5">
        <div class="kpi"><span>Total suivis</span><strong><?= (int)$stats['total'] ?></strong></div>
        <div class="kpi"><span>Top 3</span><strong><?= (int)$stats['top3'] ?></strong></div>
        <div class="kpi"><span>Top 10</span><strong><?= (int)$stats['top10'] ?></strong></div>
        <div class="kpi"><span>Progression moyenne</span><strong><?= number_format((float)$stats['avg_progress'], 1, ',', ' ') ?></strong></div>
        <div class="kpi"><span>Opportunités</span><strong><?= (int)$stats['opportunities'] ?></strong></div>
    </div>

    <form method="get" class="inline-form seo-filter-form">
        <input type="hidden" name="module" value="seo">
        <input type="hidden" name="action" value="keywords">

        <select name="city">
            <option value="">Ville</option>
            <?php foreach ($filterValues['cities'] as $city): ?>
                <option value="<?= e((string)$city) ?>" <?= $filters['city'] === $city ? 'selected' : '' ?>><?= e((string)$city) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="intent">
            <option value="">Intention</option>
            <?php foreach ($filterValues['intents'] as $intent): ?>
                <option value="<?= e($intent) ?>" <?= $filters['intent'] === $intent ? 'selected' : '' ?>><?= e(ucfirst($intent)) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="status">
            <option value="">Statut</option>
            <?php foreach ($filterValues['statuses'] as $status): ?>
                <option value="<?= e($status) ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="top10">
            <option value="">Top 10</option>
            <option value="1" <?= $filters['top10'] === '1' ? 'selected' : '' ?>>Seulement top 10</option>
        </select>

        <select name="trend">
            <option value="">Tendance</option>
            <option value="progression" <?= $filters['trend'] === 'progression' ? 'selected' : '' ?>>Progression</option>
            <option value="regression" <?= $filters['trend'] === 'regression' ? 'selected' : '' ?>>Régression</option>
        </select>

        <button type="submit">Filtrer</button>
    </form>

    <?php if (!$keywords): ?>
        <div class="seo-empty-state">
            <h3>Votre pilotage SEO local démarre ici</h3>
            <p>Ajoutez vos requêtes propriétaires clés (ex: estimation maison Aix) pour mesurer votre visibilité quartier par quartier.</p>
            <a class="seo-primary" href="/admin?module=seo&action=keyword_edit">Créer mon premier mot-clé</a>
        </div>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Mot-clé</th>
                    <th>Ville</th>
                    <th>Intention</th>
                    <th>URL cible</th>
                    <th>Position actuelle</th>
                    <th>Position précédente</th>
                    <th>Delta</th>
                    <th>Dernier check</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($keywords as $keyword): ?>
                <?php $delta = $keyword['delta'] !== null ? (int)$keyword['delta'] : null; ?>
                <tr>
                    <td><?= e((string)$keyword['keyword']) ?></td>
                    <td><?= e((string)($keyword['city_name'] ?: '—')) ?></td>
                    <td><span class="pill"><?= e((string)$keyword['intent']) ?></span></td>
                    <td>
                        <?php if (!empty($keyword['target_url'])): ?>
                            <a href="<?= e((string)$keyword['target_url']) ?>" target="_blank" rel="noopener"><?= e((string)$keyword['target_url']) ?></a>
                        <?php else: ?>
                            <span class="seo-muted">Non liée</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $keyword['current_position'] ? '#' . (int)$keyword['current_position'] : '—' ?></td>
                    <td><?= $keyword['previous_position'] ? '#' . (int)$keyword['previous_position'] : '—' ?></td>
                    <td>
                        <?php if ($delta === null): ?>
                            —
                        <?php elseif ($delta > 0): ?>
                            <span class="seo-good">+<?= $delta ?></span>
                        <?php elseif ($delta < 0): ?>
                            <span class="seo-bad"><?= $delta ?></span>
                        <?php else: ?>
                            0
                        <?php endif; ?>
                    </td>
                    <td><?= e($fmtDate($keyword['last_checked_at'] ?? null)) ?></td>
                    <td><span class="pill pill-status-<?= e((string)$keyword['status']) ?>"><?= e((string)$keyword['status']) ?></span></td>
                    <td>
                        <div class="seo-actions-inline">
                            <a href="/admin?module=seo&action=keyword_edit&id=<?= (int)$keyword['id'] ?>">Éditer</a>
                            <a href="/admin?module=seo&action=keyword_positions&id=<?= (int)$keyword['id'] ?>">Positions</a>
                            <form method="post" onsubmit="return confirm('Supprimer ce mot-clé ?');">
                                <?= csrfField() ?>
                                <input type="hidden" name="keyword_id" value="<?= (int)$keyword['id'] ?>">
                                <button type="submit" name="delete_keyword">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <section class="seo-opportunities">
        <h3>Opportunités</h3>
        <div class="grid-two">
            <div class="chart-card">
                <h4>Positions 4 à 20</h4>
                <ul>
                    <?php foreach ($opportunities['position_4_20'] as $item): ?>
                        <li><?= e((string)$item['keyword']) ?> (<?= e((string)($item['city_name'] ?: 'zone non précisée')) ?>) · #<?= (int)$item['current_position'] ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="chart-card">
                <h4>Sans URL cible</h4>
                <ul>
                    <?php foreach ($opportunities['missing_url'] as $item): ?>
                        <li><?= e((string)$item['keyword']) ?> · <a href="/admin?module=seo&action=keyword_edit&id=<?= (int)$item['id'] ?>">lier une page</a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="chart-card">
                <h4>En progression</h4>
                <ul>
                    <?php foreach ($opportunities['progressing'] as $item): ?>
                        <li><?= e((string)$item['keyword']) ?> · +<?= (int)$item['previous_position'] - (int)$item['current_position'] ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="chart-card">
                <h4>Stratégiques sans fiche ville</h4>
                <ul>
                    <?php foreach ($opportunities['city_gap'] as $item): ?>
                        <li><?= e((string)$item['keyword']) ?> · <?= e((string)$item['city_name']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </section>
</section>

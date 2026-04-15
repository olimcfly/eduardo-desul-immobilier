<?php

declare(strict_types=1);

$userId = (int)(Auth::user()['id'] ?? 0);
$statusFilter = trim((string)($_GET['status'] ?? ''));
$search = trim((string)($_GET['q'] ?? ''));

$stats = $cityPageService->getStats($userId);
$rows = $cityPageService->listForUser($userId, $statusFilter, $search);

$badgeClass = static function (string $status): string {
    return match ($status) {
        'published' => 'fv-badge fv-badge-published',
        'ready' => 'fv-badge fv-badge-ready',
        default => 'fv-badge fv-badge-draft',
    };
};
?>
<section class="seo-section fiche-ville-module">
    <div class="seo-breadcrumb"><a href="/admin?module=seo">Accueil</a> &gt; SEO &gt; Fiches villes</div>
    <div class="fv-head">
        <div>
            <h2>📍 Fiches villes</h2>
            <p class="seo-subtitle">Créez des pages locales simples à gérer pour chaque commune.</p>
        </div>
        <a class="btn btn-sm" href="/admin?module=seo&action=ville-edit">+ Nouvelle fiche</a>
    </div>

    <div class="kpi-grid">
        <div class="kpi"><span>Total fiches</span><strong><?= (int)$stats['total'] ?></strong></div>
        <div class="kpi"><span>Publiées</span><strong><?= (int)$stats['published'] ?></strong></div>
        <div class="kpi"><span>Brouillons</span><strong><?= (int)$stats['draft'] ?></strong></div>
        <div class="kpi"><span>Score SEO moyen</span><strong><?= (int)$stats['avg_seo_score'] ?>/100</strong></div>
    </div>

    <form class="inline-form" method="get" action="/admin">
        <input type="hidden" name="module" value="seo">
        <input type="hidden" name="action" value="villes">
        <select name="status">
            <option value="">Tous les statuts</option>
            <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Brouillon</option>
            <option value="ready" <?= $statusFilter === 'ready' ? 'selected' : '' ?>>Prêt</option>
            <option value="published" <?= $statusFilter === 'published' ? 'selected' : '' ?>>Publié</option>
        </select>
        <input type="text" name="q" placeholder="Rechercher une commune" value="<?= e($search) ?>">
        <button type="submit">Filtrer</button>
        <a class="btn btn-sm" href="/admin?module=seo&action=villes">Réinitialiser</a>
    </form>

    <?php if ($rows === []): ?>
        <div class="fv-empty-state">
            <h3>Créez votre première fiche ville</h3>
            <p>Commencez par une commune prioritaire, puis complétez les sections pour atteindre un bon score SEO.</p>
            <a class="btn btn-sm" href="/admin?module=seo&action=ville-edit">Créer ma première fiche</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Commune</th>
                    <th>Slug</th>
                    <th>Statut</th>
                    <th>Score SEO</th>
                    <th>Score contenu</th>
                    <th>Dernière mise à jour</th>
                    <th>Publié le</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><strong><?= e((string)$row['city_name']) ?></strong></td>
                        <td><code>/<?= e((string)$row['slug']) ?>/</code></td>
                        <td><span class="<?= $badgeClass((string)$row['status']) ?>"><?= e((string)$row['status']) ?></span></td>
                        <td><strong><?= (int)$row['seo_score'] ?>/100</strong></td>
                        <td><?= (int)$row['content_score'] ?>/100</td>
                        <td><?= e((string)$row['updated_at']) ?></td>
                        <td><?= $row['published_at'] ? e((string)$row['published_at']) : '—' ?></td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-sm" href="/admin?module=seo&action=ville-edit&id=<?= (int)$row['id'] ?>">Gérer</a>
                                <a class="btn btn-sm" href="/admin?module=seo&action=ville-preview&id=<?= (int)$row['id'] ?>">Prévisualiser</a>
                                <form method="post" action="/modules/seo/fiches-villes/api.php" class="fv-inline-form">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="mode" value="toggle-publication">
                                    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                    <button type="submit" class="btn btn-sm"><?= $row['status'] === 'published' ? 'Dépublier' : 'Publier' ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

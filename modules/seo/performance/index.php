<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/SeoTechnicalPerformanceService.php';

$userId = (int)(Auth::user()['id'] ?? 0);
$service = new SeoTechnicalPerformanceService(db(), $userId);
$data = $service->getDashboardData();
$flash = getFlash();

$statusLabels = [
    'bon' => 'Bon',
    'moyen' => 'Moyen',
    'a_corriger' => 'À corriger',
];
?>
<section class="seo-section seo-performance-dashboard">
    <div class="seo-breadcrumb"><a href="/admin?module=seo">Accueil</a> &gt; SEO &gt; Performance technique</div>

    <div class="seo-headline">
        <div>
            <h2>Performance technique</h2>
            <p>Un tableau de bord clair pour savoir si votre site est sain, quoi corriger et dans quel ordre.</p>
        </div>
        <form method="post" action="/admin?module=seo&action=performance_run">
            <?= csrfField() ?>
            <button type="submit">Lancer un audit global</button>
        </form>
    </div>

    <?php if ($flash): ?>
        <div class="seo-flash seo-flash-<?= e((string)$flash['type']) ?>"><?= e((string)$flash['message']) ?></div>
    <?php endif; ?>

    <div class="kpi-grid seo-kpi-grid-5">
        <div class="kpi"><span>Score global</span><strong><?= (int)$data['global_score'] ?>/100</strong></div>
        <div class="kpi"><span>État</span><strong><?= e($statusLabels[$data['status']] ?? 'À corriger') ?></strong></div>
        <div class="kpi"><span>Critiques</span><strong><?= (int)$data['issue_counts']['critical'] ?></strong></div>
        <div class="kpi"><span>Importants</span><strong><?= (int)$data['issue_counts']['important'] ?></strong></div>
        <div class="kpi"><span>Mineurs</span><strong><?= (int)$data['issue_counts']['minor'] ?></strong></div>
    </div>

    <div class="grid-two">
        <div class="chart-card">
            <h3>Pages prévues dans l’audit</h3>
            <ul>
                <?php foreach ($data['target_pages'] as $target): ?>
                    <li><?= e((string)$target['url']) ?> · <span class="pill"><?= e((string)$target['type']) ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="chart-card">
            <h3>Décision rapide</h3>
            <ul>
                <li><strong>Mon site est-il sain ?</strong> <?= (int)$data['global_score'] >= 80 ? 'Oui, globalement.' : 'Des correctifs sont recommandés.' ?></li>
                <li><strong>Quelles pages ont un problème ?</strong> Voir la liste des pages auditées.</li>
                <li><strong>Quoi corriger en priorité ?</strong> Commencer par les problèmes critiques puis importants.</li>
            </ul>
            <a class="seo-secondary" href="/admin?module=seo&action=performance_history">Voir toutes les pages auditées</a>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Page</th><th>Type</th><th>Score</th><th>Date audit</th><th>Problèmes</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach (array_slice($data['latest_audits'], 0, 12) as $audit): ?>
                <tr>
                    <td><?= e((string)$audit['page_url']) ?></td>
                    <td><span class="pill"><?= e((string)$audit['page_type']) ?></span></td>
                    <td><?= (int)$audit['global_score'] ?>/100</td>
                    <td><?= e((string)$audit['audited_at']) ?></td>
                    <td><?= (int)$audit['broken_links_count'] + (int)$audit['image_issues_count'] ?></td>
                    <td><a href="/admin?module=seo&action=performance_history&audit_id=<?= (int)$audit['id'] ?>">Voir détail</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

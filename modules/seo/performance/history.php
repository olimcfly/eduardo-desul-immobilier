<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/SeoTechnicalPerformanceService.php';

$userId = (int)(Auth::user()['id'] ?? 0);
$service = new SeoTechnicalPerformanceService(db(), $userId);

$auditId = isset($_GET['audit_id']) ? (int)$_GET['audit_id'] : 0;
$list = $service->getAuditsList();
$detail = $auditId > 0 ? $service->getAuditDetail($auditId) : null;
?>
<section class="seo-section seo-performance-history">
    <div class="seo-breadcrumb"><a href="/admin?module=seo">Accueil</a> &gt; SEO &gt; <a href="/admin?module=seo&action=performance">Performance technique</a> &gt; Historique</div>

    <h2>Pages auditées</h2>

    <div class="table-wrap">
        <table>
            <thead>
            <tr><th>Page</th><th>Type</th><th>Score</th><th>Date audit</th><th>Nb problèmes</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php foreach ($list as $row): ?>
                <tr>
                    <td><?= e((string)$row['page_url']) ?></td>
                    <td><span class="pill"><?= e((string)$row['page_type']) ?></span></td>
                    <td><?= (int)$row['global_score'] ?>/100</td>
                    <td><?= e((string)$row['audited_at']) ?></td>
                    <td><?= (int)$row['total_issues'] ?></td>
                    <td><a href="/admin?module=seo&action=performance_history&audit_id=<?= (int)$row['id'] ?>">Voir détail</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($detail): ?>
        <div class="chart-card">
            <h3>Détail audit — <?= e((string)$detail['audit']['page_url']) ?></h3>
            <p>
                Type : <span class="pill"><?= e((string)$detail['audit']['page_type']) ?></span> ·
                Score : <strong><?= (int)$detail['audit']['global_score'] ?>/100</strong> ·
                Chargement estimé : <strong><?= (int)$detail['audit']['load_time_ms'] ?> ms</strong> ·
                Poids : <strong><?= (int)$detail['audit']['page_weight_kb'] ?> KB</strong>
            </p>
            <p>
                Meta SEO OK : <strong><?= (int)$detail['audit']['seo_meta_ok'] === 1 ? 'Oui' : 'Non' ?></strong> ·
                Liens cassés : <strong><?= (int)$detail['audit']['broken_links_count'] ?></strong> ·
                Images sans alt : <strong><?= (int)$detail['audit']['image_issues_count'] ?></strong>
            </p>

            <h4>Problèmes détectés</h4>
            <table>
                <thead><tr><th>Priorité</th><th>Problème</th><th>Description</th><th>Action recommandée</th></tr></thead>
                <tbody>
                <?php if (!$detail['issues']): ?>
                    <tr><td colspan="4">Aucun problème bloquant détecté.</td></tr>
                <?php else: ?>
                    <?php foreach ($detail['issues'] as $issue): ?>
                        <tr>
                            <td><span class="pill pill-status-<?= e((string)$issue['severity']) ?>"><?= e((string)$issue['severity']) ?></span></td>
                            <td><?= e((string)$issue['issue_label']) ?></td>
                            <td><?= e((string)$issue['issue_description']) ?></td>
                            <td><?= e((string)$issue['recommended_action']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

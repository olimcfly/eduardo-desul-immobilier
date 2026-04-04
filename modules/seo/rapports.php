<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

$user_id = $_SESSION['user_id'];

// Récupération des données pour les rapports
// Keywords
$stmt = $pdo->prepare("SELECT * FROM seo_keywords WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$keywords = $stmt->fetchAll();

// Audits
$stmt = $pdo->prepare("SELECT * FROM seo_audits WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$audits = $stmt->fetchAll();

// Backlinks
$stmt = $pdo->prepare("SELECT * FROM seo_backlinks WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$backlinks = $stmt->fetchAll();

// Positions
$stmt = $pdo->prepare("SELECT * FROM seo_positions WHERE user_id = ? ORDER BY date_check DESC");
$stmt->execute([$user_id]);
$positions = $stmt->fetchAll();

// Calculs stats keywords
$total_keywords = count($keywords);
$kw_top3 = count(array_filter($keywords, fn($k) => $k['position'] <= 3));
$kw_top10 = count(array_filter($keywords, fn($k) => $k['position'] <= 10));
$kw_top50 = count(array_filter($keywords, fn($k) => $k['position'] <= 50));
$avg_position = $total_keywords > 0 ? round(array_sum(array_column($keywords, 'position')) / $total_keywords, 1) : 0;

// Calculs stats audits
$total_audits = count($audits);
$avg_seo = $total_audits > 0 ? round(array_sum(array_column($audits, 'score_seo')) / $total_audits) : 0;
$avg_perf = $total_audits > 0 ? round(array_sum(array_column($audits, 'score_performance')) / $total_audits) : 0;
$avg_access = $total_audits > 0 ? round(array_sum(array_column($audits, 'score_accessibility')) / $total_audits) : 0;

// Calculs stats backlinks
$total_backlinks = count($backlinks);
$bl_actifs = count(array_filter($backlinks, fn($b) => $b['status'] === 'actif'));
$bl_dofollow = count(array_filter($backlinks, fn($b) => $b['link_type'] === 'dofollow'));
$avg_da = $total_backlinks > 0 ? round(array_sum(array_column($backlinks, 'domain_authority')) / $total_backlinks) : 0;

require_once '../../includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-chart-bar me-2"></i>Rapports SEO</h2>
        <button class="btn btn-success" onclick="window.print()">
            <i class="fas fa-print me-1"></i>Imprimer le rapport
        </button>
    </div>

    <!-- Résumé général -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Résumé Général</h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="p-3 border rounded">
                        <h2 class="text-primary"><?= $total_keywords ?></h2>
                        <p class="text-muted mb-0">Mots-clés suivis</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded">
                        <h2 class="text-success"><?= $total_audits ?></h2>
                        <p class="text-muted mb-0">Audits réalisés</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded">
                        <h2 class="text-info"><?= $total_backlinks ?></h2>
                        <p class="text-muted mb-0">Backlinks</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded">
                        <h2 class="text-warning"><?= $avg_position ?></h2>
                        <p class="text-muted mb-0">Position moyenne</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rapport Keywords -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-key me-2"></i>Rapport Mots-clés</h5>
        </div>
        <div class="card-body">
            <div class="row text-center mb-3">
                <div class="col-md-3">
                    <div class="p-2 bg-success bg-opacity-10 rounded">
                        <h4 class="text-success"><?= $kw_top3 ?></h4>
                        <small>Top 3</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 bg-primary bg-opacity-10 rounded">
                        <h4 class="text-primary"><?= $kw_top10 ?></h4>
                        <small>Top 10</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 bg-warning bg-opacity-10 rounded">
                        <h4 class="text-warning"><?= $kw_top50 ?></h4>
                        <small>Top 50</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 bg-secondary bg-opacity-10 rounded">
                        <h4 class="text-secondary"><?= $avg_position ?></h4>
                        <small>Position moy.</small>
                    </div>
                </div>
            </div>

            <?php if (!empty($keywords)): ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Mot-clé</th>
                            <th>Position</th>
                            <th>Volume</th>
                            <th>Difficulté</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($keywords, 0, 10) as $kw): ?>
                        <tr>
                            <td><?= htmlspecialchars($kw['keyword']) ?></td>
                            <td>
                                <span class="badge <?= $kw['position'] <= 3 ? 'bg-success' : ($kw['position'] <= 10 ? 'bg-primary' : ($kw['position'] <= 50 ? 'bg-warning' : 'bg-danger')) ?>">
                                    #<?= $kw['position'] ?>
                                </span>
                            </td>
                            <td><?= number_format($kw['search_volume']) ?></td>
                            <td>
                                <div class="progress" style="height:8px;width:80px">
                                    <div class="progress-bar <?= $kw['difficulty'] <= 30 ? 'bg-success' : ($kw['difficulty'] <= 60 ? 'bg-warning' : 'bg-danger') ?>"
                                         style="width:<?= $kw['difficulty'] ?>%"></div>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= $kw['status'] === 'actif' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= ucfirst($kw['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p class="text-muted text-center">Aucun mot-clé enregistré.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Rapport Audits -->
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-search-plus me-2"></i>Rapport Audits</h5>
        </div>
        <div class="card-body">
            <div class="row text-center mb-3">
                <div class="col-md-4">
                    <div class="p-2 bg-primary bg-opacity-10 rounded">
                        <h4 class="text-primary"><?= $avg_seo ?>/100</h4>
                        <small>Score SEO moyen</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-2 bg-success bg-opacity-10 rounded">
                        <h4 class="text-success"><?= $avg_perf ?>/100</h4>
                        <small>Performance moyenne</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-2 bg-info bg-opacity-10 rounded">
                        <h4 class="text-info"><?= $avg_access ?>/100</h4>
                        <small>Accessibilité moyenne</small>
                    </div>
                </div>
            </div>

            <?php if (!empty($audits)): ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>URL</th>
                            <th>Type</th>
                            <th>SEO</th>
                            <th>Performance</th>
                            <th>Accessibilité</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($audits, 0, 5) as $audit): ?>
                        <tr>
                            <td class="text-truncate" style="max-width:200px">
                                <?= htmlspecialchars($audit['url']) ?>
                            </td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($audit['audit_type']) ?></span></td>
                            <td>
                                <span class="badge <?= $audit['score_seo'] >= 80 ? 'bg-success' : ($audit['score_seo'] >= 60 ? 'bg-warning' : 'bg-danger') ?>">
                                    <?= $audit['score_seo'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $audit['score_performance'] >= 80 ? 'bg-success' : ($audit['score_performance'] >= 60 ? 'bg-warning' : 'bg-danger') ?>">
                                    <?= $audit['score_performance'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $audit['score_accessibility'] >= 80 ? 'bg-success' : ($audit['score_accessibility'] >= 60 ? 'bg-warning' : 'bg-danger') ?>">
                                    <?= $audit['score_accessibility'] ?>
                                </span>
                            </td>
                            <td><small><?= date('d/m/Y', strtotime($audit['created_at'])) ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p class="text-muted text-center">Aucun audit réalisé.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Rapport Backlinks -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-link me-2"></i>Rapport Backlinks</h5>
        </div>
        <div class="card-body">
            <div class="row text-center mb-3">
                <div class="col-md-3">
                    <div class="p-2 bg-info bg-opacity-10 rounded">
                        <h4 class="text-info"><?= $total_backlinks ?></h4>
                        <small>Total</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 bg-success bg-opacity-10 rounded">
                        <h4 class="text-success"><?= $bl_actifs ?></h4>
                        <small>Actifs</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 bg-primary bg-opacity-10 rounded">
                        <h4 class="text-primary"><?= $bl_dofollow ?></h4>
                        <small>Dofollow</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 bg-warning bg-opacity-10 rounded">
                        <h4 class="text-warning"><?= $avg_da ?></h4>
                        <small>DA moyen</small>
                    </div>
                </div>
            </div>

            <?php if (!empty($backlinks)): ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Source</th>
                            <th>Ancre</th>
                            <th>DA</th>
                            <th>Type</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($backlinks, 0, 10) as $bl): ?>
                        <tr>
                            <td class="text-truncate" style="max-width:200px">
                                <a href="<?= htmlspecialchars($bl['source_url']) ?>" target="_blank">
                                    <?= htmlspecialchars($bl['source_url']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($bl['anchor_text']) ?></td>
                            <td>
                                <span class="badge <?= $bl['domain_authority'] >= 50 ? 'bg-success' : ($bl['domain_authority'] >= 30 ? 'bg-warning' : 'bg-danger') ?>">
                                    <?= $bl['domain_authority'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $bl['link_type'] === 'dofollow' ? 'bg-primary' : 'bg-secondary' ?>">
                                    <?= $bl['link_type'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $bl['status'] === 'actif' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= ucfirst($bl['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p class="text-muted text-center">Aucun backlink enregistré.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

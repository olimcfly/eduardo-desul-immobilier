<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

$user_id = $_SESSION['user_id'];

// Lancer un nouvel audit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_audit'])) {
    $url = trim($_POST['url']);
    $audit_type = trim($_POST['audit_type']);
    
    if (!empty($url)) {
        // Simulation des scores d'audit
        $score_seo = rand(60, 100);
        $score_performance = rand(50, 100);
        $score_accessibility = rand(60, 100);
        $issues = json_encode([
            'missing_meta' => rand(0, 5),
            'broken_links' => rand(0, 3),
            'missing_alt' => rand(0, 10),
            'slow_pages' => rand(0, 4),
        ]);
        
        $stmt = $pdo->prepare("INSERT INTO seo_audits (user_id, url, audit_type, score_seo, score_performance, score_accessibility, issues, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'completed')");
        $stmt->execute([$user_id, $url, $audit_type, $score_seo, $score_performance, $score_accessibility, $issues]);
        header('Location: audits.php');
        exit;
    }
}

// Suppression
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM seo_audits WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    header('Location: audits.php');
    exit;
}

// Récupération des audits
$stmt = $pdo->prepare("SELECT * FROM seo_audits WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$audits = $stmt->fetchAll();

require_once '../../includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-search-plus me-2"></i>Audits SEO</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAuditModal">
            <i class="fas fa-plus me-1"></i>Nouvel Audit
        </button>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h3 class="text-primary"><?= count($audits) ?></h3>
                    <small>Total Audits</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h3 class="text-success">
                        <?= count(array_filter($audits, fn($a) => $a['score_seo'] >= 80)) ?>
                    </h3>
                    <small>Score SEO ≥ 80</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h3 class="text-warning">
                        <?= count(array_filter($audits, fn($a) => $a['score_seo'] >= 50 && $a['score_seo'] < 80)) ?>
                    </h3>
                    <small>Score SEO Moyen</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <h3 class="text-danger">
                        <?= count(array_filter($audits, fn($a) => $a['score_seo'] < 50)) ?>
                    </h3>
                    <small>Score SEO Faible</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des audits -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($audits)): ?>
                <p class="text-center text-muted py-4">Aucun audit effectué. Lancez votre premier audit !</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>URL</th>
                                <th>Type</th>
                                <th>Score SEO</th>
                                <th>Performance</th>
                                <th>Accessibilité</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($audits as $audit): ?>
                                <?php $issues = json_decode($audit['issues'], true); ?>
                                <tr>
                                    <td>
                                        <a href="<?= htmlspecialchars($audit['url']) ?>" target="_blank" class="text-truncate d-inline-block" style="max-width:200px">
                                            <?= htmlspecialchars($audit['url']) ?>
                                        </a>
                                    </td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($audit['audit_type']) ?></span></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:8px">
                                                <div class="progress-bar <?= $audit['score_seo'] >= 80 ? 'bg-success' : ($audit['score_seo'] >= 50 ? 'bg-warning' : 'bg-danger') ?>" 
                                                     style="width:<?= $audit['score_seo'] ?>%"></div>
                                            </div>
                                            <small><?= $audit['score_seo'] ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:8px">
                                                <div class="progress-bar bg-info" style="width:<?= $audit['score_performance'] ?>%"></div>
                                            </div>
                                            <small><?= $audit['score_performance'] ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:8px">
                                                <div class="progress-bar bg-purple" style="width:<?= $audit['score_accessibility'] ?>%"></div>
                                            </div>
                                            <small><?= $audit['score_accessibility'] ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $audit['status'] === 'completed' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($audit['status']) ?>
                                        </span>
                                    </td>
                                    <td><small><?= date('d/m/Y', strtotime($audit['created_at'])) ?></small></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" 
                                                data-bs-target="#detailModal<?= $audit['id'] ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="?delete=<?= $audit['id'] ?>" class="btn btn-sm btn-danger"
                                           onclick="return confirm('Supprimer cet audit ?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>

                                <!-- Modal Détail -->
                                <div class="modal fade" id="detailModal<?= $audit['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Détail de l'audit</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>URL :</strong> <?= htmlspecialchars($audit['url']) ?></p>
                                                <p><strong>Type :</strong> <?= htmlspecialchars($audit['audit_type']) ?></p>
                                                <hr>
                                                <h6>Problèmes détectés :</h6>
                                                <ul class="list-group">
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        Meta manquantes
                                                        <span class="badge bg-danger"><?= $issues['missing_meta'] ?? 0 ?></span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        Liens cassés
                                                        <span class="badge bg-danger"><?= $issues['broken_links'] ?? 0 ?></span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        Images sans alt
                                                        <span class="badge bg-warning"><?= $issues['missing_alt'] ?? 0 ?></span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        Pages lentes
                                                        <span class="badge bg-warning"><?= $issues['slow_pages'] ?? 0 ?></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Nouvel Audit -->
<div class="modal fade" id="addAuditModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouvel Audit SEO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">URL à auditer</label>
                        <input type="url" name="url" class="form-control" placeholder="https://exemple.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type d'audit</label>
                        <select name="audit_type" class="form-select">
                            <option value="complet">Audit Complet</option>
                            <option value="seo">SEO uniquement</option>
                            <option value="performance">Performance</option>
                            <option value="accessibilite">Accessibilité</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" name="run_audit" class="btn btn-primary">
                        <i class="fas fa-play me-1"></i>Lancer l'audit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

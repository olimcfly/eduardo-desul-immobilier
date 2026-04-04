<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

$user_id = $_SESSION['user_id'];

// Ajouter un backlink
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_backlink'])) {
    $source_url = trim($_POST['source_url']);
    $target_url = trim($_POST['target_url']);
    $anchor_text = trim($_POST['anchor_text']);
    $domain_authority = (int)$_POST['domain_authority'];
    $link_type = trim($_POST['link_type']);

    if (!empty($source_url) && !empty($target_url)) {
        $stmt = $pdo->prepare("INSERT INTO seo_backlinks (user_id, source_url, target_url, anchor_text, domain_authority, link_type, status) VALUES (?, ?, ?, ?, ?, ?, 'actif')");
        $stmt->execute([$user_id, $source_url, $target_url, $anchor_text, $domain_authority, $link_type]);
        header('Location: backlinks.php');
        exit;
    }
}

// Suppression
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM seo_backlinks WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    header('Location: backlinks.php');
    exit;
}

// Récupération
$stmt = $pdo->prepare("SELECT * FROM seo_backlinks WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$backlinks = $stmt->fetchAll();

require_once '../../includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-link me-2"></i>Backlinks</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBacklinkModal">
            <i class="fas fa-plus me-1"></i>Ajouter un Backlink
        </button>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h3 class="text-primary"><?= count($backlinks) ?></h3>
                    <small>Total Backlinks</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h3 class="text-success">
                        <?= count(array_filter($backlinks, fn($b) => $b['status'] === 'actif')) ?>
                    </h3>
                    <small>Actifs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h3 class="text-warning">
                        <?= count(array_filter($backlinks, fn($b) => $b['domain_authority'] >= 50)) ?>
                    </h3>
                    <small>DA ≥ 50</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-info">
                <div class="card-body">
                    <h3 class="text-info">
                        <?= count(array_filter($backlinks, fn($b) => $b['link_type'] === 'dofollow')) ?>
                    </h3>
                    <small>Dofollow</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($backlinks)): ?>
                <p class="text-center text-muted py-4">Aucun backlink enregistré.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Source</th>
                                <th>Cible</th>
                                <th>Ancre</th>
                                <th>DA</th>
                                <th>Type</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($backlinks as $bl): ?>
                                <tr>
                                    <td>
                                        <a href="<?= htmlspecialchars($bl['source_url']) ?>" target="_blank" 
                                           class="text-truncate d-inline-block" style="max-width:180px">
                                            <?= htmlspecialchars($bl['source_url']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="<?= htmlspecialchars($bl['target_url']) ?>" target="_blank"
                                           class="text-truncate d-inline-block" style="max-width:180px">
                                            <?= htmlspecialchars($bl['target_url']) ?>
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
                                            <?= htmlspecialchars($bl['link_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $bl['status'] === 'actif' ? 'bg-success' : 'bg-danger' ?>">
                                            <?= ucfirst($bl['status']) ?>
                                        </span>
                                    </td>
                                    <td><small><?= date('d/m/Y', strtotime($bl['created_at'])) ?></small></td>
                                    <td>
                                        <a href="?delete=<?= $bl['id'] ?>" class="btn btn-sm btn-danger"
                                           onclick="return confirm('Supprimer ce backlink ?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Ajout -->
<div class="modal fade" id="addBacklinkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un Backlink</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">URL Source</label>
                        <input type="url" name="source_url" class="form-control" placeholder="https://site-source.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL Cible</label>
                        <input type="url" name="target_url" class="form-control" placeholder="https://votre-site.com/page" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Texte d'ancre</label>
                        <input type="text" name="anchor_text" class="form-control" placeholder="Ex: agence immobilière Paris">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Domain Authority (DA)</label>
                        <input type="number" name="domain_authority" class="form-control" min="0" max="100" value="30">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type de lien</label>
                        <select name="link_type" class="form-select">
                            <option value="dofollow">Dofollow</option>
                            <option value="nofollow">Nofollow</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" name="add_backlink" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

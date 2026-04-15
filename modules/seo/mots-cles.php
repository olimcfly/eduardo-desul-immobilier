<?php
/** @deprecated SEO legacy freeze: no new feature here. Use /admin?module=seo&action=keywords */
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

$user_id = $_SESSION['user_id'];

// Ajout d'un mot-clé
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_keyword'])) {
    $keyword = trim($_POST['keyword']);
    $target_url = trim($_POST['target_url']);
    $estimated_volume = (int)$_POST['estimated_volume'];
    $difficulty = (int)$_POST['difficulty'];

    if (!empty($keyword)) {
        $stmt = $pdo->prepare("INSERT INTO seo_keywords (user_id, keyword, target_url, estimated_volume, difficulty) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $keyword, $target_url, $estimated_volume, $difficulty]);
        header('Location: mots-cles.php');
        exit;
    }
}

// Suppression
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM seo_keywords WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    header('Location: mots-cles.php');
    exit;
}

// Récupération des mots-clés
$stmt = $pdo->prepare("SELECT * FROM seo_keywords WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$keywords = $stmt->fetchAll();

require_once '../../includes/header.php';
require_once __DIR__ . '/_legacy_guard.php';
seoLegacyGuard('modules/seo/mots-cles.php', '/admin?module=seo&action=keywords');
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-search"></i> Suivi des mots-clés SEO</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addKeywordModal">
            <i class="fas fa-plus"></i> Ajouter un mot-clé
        </button>
    </div>

    <!-- Tableau des mots-clés -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Mot-clé</th>
                        <th>URL cible</th>
                        <th>Position actuelle</th>
                        <th>Position précédente</th>
                        <th>Évolution</th>
                        <th>Volume</th>
                        <th>Difficulté</th>
                        <th>Dernière vérif.</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($keywords)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Aucun mot-clé suivi pour le moment.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($keywords as $kw): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($kw['keyword']) ?></strong></td>
                                <td>
                                    <?php if ($kw['target_url']): ?>
                                        <a href="<?= htmlspecialchars($kw['target_url']) ?>" target="_blank" class="text-truncate d-inline-block" style="max-width:150px;">
                                            <?= htmlspecialchars($kw['target_url']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($kw['current_position']): ?>
                                        <span class="badge bg-primary fs-6">#<?= $kw['current_position'] ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($kw['previous_position']): ?>
                                        <span class="badge bg-secondary">#<?= $kw['previous_position'] ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($kw['evolution'] !== null): ?>
                                        <?php if ($kw['evolution'] > 0): ?>
                                            <span class="text-success"><i class="fas fa-arrow-up"></i> +<?= $kw['evolution'] ?></span>
                                        <?php elseif ($kw['evolution'] < 0): ?>
                                            <span class="text-danger"><i class="fas fa-arrow-down"></i> <?= $kw['evolution'] ?></span>
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fas fa-minus"></i> 0</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= number_format($kw['estimated_volume']) ?></td>
                                <td>
                                    <?php
                                        $diff = $kw['difficulty'];
                                        $color = $diff <= 30 ? 'success' : ($diff <= 60 ? 'warning' : 'danger');
                                    ?>
                                    <span class="badge bg-<?= $color ?>"><?= $diff ?>/100</span>
                                </td>
                                <td>
                                    <?= $kw['last_checked_at'] ? date('d/m/Y H:i', strtotime($kw['last_checked_at'])) : '<span class="text-muted">—</span>' ?>
                                </td>
                                <td>
                                    <a href="mots-cles.php?delete=<?= $kw['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce mot-clé ?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ajout -->
<div class="modal fade" id="addKeywordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Ajouter un mot-clé</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Mot-clé <span class="text-danger">*</span></label>
                        <input type="text" name="keyword" class="form-control" placeholder="ex: agence immobilière Paris" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL cible</label>
                        <input type="url" name="target_url" class="form-control" placeholder="https://...">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Volume estimé / mois</label>
                            <input type="number" name="estimated_volume" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Difficulté (0-100)</label>
                            <input type="number" name="difficulty" class="form-control" value="0" min="0" max="100">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" name="add_keyword" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

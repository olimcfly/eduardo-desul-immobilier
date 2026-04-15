<?php
/** @deprecated SEO legacy freeze: no new feature here. Use /admin?module=seo&action=villes */
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

$user_id = $_SESSION['user_id'];

// Ajout d'une page ville
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_city'])) {
    $city_name = trim($_POST['city_name']);
    $slug = strtolower(str_replace(' ', '-', $city_name));
    $meta_title = trim($_POST['meta_title']);
    $meta_description = trim($_POST['meta_description']);
    $content = trim($_POST['content']);
    $zip_code = trim($_POST['zip_code']);
    $department = trim($_POST['department']);

    if (!empty($city_name)) {
        $stmt = $pdo->prepare("INSERT INTO seo_city_pages (user_id, city_name, slug, meta_title, meta_description, content, zip_code, department) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $city_name, $slug, $meta_title, $meta_description, $content, $zip_code, $department]);
        header('Location: villes.php');
        exit;
    }
}

// Suppression
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM seo_city_pages WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    header('Location: villes.php');
    exit;
}

// Toggle statut publié
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE seo_city_pages SET is_published = NOT is_published WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    header('Location: villes.php');
    exit;
}

// Récupération des pages
$stmt = $pdo->prepare("SELECT * FROM seo_city_pages WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$cities = $stmt->fetchAll();

require_once '../../includes/header.php';
require_once __DIR__ . '/_legacy_guard.php';
seoLegacyGuard('modules/seo/villes.php', '/admin?module=seo&action=villes');
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-map-marker-alt"></i> Pages SEO par ville</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCityModal">
            <i class="fas fa-plus"></i> Ajouter une ville
        </button>
    </div>

    <!-- Tableau -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Ville</th>
                        <th>Code postal</th>
                        <th>Département</th>
                        <th>Slug</th>
                        <th>Meta Title</th>
                        <th>Statut</th>
                        <th>Créée le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cities)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Aucune page ville créée.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cities as $city): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($city['city_name']) ?></strong></td>
                                <td><?= htmlspecialchars($city['zip_code']) ?></td>
                                <td><?= htmlspecialchars($city['department']) ?></td>
                                <td><code><?= htmlspecialchars($city['slug']) ?></code></td>
                                <td><?= htmlspecialchars($city['meta_title']) ?></td>
                                <td>
                                    <a href="?toggle=<?= $city['id'] ?>" class="badge <?= $city['is_published'] ? 'bg-success' : 'bg-secondary' ?> text-decoration-none">
                                        <?= $city['is_published'] ? 'Publié' : 'Brouillon' ?>
                                    </a>
                                </td>
                                <td><?= date('d/m/Y', strtotime($city['created_at'])) ?></td>
                                <td>
                                    <a href="?delete=<?= $city['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette page ?')">
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
<div class="modal fade" id="addCityModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-map-marker-alt"></i> Ajouter une page ville</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ville <span class="text-danger">*</span></label>
                            <input type="text" name="city_name" class="form-control" placeholder="ex: Paris" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Code postal</label>
                            <input type="text" name="zip_code" class="form-control" placeholder="75001">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Département</label>
                            <input type="text" name="department" class="form-control" placeholder="Paris (75)">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control" placeholder="ex: Agence immobilière à Paris - ...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="2" placeholder="Description SEO..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contenu de la page</label>
                        <textarea name="content" class="form-control" rows="5" placeholder="Contenu optimisé SEO pour cette ville..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" name="add_city" class="btn btn-primary">Créer la page</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

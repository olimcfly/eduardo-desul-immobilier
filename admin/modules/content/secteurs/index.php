<?php
if (!isset($pdo) && isset($db)) {
    $pdo = $db;
}
if (!isset($pdo) || !$pdo instanceof PDO) {
    require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/init.php';
}

require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/classes/SecteurSeoService.php';
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/classes/SecteurPublishService.php';
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/classes/SecteurService.php';

$service = new SecteurService($pdo);
$service->ensureSchema();

$websiteId = max(1, (int)($_GET['website_id'] ?? $_SESSION['current_website_id'] ?? 1));
$action = $_GET['action'] ?? '';

if ($action === 'create') {
    $newId = $service->createSecteur($websiteId, [
        'name' => trim((string)($_GET['name'] ?? 'Nouveau secteur')),
        'city_name' => trim((string)($_GET['city_name'] ?? '')),
    ]);
    header('Location: /admin/dashboard.php?page=secteurs-edit&id=' . $newId . '&website_id=' . $websiteId . '&msg=created');
    exit;
}

if ($action === 'status' && isset($_GET['id'], $_GET['status'])) {
    $service->updateStatus((int)$_GET['id'], $websiteId, (string)$_GET['status']);
    header('Location: /admin/dashboard.php?page=secteurs&website_id=' . $websiteId . '&msg=status_updated');
    exit;
}

$search = trim((string)($_GET['q'] ?? ''));
$statusFilter = trim((string)($_GET['status'] ?? 'all'));
$secteurs = $service->listSecteurs($websiteId, $search, $statusFilter);
$statuses = $service->getStatuses();
$flash = $_GET['msg'] ?? '';
?>

<div class="page-hd anim" style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap">
    <div>
        <h1><i class="fas fa-map-marked-alt" style="color:#4f46e5;margin-right:8px"></i>Secteurs</h1>
        <div class="page-hd-sub">Pages locales piliers SEO par secteur/quartier (indépendant des pages & articles)</div>
    </div>
    <a href="?page=secteurs&action=create&website_id=<?= (int)$websiteId ?>" class="btn btn-p btn-sm">
        <i class="fas fa-plus"></i> Nouveau secteur
    </a>
</div>

<?php if ($flash): ?>
<div class="card" style="padding:12px 16px;margin-bottom:12px;background:#ecfeff;border-color:#a5f3fc">
    <?= htmlspecialchars($flash === 'created' ? 'Secteur créé.' : ($flash === 'status_updated' ? 'Statut mis à jour.' : $flash)) ?>
</div>
<?php endif; ?>

<div class="card anim" style="padding:16px;margin-bottom:16px">
    <form method="get" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
        <input type="hidden" name="page" value="secteurs">
        <input type="hidden" name="website_id" value="<?= (int)$websiteId ?>">
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher un secteur" class="input" style="max-width:280px">
        <select name="status" class="input" style="max-width:220px">
            <option value="all">Tous les statuts</option>
            <?php foreach ($statuses as $status): ?>
                <option value="<?= htmlspecialchars($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>>
                    <?= htmlspecialchars($service->getStatusLabel($status)) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-sm" type="submit"><i class="fas fa-filter"></i> Filtrer</button>
    </form>
</div>

<div class="card anim">
    <div style="overflow:auto">
        <table class="table" style="width:100%">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Ville</th>
                    <th>Slug</th>
                    <th>Statut</th>
                    <th>Mis à jour</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$secteurs): ?>
                <tr><td colspan="6" style="padding:20px;text-align:center;color:#64748b">Aucun secteur.</td></tr>
            <?php else: ?>
                <?php foreach ($secteurs as $s): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($s['name']) ?></strong><?= !empty($s['is_primary']) ? ' <span class="badge">Principal</span>' : '' ?></td>
                        <td><?= htmlspecialchars($s['city_name'] ?? '-') ?></td>
                        <td><code>/<?= htmlspecialchars($s['slug']) ?></code></td>
                        <td><?= htmlspecialchars($service->getStatusLabel($s['status'])) ?></td>
                        <td><?= htmlspecialchars($s['updated_at']) ?></td>
                        <td style="text-align:right;white-space:nowrap">
                            <a class="btn btn-sm" href="?page=secteurs-edit&id=<?= (int)$s['id'] ?>&website_id=<?= (int)$websiteId ?>">Éditer</a>
                            <a class="btn btn-sm" target="_blank" href="/front/router.php?slug=<?= urlencode($s['slug']) ?>&preview=1&website_id=<?= (int)$websiteId ?>">Preview</a>
                            <?php if ($s['status'] === 'published'): ?>
                                <a class="btn btn-sm" href="?page=secteurs&action=status&id=<?= (int)$s['id'] ?>&status=draft&website_id=<?= (int)$websiteId ?>">Dépublier</a>
                            <?php else: ?>
                                <a class="btn btn-p btn-sm" href="?page=secteurs&action=status&id=<?= (int)$s['id'] ?>&status=published&website_id=<?= (int)$websiteId ?>">Publier</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

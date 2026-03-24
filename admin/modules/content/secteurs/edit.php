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
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: /admin/dashboard.php?page=secteurs&website_id=' . $websiteId);
    exit;
}

$secteur = $service->getSecteur($id, $websiteId);
if (!$secteur) {
    header('Location: /admin/dashboard.php?page=secteurs&website_id=' . $websiteId . '&msg=not_found');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sections = [];
    $postedSections = $_POST['sections'] ?? [];
    if (is_array($postedSections)) {
        foreach ($postedSections as $sectionId => $payload) {
            $sections[] = [
                'id' => (int)$sectionId,
                'section_label' => $payload['section_label'] ?? '',
                'content' => $payload['content'] ?? '',
                'source_type' => $payload['source_type'] ?? 'manual',
                'is_enabled' => isset($payload['is_enabled']) ? 1 : 0,
                'sort_order' => (int)($payload['sort_order'] ?? 0),
            ];
        }
    }

    $service->updateSecteur($id, $websiteId, [
        'name' => $_POST['name'] ?? '',
        'city_name' => $_POST['city_name'] ?? '',
        'slug' => $_POST['slug'] ?? '',
        'status' => $_POST['status'] ?? 'draft',
        'excerpt' => $_POST['excerpt'] ?? '',
        'intro' => $_POST['intro'] ?? '',
        'is_primary' => isset($_POST['is_primary']) ? 1 : 0,
        'sort_order' => (int)($_POST['sort_order'] ?? 0),
        'seo_title' => $_POST['seo_title'] ?? '',
        'seo_description' => $_POST['seo_description'] ?? '',
        'canonical_url' => $_POST['canonical_url'] ?? '',
        'sections' => $sections,
    ]);

    header('Location: /admin/dashboard.php?page=secteurs-edit&id=' . $id . '&website_id=' . $websiteId . '&msg=saved');
    exit;
}

$secteur = $service->getSecteur($id, $websiteId);
$statuses = $service->getStatuses();
$flash = $_GET['msg'] ?? '';
?>
<div class="page-hd anim" style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap">
    <div>
        <h1><i class="fas fa-map-pin" style="color:#6366f1;margin-right:8px"></i>Éditer secteur</h1>
        <div class="page-hd-sub">Workflow: brouillon → données prêtes → IA généré → relu → publié</div>
    </div>
    <div style="display:flex;gap:8px">
        <a class="btn btn-sm" href="?page=secteurs&website_id=<?= (int)$websiteId ?>">Retour liste</a>
        <a class="btn btn-sm" target="_blank" href="/front/router.php?slug=<?= urlencode($secteur['slug']) ?>&preview=1&website_id=<?= (int)$websiteId ?>">Preview</a>
    </div>
</div>

<?php if ($flash === 'saved'): ?>
<div class="card" style="padding:12px 16px;margin-bottom:12px;background:#ecfdf5;border-color:#86efac">Secteur enregistré.</div>
<?php endif; ?>

<form method="post" class="card anim" style="padding:16px;display:grid;gap:16px">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px">
        <label>Nom (H1)
            <input class="input" name="name" value="<?= htmlspecialchars($secteur['name']) ?>" required>
        </label>
        <label>Ville
            <input class="input" name="city_name" value="<?= htmlspecialchars($secteur['city_name'] ?? '') ?>">
        </label>
        <label>Slug
            <input class="input" name="slug" value="<?= htmlspecialchars($secteur['slug']) ?>" required>
        </label>
        <label>Statut
            <select class="input" name="status">
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= htmlspecialchars($status) ?>" <?= $secteur['status'] === $status ? 'selected' : '' ?>>
                        <?= htmlspecialchars($service->getStatusLabel($status)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Ordre d'affichage
            <input class="input" type="number" name="sort_order" value="<?= (int)$secteur['sort_order'] ?>">
        </label>
        <label style="display:flex;align-items:center;gap:8px;margin-top:22px">
            <input type="checkbox" name="is_primary" value="1" <?= !empty($secteur['is_primary']) ? 'checked' : '' ?>> Secteur principal
        </label>
    </div>

    <label>Extrait
        <textarea class="input" name="excerpt" rows="2"><?= htmlspecialchars($secteur['excerpt'] ?? '') ?></textarea>
    </label>

    <label>Introduction locale
        <textarea class="input" name="intro" rows="4"><?= htmlspecialchars($secteur['intro'] ?? '') ?></textarea>
    </label>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <label>SEO title
            <input class="input" name="seo_title" value="<?= htmlspecialchars($secteur['seo_title'] ?? '') ?>">
        </label>
        <label>Canonical URL
            <input class="input" name="canonical_url" value="<?= htmlspecialchars($secteur['canonical_url'] ?? '') ?>">
        </label>
    </div>

    <label>SEO description
        <textarea class="input" name="seo_description" rows="2"><?= htmlspecialchars($secteur['seo_description'] ?? '') ?></textarea>
    </label>

    <div class="card" style="padding:12px;background:#f8fafc;border:1px solid #e2e8f0">
        <h3 style="margin-bottom:10px">Sections de contenu (édition section par section)</h3>
        <p style="margin-bottom:10px;color:#64748b">Prévu pour préremplissage Analyse de marché et génération IA (source_type).</p>
        <div style="display:grid;gap:12px">
            <?php foreach ($secteur['sections'] as $section): ?>
                <div style="border:1px solid #e2e8f0;border-radius:8px;padding:12px;background:white">
                    <div style="display:grid;grid-template-columns:2fr 1fr 100px;gap:10px;margin-bottom:8px">
                        <input class="input" name="sections[<?= (int)$section['id'] ?>][section_label]" value="<?= htmlspecialchars($section['section_label']) ?>">
                        <select class="input" name="sections[<?= (int)$section['id'] ?>][source_type]">
                            <?php foreach (['manual' => 'Manuel', 'ai' => 'IA', 'imported_market_analysis' => 'Analyse marché'] as $key => $label): ?>
                                <option value="<?= $key ?>" <?= $section['source_type'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input class="input" type="number" name="sections[<?= (int)$section['id'] ?>][sort_order]" value="<?= (int)$section['sort_order'] ?>">
                    </div>
                    <textarea class="input" rows="5" name="sections[<?= (int)$section['id'] ?>][content]"><?= htmlspecialchars($section['content'] ?? '') ?></textarea>
                    <label style="display:inline-flex;align-items:center;gap:8px;margin-top:8px">
                        <input type="checkbox" name="sections[<?= (int)$section['id'] ?>][is_enabled]" value="1" <?= !empty($section['is_enabled']) ? 'checked' : '' ?>> Section active
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap">
        <div style="color:#64748b">Publication: <?= $secteur['published_at'] ? htmlspecialchars($secteur['published_at']) : 'non publiée' ?></div>
        <button class="btn btn-p" type="submit"><i class="fas fa-save"></i> Enregistrer</button>
    </div>
</form>

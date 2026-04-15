<?php // modules/funnels/views/wizard/step4_preview.php
$id = (int) ($_GET['id'] ?? 0);
$db = \Database::getInstance();
$service = new FunnelService($db);
$funnel  = $id ? $service->getById($id) : null;
if (!$funnel): ?>
<div class="alert alert-danger">Funnel introuvable.</div>
<?php return; endif; ?>
<div class="container-fluid px-4" style="max-width: 860px;">

    <!-- Progress -->
    <div class="mb-4">
        <div class="progress" style="height:4px;">
            <div class="progress-bar bg-success" style="width:100%"></div>
        </div>
        <div class="d-flex justify-content-between mt-1">
            <small class="text-muted">1. Canal</small>
            <small class="text-muted">2. Template</small>
            <small class="text-muted">3. Configuration</small>
            <small class="text-success fw-semibold">4. Publication ✓</small>
        </div>
    </div>

    <div class="text-center mb-4">
        <div class="rounded-circle bg-success-subtle d-inline-flex align-items-center justify-content-center mb-3"
             style="width:72px;height:72px">
            <i class="fas fa-check fa-2x text-success"></i>
        </div>
        <h2 class="h4 fw-bold mb-1">Votre funnel est prêt</h2>
        <p class="text-muted">Vérifiez les informations et publiez quand vous êtes prêt(e).</p>
    </div>

    <!-- Récap -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <small class="text-muted d-block">Nom</small>
                    <strong><?= htmlspecialchars($funnel['name']) ?></strong>
                </div>
                <div class="col-md-6">
                    <small class="text-muted d-block">Canal</small>
                    <strong><?= htmlspecialchars($funnel['canal']) ?></strong>
                </div>
                <div class="col-md-6">
                    <small class="text-muted d-block">URL publique</small>
                    <code><?= rtrim(APP_URL, '/') ?>/lp/<?= htmlspecialchars($funnel['slug']) ?></code>
                </div>
                <div class="col-md-6">
                    <small class="text-muted d-block">Ville</small>
                    <strong><?= htmlspecialchars($funnel['ville'] ?? '—') ?></strong>
                </div>

                <!-- SEO Preview -->
                <div class="col-12 mt-2">
                    <small class="text-muted d-block mb-2">Aperçu SERP Google</small>
                    <div class="bg-light rounded p-3">
                        <div class="text-primary" style="font-size:18px;line-height:1.3">
                            <?= htmlspecialchars($funnel['seo_title'] ?? '') ?>
                        </div>
                        <div class="text-success small"><?= rtrim(APP_URL, '/') ?>/lp/<?= htmlspecialchars($funnel['slug']) ?></div>
                        <div class="text-muted small mt-1"><?= htmlspecialchars($funnel['meta_description'] ?? '') ?></div>
                    </div>
                </div>

                <!-- Checklist -->
                <div class="col-12 mt-2">
                    <small class="text-muted d-block mb-2">Checklist avant publication</small>
                    <?php
                    $checks = [
                        ['ok' => !empty($funnel['h1']),              'label' => 'H1 renseigné'],
                        ['ok' => !empty($funnel['seo_title']),        'label' => 'SEO Title (max 70 car.)'],
                        ['ok' => !empty($funnel['meta_description']), 'label' => 'Meta description'],
                        ['ok' => !empty($funnel['cta_label']),        'label' => 'CTA principal défini'],
                        ['ok' => $funnel['canal'] !== 'google_ads' || !empty($funnel['utm_campaign']), 'label' => 'UTM campaign (Google Ads)'],
                    ];
                    foreach ($checks as $c): ?>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <?php if ($c['ok']): ?>
                            <i class="fas fa-check-circle text-success"></i>
                        <?php else: ?>
                            <i class="fas fa-circle-exclamation text-warning"></i>
                        <?php endif; ?>
                        <small><?= htmlspecialchars($c['label']) ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-3 justify-content-between">
        <a href="?module=funnels&action=edit&id=<?= $funnel['id'] ?>" class="btn btn-outline-secondary">
            <i class="fas fa-pen me-1"></i>Modifier
        </a>
        <div class="d-flex gap-2">
            <a href="?module=funnels" class="btn btn-outline-secondary">Voir tous les funnels</a>
            <?php if ($funnel['status'] !== 'published'): ?>
            <button class="btn btn-success px-4" onclick="publishFunnel(<?= $funnel['id'] ?>)">
                <i class="fas fa-rocket me-1"></i>Publier maintenant
            </button>
            <?php else: ?>
            <a href="<?= rtrim(APP_URL, '/') ?>/lp/<?= htmlspecialchars($funnel['slug']) ?>"
               target="_blank" class="btn btn-primary px-4">
                <i class="fas fa-external-link-alt me-1"></i>Voir la landing page
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function publishFunnel(id) {
    fetch('/public/admin/api/funnels/ajax.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'publish', id})
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) location.reload();
        else alert(d.error || 'Erreur lors de la publication');
    });
}
</script>

<?php // modules/funnels/views/edit.php
$canalInfo = $canaux[$funnel['canal']] ?? ['label' => $funnel['canal'], 'color' => '#666'];
$isGoogleAds = $funnel['canal'] === 'google_ads';
$isPublished = $funnel['status'] === 'published';
?>
<div class="container-fluid px-4" style="max-width: 900px;">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="?module=funnels" class="text-muted text-decoration-none small">
                <i class="fas fa-arrow-left me-1"></i>Tous les funnels
            </a>
            <h1 class="h4 fw-bold mb-0 mt-1"><?= htmlspecialchars($funnel['name']) ?></h1>
            <small class="text-muted">
                <span class="badge rounded-pill" style="background:<?= $canalInfo['color'] ?>22;color:<?= $canalInfo['color'] ?>">
                    <?= htmlspecialchars($canalInfo['label']) ?>
                </span>
                /lp/<?= htmlspecialchars($funnel['slug']) ?>
            </small>
        </div>
        <div class="d-flex gap-2">
            <?php if ($isPublished): ?>
                <a href="<?= rtrim(APP_URL, '/') ?>/lp/<?= htmlspecialchars($funnel['slug']) ?>"
                   target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-external-link-alt me-1"></i>Voir la LP
                </a>
                <button class="btn btn-outline-secondary btn-sm" onclick="unpublishFunnel(<?= $funnel['id'] ?>)">
                    Dépublier
                </button>
            <?php else: ?>
                <button class="btn btn-success btn-sm" onclick="publishFunnel(<?= $funnel['id'] ?>)">
                    <i class="fas fa-rocket me-1"></i>Publier
                </button>
            <?php endif; ?>
        </div>
    </div>

    <form id="edit-form">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?= $funnel['id'] ?>">
        <input type="hidden" name="canal" value="<?= htmlspecialchars($funnel['canal']) ?>">
        <input type="hidden" name="template_id" value="<?= htmlspecialchars($funnel['template_id']) ?>">

        <!-- Ciblage -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-semibold border-bottom py-3">
                <i class="fas fa-map-marker-alt me-2 text-primary"></i>Ciblage
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nom interne</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($funnel['name']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Ville</label>
                        <input type="text" name="ville" class="form-control" value="<?= htmlspecialchars($funnel['ville'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Quartier</label>
                        <input type="text" name="quartier" class="form-control" value="<?= htmlspecialchars($funnel['quartier'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mot-clé</label>
                        <input type="text" name="keyword" class="form-control" value="<?= htmlspecialchars($funnel['keyword'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Persona</label>
                        <select name="persona" class="form-select">
                            <?php foreach (['vendeur','acheteur','investisseur','primo_accedant','senior'] as $p): ?>
                            <option value="<?= $p ?>" <?= ($funnel['persona'] ?? '') === $p ? 'selected' : '' ?>>
                                <?= ucfirst(str_replace('_', ' ', $p)) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Niveau de conscience</label>
                        <select name="awareness_level" class="form-select">
                            <?php foreach (['problem_aware','solution_aware','product_aware','most_aware'] as $a): ?>
                            <option value="<?= $a ?>" <?= ($funnel['awareness_level'] ?? '') === $a ? 'selected' : '' ?>>
                                <?= ucwords(str_replace('_', ' ', $a)) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu LP -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-semibold border-bottom py-3">
                <i class="fas fa-pen-nib me-2 text-primary"></i>Contenu
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">H1</label>
                        <input type="text" name="h1" class="form-control" maxlength="120"
                               value="<?= htmlspecialchars($funnel['h1'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Promesse / sous-titre</label>
                        <input type="text" name="promise" class="form-control"
                               value="<?= htmlspecialchars($funnel['promise'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">CTA principal</label>
                        <input type="text" name="cta_label" class="form-control"
                               value="<?= htmlspecialchars($funnel['cta_label'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">SEO Title <small class="text-muted">(max 70)</small></label>
                        <input type="text" name="seo_title" class="form-control" maxlength="70"
                               value="<?= htmlspecialchars($funnel['seo_title'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Meta Description <small class="text-muted">(max 160)</small></label>
                        <textarea name="meta_description" class="form-control" rows="2" maxlength="160"><?= htmlspecialchars($funnel['meta_description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($isGoogleAds): ?>
        <!-- Google Ads -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-semibold border-bottom py-3">
                <i class="fab fa-google me-2 text-warning"></i>Google Ads
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Campagne</label>
                        <input type="text" name="campaign_name" class="form-control"
                               value="<?= htmlspecialchars($funnel['campaign_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ad Group</label>
                        <input type="text" name="ad_group" class="form-control"
                               value="<?= htmlspecialchars($funnel['ad_group'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">utm_source</label>
                        <input type="text" name="utm_source" class="form-control"
                               value="<?= htmlspecialchars($funnel['utm_source'] ?? 'google') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">utm_medium</label>
                        <input type="text" name="utm_medium" class="form-control"
                               value="<?= htmlspecialchars($funnel['utm_medium'] ?? 'cpc') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">utm_campaign</label>
                        <input type="text" name="utm_campaign" class="form-control"
                               value="<?= htmlspecialchars($funnel['utm_campaign'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Ressource & Séquence -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-semibold border-bottom py-3">
                <i class="fas fa-link me-2 text-primary"></i>Ressource & Séquence
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Séquence email</label>
                        <select name="sequence_id" class="form-select">
                            <option value="">Aucune séquence</option>
                            <?php foreach ($sequences as $seq): ?>
                            <option value="<?= $seq['id'] ?>"
                                    <?= (int)($funnel['sequence_id'] ?? 0) === (int)$seq['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($seq['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-end">
            <a href="?module=funnels" class="btn btn-outline-secondary">Annuler</a>
            <button type="button" class="btn btn-primary" onclick="saveFunnel()">
                <i class="fas fa-save me-1"></i>Enregistrer
            </button>
        </div>
    </form>
</div>

<script>
function saveFunnel() {
    const form = document.getElementById('edit-form');
    const data = Object.fromEntries(new FormData(form));
    fetch('/public/admin/api/funnels/ajax.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            const toast = document.createElement('div');
            toast.className = 'position-fixed top-0 end-0 m-3 alert alert-success shadow';
            toast.innerHTML = '<i class="fas fa-check me-2"></i>Enregistré !';
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2500);
        } else {
            alert((d.errors || [d.error || 'Erreur']).join('\n'));
        }
    });
}

function publishFunnel(id) {
    if (!confirm('Publier ce funnel ? Il sera accessible publiquement.')) return;
    fetch('/public/admin/api/funnels/ajax.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'publish', id})
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
}

function unpublishFunnel(id) {
    if (!confirm('Dépublier ce funnel ?')) return;
    fetch('/public/admin/api/funnels/ajax.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'unpublish', id})
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
</script>

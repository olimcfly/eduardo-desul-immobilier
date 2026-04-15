<?php // modules/funnels/views/wizard/step3_config.php
$canal      = $_GET['canal'] ?? '';
$templateId = $_GET['template'] ?? '';
$canalInfo  = $canaux[$canal] ?? ['label' => $canal, 'color' => '#666'];
$tplInfo    = $templates[$canal][$templateId] ?? ['label' => $templateId];
$isGoogleAds = $canal === 'google_ads';
$isSeo      = $canal === 'seo';
?>
<div class="container-fluid px-4" style="max-width: 860px;">

    <!-- Progress -->
    <div class="mb-4">
        <div class="d-flex align-items-center gap-2 mb-2">
            <a href="?module=funnels&action=wizard&step=2&canal=<?= $canal ?>" class="text-muted text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i>Retour
            </a>
            <span class="text-muted">›</span>
            <span class="fw-semibold">Nouveau funnel</span>
        </div>
        <div class="progress" style="height:4px;">
            <div class="progress-bar bg-primary" style="width:75%"></div>
        </div>
        <div class="d-flex justify-content-between mt-1">
            <small class="text-muted">1. Canal</small>
            <small class="text-muted">2. Template</small>
            <small class="text-primary fw-semibold">3. Configuration</small>
            <small class="text-muted">4. Publication</small>
        </div>
    </div>

    <h2 class="h4 fw-bold mb-1">Configurez votre funnel</h2>
    <p class="text-muted mb-4">
        Template : <strong><?= htmlspecialchars($tplInfo['label']) ?></strong> —
        Canal : <span style="color:<?= $canalInfo['color'] ?>"><?= htmlspecialchars($canalInfo['label']) ?></span>
    </p>

    <form id="funnel-config-form" method="POST" action="/public/admin/api/funnels/ajax.php">
        <input type="hidden" name="action" value="create">
        <input type="hidden" name="canal" value="<?= htmlspecialchars($canal) ?>">
        <input type="hidden" name="template_id" value="<?= htmlspecialchars($templateId) ?>">
        <?= csrf_field() ?? '' ?>

        <!-- Bloc 1 : Ciblage -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-semibold border-bottom py-3">
                <i class="fas fa-map-marker-alt me-2 text-primary"></i>Ciblage géographique & persona
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Ville <span class="text-danger">*</span></label>
                        <input type="text" name="ville" class="form-control" placeholder="ex: Lyon" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Quartier</label>
                        <input type="text" name="quartier" class="form-control" placeholder="ex: Croix-Rousse">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mot-clé principal
                            <?php if ($isGoogleAds): ?>
                            <i class="fas fa-circle-info text-warning ms-1" title="Doit apparaître dans le H1 pour le Quality Score Google Ads"></i>
                            <?php endif; ?>
                        </label>
                        <input type="text" name="keyword" id="keyword" class="form-control"
                               placeholder="ex: vendre maison" <?= $isGoogleAds ? 'required' : '' ?>>
                        <?php if ($isGoogleAds): ?>
                        <small class="text-muted">Doit être présent dans le H1 (Quality Score)</small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Persona</label>
                        <select name="persona" class="form-select">
                            <option value="vendeur">Vendeur</option>
                            <option value="acheteur">Acheteur</option>
                            <option value="investisseur">Investisseur</option>
                            <option value="primo_accedant">Primo-accédant</option>
                            <option value="senior">Senior</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Niveau de conscience</label>
                        <select name="awareness_level" class="form-select">
                            <option value="problem_aware">Problem Aware — connaît le problème</option>
                            <option value="solution_aware">Solution Aware — cherche une solution</option>
                            <option value="product_aware">Product Aware — compare des offres</option>
                            <option value="most_aware">Most Aware — prêt à agir</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bloc 2 : Contenu LP -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-semibold border-bottom py-3">
                <i class="fas fa-pen-nib me-2 text-primary"></i>Contenu de la landing page
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Nom interne (admin) <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control"
                               placeholder="ex: Guide Vendeur Lyon Croix-Rousse" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold d-flex justify-content-between">
                            <span>H1 — Titre principal <span class="text-danger">*</span></span>
                            <span class="text-muted small" id="h1-count">0/120</span>
                        </label>
                        <input type="text" name="h1" id="h1" class="form-control"
                               placeholder="ex: Vendez votre maison à Lyon Croix-Rousse au meilleur prix"
                               maxlength="120" required>
                        <?php if ($isGoogleAds): ?>
                        <div id="kw-warning" class="alert alert-warning py-2 mt-1 d-none small">
                            <i class="fas fa-triangle-exclamation me-1"></i>
                            Le mot-clé doit apparaître dans le H1 (Quality Score Google Ads)
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Promesse / Sous-titre</label>
                        <input type="text" name="promise" class="form-control"
                               placeholder="ex: Téléchargez gratuitement notre guide complet">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Label du CTA principal</label>
                        <input type="text" name="cta_label" class="form-control"
                               placeholder="ex: Télécharger le guide gratuit" value="Télécharger gratuitement">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">CTA secondaire (optionnel)</label>
                        <input type="text" name="cta_secondary" class="form-control"
                               placeholder="ex: Voir les témoignages">
                    </div>
                </div>
            </div>
        </div>

        <!-- Bloc 3 : SEO -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-semibold border-bottom py-3">
                <i class="fas fa-search me-2 text-primary"></i>SEO
                <small class="text-muted fw-normal ms-2">(auto-généré si vide)</small>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold d-flex justify-content-between">
                            <span>SEO Title</span>
                            <span id="title-count" class="text-muted small">0/70</span>
                        </label>
                        <input type="text" name="seo_title" id="seo-title" class="form-control"
                               placeholder="Auto-généré depuis ville + template + année" maxlength="70">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold d-flex justify-content-between">
                            <span>Meta Description</span>
                            <span id="meta-count" class="text-muted small">0/160</span>
                        </label>
                        <textarea name="meta_description" id="meta-desc" class="form-control" rows="2"
                                  placeholder="Auto-générée si vide" maxlength="160"></textarea>
                    </div>
                    <?php if ($isSeo): ?>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="indexable" value="1" id="indexable">
                            <label class="form-check-label" for="indexable">
                                Page indexable (SEO) — laissez décoché pour les LP Ads
                            </label>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Bloc 4 : Google Ads (conditionnel) -->
        <?php if ($isGoogleAds): ?>
        <div class="card border-0 shadow-sm mb-4 border-warning-subtle">
            <div class="card-header bg-transparent fw-semibold border-bottom py-3">
                <i class="fab fa-google me-2 text-warning"></i>Paramètres Google Ads
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nom de la campagne</label>
                        <input type="text" name="campaign_name" class="form-control" placeholder="ex: Vente Lyon 2025">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ad Group</label>
                        <input type="text" name="ad_group" class="form-control" placeholder="ex: Lyon Croix-Rousse Maison">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">utm_source</label>
                        <input type="text" name="utm_source" class="form-control" value="google">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">utm_medium</label>
                        <input type="text" name="utm_medium" class="form-control" value="cpc">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">utm_campaign</label>
                        <input type="text" name="utm_campaign" class="form-control" placeholder="ex: vente-lyon-2025">
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Bloc 5 : Ressource & Séquence -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-semibold border-bottom py-3">
                <i class="fas fa-link me-2 text-primary"></i>Ressource & Séquence automatique
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ressource associée</label>
                        <select name="ressource_id" class="form-select">
                            <option value="">Aucune ressource</option>
                            <!-- populated via AJAX ou include -->
                        </select>
                        <small class="text-muted">Guide PDF envoyé automatiquement après soumission</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Séquence email</label>
                        <select name="sequence_id" class="form-select">
                            <option value="">Aucune séquence</option>
                            <!-- populated via AJAX ou include -->
                        </select>
                        <small class="text-muted">Relances automatiques J0 / J+2 / J+5</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Type de thank you page</label>
                        <div class="row g-2">
                            <?php foreach ([
                                'telechargement'  => ['📥', 'Téléchargement'],
                                'estimation_recue'=> ['🏠', 'Estimation reçue'],
                                'rdv_confirme'    => ['📅', 'RDV confirmé'],
                                'contact_recu'    => ['✉️', 'Contact reçu'],
                            ] as $val => [$icon, $label]): ?>
                            <div class="col-6 col-md-3">
                                <input type="radio" class="btn-check" name="thankyou_type" id="ty_<?= $val ?>"
                                       value="<?= $val ?>" <?= $val === 'telechargement' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-secondary w-100 py-3" for="ty_<?= $val ?>">
                                    <div class="fs-4 mb-1"><?= $icon ?></div>
                                    <small><?= $label ?></small>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-3 justify-content-end">
            <button type="button" class="btn btn-outline-secondary" onclick="submitFunnel('draft')">
                Sauver en brouillon
            </button>
            <button type="button" class="btn btn-primary" onclick="submitFunnel('review')">
                Continuer <i class="fas fa-arrow-right ms-1"></i>
            </button>
        </div>
    </form>
</div>

<script>
// Compteurs de caractères
document.querySelectorAll('[id="h1"], [id="seo-title"], [id="meta-desc"]').forEach(() => {});

function countChars(id, counterId, max) {
    const el = document.getElementById(id);
    const counter = document.getElementById(counterId);
    if (!el || !counter) return;
    const update = () => {
        const len = el.value.length;
        counter.textContent = len + '/' + max;
        counter.className = len > max * 0.9 ? 'text-warning small' : 'text-muted small';
    };
    el.addEventListener('input', update);
    update();
}

countChars('h1', 'h1-count', 120);
countChars('seo-title', 'title-count', 70);
countChars('meta-desc', 'meta-count', 160);

// Vérification Quality Score (Google Ads)
<?php if ($isGoogleAds): ?>
function checkQualityScore() {
    const kw = document.getElementById('keyword')?.value?.toLowerCase() || '';
    const h1 = document.getElementById('h1')?.value?.toLowerCase() || '';
    const warn = document.getElementById('kw-warning');
    if (warn && kw && h1 && !h1.includes(kw)) {
        warn.classList.remove('d-none');
    } else if (warn) {
        warn.classList.add('d-none');
    }
}
document.getElementById('keyword')?.addEventListener('input', checkQualityScore);
document.getElementById('h1')?.addEventListener('input', checkQualityScore);
<?php endif; ?>

function submitFunnel(mode) {
    const form = document.getElementById('funnel-config-form');
    if (!form.checkValidity()) { form.reportValidity(); return; }

    const data = Object.fromEntries(new FormData(form));
    data.action = 'create';
    data.mode   = mode;

    fetch('/public/admin/api/funnels/ajax.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            window.location.href = '?module=funnels&action=wizard&step=4&id=' + d.id;
        } else {
            alert((d.errors || [d.error || 'Erreur']).join('\n'));
        }
    });
}
</script>

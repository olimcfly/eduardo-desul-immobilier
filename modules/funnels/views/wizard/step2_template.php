<?php // modules/funnels/views/wizard/step2_template.php
$canal = $_GET['canal'] ?? '';
$canalTemplates = $templates[$canal] ?? [];
$canalInfo = $canaux[$canal] ?? ['label' => $canal, 'color' => '#666'];

$templatePreviews = [
    'guide_vendeur_v1'     => ['thumb' => '📋', 'desc' => 'LP épurée avec formulaire de téléchargement. Idéale pour capturer vendeurs qualifiés. Above-the-fold optimisé.'],
    'estimation_cta_v1'    => ['thumb' => '🏠', 'desc' => 'LP avec simulateur de prix. Capture forte intention. Intégration formulaire estimateur.'],
    'rdv_direct_v1'        => ['thumb' => '📅', 'desc' => 'LP minimaliste pour prise de RDV immédiate. Un seul CTA, zéro friction.'],
    'guide_acheteur_v1'    => ['thumb' => '🔑', 'desc' => 'Guide complet pour acheteurs. Formulaire email simple. Séquence automatique incluse.'],
    'guide_local_v1'       => ['thumb' => '📍', 'desc' => 'Page locale indexable. Contenu marché + FAQ. Compatible SEO long terme.'],
    'fiche_ville_v1'       => ['thumb' => '🗺️', 'desc' => 'Fiche ville complète. Prix, tendances, quartiers. Formulaire de contact intégré.'],
    'tunnel_estimation_v1' => ['thumb' => '⚡', 'desc' => 'Tunnel multi-étapes. Estimateur + capture email. Haute conversion.'],
    'prise_rdv_v1'         => ['thumb' => '🤝', 'desc' => 'Page RDV simple et directe. Formulaire 3 champs. Thank you page incluse.'],
];
?>
<div class="container-fluid px-4" style="max-width: 860px;">

    <!-- Progress -->
    <div class="mb-4">
        <div class="d-flex align-items-center gap-2 mb-2">
            <a href="?module=funnels&action=wizard&step=1" class="text-muted text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i>Retour
            </a>
            <span class="text-muted">›</span>
            <span class="fw-semibold">Nouveau funnel</span>
            <span class="text-muted">›</span>
            <span style="color:<?= $canalInfo['color'] ?>"><?= htmlspecialchars($canalInfo['label']) ?></span>
        </div>
        <div class="progress" style="height:4px;">
            <div class="progress-bar bg-primary" style="width:50%"></div>
        </div>
        <div class="d-flex justify-content-between mt-1">
            <small class="text-muted">1. Canal</small>
            <small class="text-primary fw-semibold">2. Template</small>
            <small class="text-muted">3. Configuration</small>
            <small class="text-muted">4. Publication</small>
        </div>
    </div>

    <h2 class="h4 fw-bold mb-1">Choisissez votre template</h2>
    <p class="text-muted mb-4">Templates verrouillés et optimisés pour la conversion. Aucune modification structurelle possible.</p>

    <?php if (empty($canalTemplates)): ?>
        <div class="alert alert-warning">Aucun template disponible pour ce canal.</div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($canalTemplates as $tplId => $tpl): ?>
        <?php $preview = $templatePreviews[$tplId] ?? ['thumb' => '📄', 'desc' => '']; ?>
        <div class="col-md-6">
            <a href="?module=funnels&action=wizard&step=3&canal=<?= $canal ?>&template=<?= $tplId ?>"
               class="card border text-decoration-none hover-lift h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="fs-2 lh-1"><?= $preview['thumb'] ?></div>
                        <div>
                            <h5 class="fw-semibold mb-1"><?= htmlspecialchars($tpl['label']) ?></h5>
                            <p class="text-muted small mb-2"><?= htmlspecialchars($preview['desc']) ?></p>
                            <div class="d-flex gap-2">
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-wpforms me-1"></i>
                                    <?= ucfirst($tpl['form']) ?>
                                </span>
                                <?php if ($tpl['indexable']): ?>
                                    <span class="badge bg-success-subtle text-success border-0">
                                        <i class="fas fa-search me-1"></i>Indexable
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning border-0">
                                        <i class="fas fa-eye-slash me-1"></i>Noindex
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.hover-lift { transition: transform .15s ease, box-shadow .15s ease; cursor: pointer; }
.hover-lift:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.08) !important; border-color: #0d6efd !important; }
</style>

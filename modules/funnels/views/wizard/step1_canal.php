<?php // modules/funnels/views/wizard/step1_canal.php ?>
<div class="container-fluid px-4" style="max-width: 860px;">

    <!-- Progress -->
    <div class="mb-4">
        <div class="d-flex align-items-center gap-2 mb-2">
            <a href="?module=funnels" class="text-muted text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i>Retour
            </a>
            <span class="text-muted">›</span>
            <span class="fw-semibold">Nouveau funnel</span>
        </div>
        <div class="progress" style="height:4px;">
            <div class="progress-bar bg-primary" style="width:25%"></div>
        </div>
        <div class="d-flex justify-content-between mt-1">
            <small class="text-primary fw-semibold">1. Canal</small>
            <small class="text-muted">2. Template</small>
            <small class="text-muted">3. Configuration</small>
            <small class="text-muted">4. Publication</small>
        </div>
    </div>

    <h2 class="h4 fw-bold mb-1">Quel est l'objectif de ce funnel ?</h2>
    <p class="text-muted mb-4">Choisissez le canal principal. Le système adaptera les templates disponibles.</p>

    <div class="row g-3">
        <?php foreach ($canaux as $key => $canal): ?>
        <div class="col-md-4">
            <a href="?module=funnels&action=wizard&step=2&canal=<?= $key ?>"
               class="card border text-decoration-none hover-lift h-100"
               style="--hover-border:<?= $canal['color'] ?>">
                <div class="card-body p-4 text-center">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                         style="width:56px;height:56px;background:<?= $canal['color'] ?>18;">
                        <i class="fas <?= $canal['icon'] ?> fa-lg" style="color:<?= $canal['color'] ?>"></i>
                    </div>
                    <h5 class="fw-semibold mb-1"><?= htmlspecialchars($canal['label']) ?></h5>
                    <?php
                    $descs = [
                        'google_ads'   => 'Landing pages conformes Google Ads, Quality Score optimisé',
                        'facebook_ads' => 'Pages de capture pour campagnes Facebook et Instagram',
                        'social'       => 'Pages pour trafic organique réseaux sociaux',
                        'seo'          => 'Pages indexables, optimisées pour le référencement naturel',
                        'rdv'          => 'Page de prise de rendez-vous directe',
                        'estimateur'   => 'Tunnel d\'estimation immobilière avec capture de lead',
                    ];
                    ?>
                    <small class="text-muted"><?= $descs[$key] ?? '' ?></small>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.hover-lift {
    transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    cursor: pointer;
}
.hover-lift:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,.08) !important;
    border-color: var(--hover-border) !important;
}
</style>

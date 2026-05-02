<?php

declare(strict_types=1);

$current = 'hub';
require __DIR__ . '/views/_subnav.php';
?>
<div class="page-header">
    <h1><i class="fas fa-chart-line page-icon"></i> HUB <span class="page-title-accent">Optimiser</span></h1>
    <p>Analysez et améliorez en continu vos performances</p>
    <p style="margin:.75rem 0 0;font-size:.95rem;"><a href="/admin?module=optimiser&view=parcours" style="font-weight:600;color:#6366f1;text-decoration:none;">Parcours d’optimisation en 5 étapes →</a></p>
</div>

<div class="cards-container">

    <a class="card" href="/admin?module=optimiser&view=analytics" style="--card-accent:#3498db; --card-icon-bg:#e3f2fd; text-decoration:none; color:inherit; display:block;">
        <div class="card-header">
            <div class="card-icon"><i class="fas fa-chart-bar"></i></div>
            <h3 class="card-title">Tableau de bord Analytics</h3>
        </div>
        <p class="card-description">Vue consolidée de vos KPIs : leads, estimations et trafic pages (si disponible).</p>
        <div class="card-tags"><span class="tag">KPIs</span><span class="tag">Reporting</span><span class="tag">30 / 90 jours</span></div>
        <span class="card-action"><i class="fas fa-arrow-right"></i> Ouvrir le tableau de bord</span>
    </a>

    <a class="card" href="/admin?module=optimiser&view=ab-testing" style="--card-accent:#f39c12; --card-icon-bg:#fef9e7; text-decoration:none; color:inherit; display:block;">
        <div class="card-header">
            <div class="card-icon"><i class="fas fa-vials"></i></div>
            <h3 class="card-title">A/B Testing</h3>
        </div>
        <p class="card-description">Testez vos pages, emails et messages pour maximiser les taux de conversion.</p>
        <div class="card-tags"><span class="tag">Tests</span><span class="tag">Conversion</span></div>
        <span class="card-action"><i class="fas fa-arrow-right"></i> Ouvrir le cadre A/B</span>
    </a>

    <a class="card" href="/admin?module=optimiser&view=recommandations" style="--card-accent:#27ae60; --card-icon-bg:#eafaf1; text-decoration:none; color:inherit; display:block;">
        <div class="card-header">
            <div class="card-icon"><i class="fas fa-lightbulb"></i></div>
            <h3 class="card-title">Recommandations IA</h3>
        </div>
        <p class="card-description">Pistes d’action et liens vers les outils déjà disponibles (analytics, SEO, rapports).</p>
        <div class="card-tags"><span class="tag">IA</span><span class="tag">Insights</span></div>
        <span class="card-action"><i class="fas fa-arrow-right"></i> Voir les recommandations</span>
    </a>

    <a class="card" href="/admin?module=optimiser&view=rapport-mensuel" style="--card-accent:#e74c3c; --card-icon-bg:#fdedec; text-decoration:none; color:inherit; display:block;">
        <div class="card-header">
            <div class="card-icon"><i class="fas fa-file-chart-line"></i></div>
            <h3 class="card-title">Rapport mensuel</h3>
        </div>
        <p class="card-description">Générez votre rapport de performance mensuel en un clic.</p>
        <div class="card-tags"><span class="tag">Rapport</span><span class="tag">Export PDF</span></div>
        <span class="card-action"><i class="fas fa-arrow-right"></i> Ouvrir le rapport</span>
    </a>

</div>

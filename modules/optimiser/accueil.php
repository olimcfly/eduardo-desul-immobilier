<?php
$pageTitle = "Optimiser";
$pageDescription = "Analysez et améliorez en continu vos performances";

require_once '../../admin/views/layout.php';

function renderContent() {
    ?>
    <div class="page-header">
        <div class="breadcrumb"><a href="/admin/">Accueil</a> &rsaquo; Optimiser</div>
        <h1><i class="fas fa-chart-line page-icon"></i> HUB <span class="page-title-accent">Optimiser</span></h1>
        <p>Analysez et améliorez en continu vos performances</p>
    </div>
    <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" class="search-input" placeholder="Rechercher…">
    </div>
    <div class="cards-container">

        <div class="card" style="--card-accent:#3498db; --card-icon-bg:#e3f2fd;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-chart-bar"></i></div>
                <h3 class="card-title">Tableau de bord Analytics</h3>
            </div>
            <p class="card-description">Vue consolidée de vos KPIs : leads, mandats, trafic, conversions.</p>
            <div class="card-tags"><span class="tag">KPIs</span><span class="tag">Reporting</span></div>
            <a href="#" class="card-action"><i class="fas fa-play"></i> Consulter</a>
        </div>

        <div class="card" style="--card-accent:#f39c12; --card-icon-bg:#fef9e7;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-vials"></i></div>
                <h3 class="card-title">A/B Testing</h3>
            </div>
            <p class="card-description">Testez vos pages, emails et messages pour maximiser les taux de conversion.</p>
            <div class="card-tags"><span class="tag">Tests</span><span class="tag">Conversion</span></div>
            <a href="#" class="card-action"><i class="fas fa-play"></i> Configurer</a>
        </div>

        <div class="card" style="--card-accent:#27ae60; --card-icon-bg:#eafaf1;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-lightbulb"></i></div>
                <h3 class="card-title">Recommandations IA</h3>
            </div>
            <p class="card-description">Suggestions personnalisées pour améliorer vos actions chaque semaine.</p>
            <div class="card-tags"><span class="tag">IA</span><span class="tag">Insights</span></div>
            <a href="#" class="card-action"><i class="fas fa-play"></i> Voir</a>
        </div>

        <div class="card" style="--card-accent:#e74c3c; --card-icon-bg:#fdedec;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-file-chart-line"></i></div>
                <h3 class="card-title">Rapport mensuel</h3>
            </div>
            <p class="card-description">Générez votre rapport de performance mensuel en un clic.</p>
            <div class="card-tags"><span class="tag">Rapport</span><span class="tag">Export PDF</span></div>
            <a href="#" class="card-action"><i class="fas fa-play"></i> Générer</a>
        </div>

    </div>
    <?php
}

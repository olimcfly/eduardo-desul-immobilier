<?php
$pageTitle = "Construire";
$pageDescription = "Posez les bases solides de votre activité";

require_once '../../admin/views/layout.php';

function renderContent() {
    ?>
    <div class="page-header">
        <div class="breadcrumb">
            <a href="/admin/">Accueil</a> &rsaquo; Construire
        </div>
        <h1>
            <i class="fas fa-layer-group page-icon"></i>
            HUB <span class="page-title-accent">Construire</span>
        </h1>
        <p>Posez les bases solides de votre activité</p>
    </div>

    <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" class="search-input" placeholder="Rechercher…">
    </div>

    <div class="cards-container">

        <div class="card" style="--card-accent:#e74c3c; --card-icon-bg:#fdedec;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-anchor"></i></div>
                <h3 class="card-title">Méthode ANCRE+</h3>
            </div>
            <p class="card-description">Définissez votre stratégie et positionnement avec le GPS Stratégique FOTO + MERE.</p>
            <div class="card-tags">
                <span class="tag">GPS Stratégique</span>
                <span class="tag">FOTO</span>
                <span class="tag">MERE</span>
            </div>
            <a href="#" class="card-action"><i class="fas fa-play"></i> Démarrer</a>
        </div>

        <div class="card" style="--card-accent:#3498db; --card-icon-bg:#e3f2fd;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-brain"></i></div>
                <h3 class="card-title">NeuroPersona</h3>
            </div>
            <p class="card-description">Identifiez et ciblez vos profils clients idéaux parmi 30 personas répartis en 4 familles.</p>
            <div class="card-tags">
                <span class="tag">30 personas</span>
                <span class="tag">4 familles</span>
            </div>
            <a href="#" class="card-action"><i class="fas fa-search"></i> Explorer</a>
        </div>

        <div class="card" style="--card-accent:#27ae60; --card-icon-bg:#eafaf1;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-briefcase"></i></div>
                <h3 class="card-title">Offre conseiller</h3>
            </div>
            <p class="card-description">Construisez votre offre différenciante en exclusivité sur votre territoire.</p>
            <div class="card-tags">
                <span class="tag">Exclusivité</span>
                <span class="tag">Valeur ajoutée</span>
            </div>
            <a href="#" class="card-action"><i class="fas fa-cog"></i> Configurer</a>
        </div>

        <div class="card" style="--card-accent:#8e44ad; --card-icon-bg:#f5eef8;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-map-marked-alt"></i></div>
                <h3 class="card-title">Zone de prospection</h3>
            </div>
            <p class="card-description">Délimitez et maîtrisez votre territoire de prospection pour maximiser votre impact.</p>
            <div class="card-tags">
                <span class="tag">Cartographie</span>
                <span class="tag">Segments</span>
            </div>
            <a href="#" class="card-action"><i class="fas fa-map-marker-alt"></i> Délimiter</a>
        </div>

    </div>
    <?php
}

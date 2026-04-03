<?php
$pageTitle = "Assistant IA";
$pageDescription = "Votre assistant intelligent pour l'immobilier";

require_once '../../admin/views/layout.php';

function renderContent() {
    ?>
    <div class="page-header">
        <div class="breadcrumb"><a href="/admin/">Accueil</a> &rsaquo; Assistant IA</div>
        <h1><i class="fas fa-robot page-icon"></i> HUB <span class="page-title-accent">Assistant IA</span></h1>
        <p>Votre assistant intelligent pour l'immobilier</p>
    </div>
    <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" class="search-input" placeholder="Rechercher…">
    </div>
    <div class="cards-container">

        <div class="card" style="--card-accent:#8e44ad; --card-icon-bg:#f5eef8;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-pen-fancy"></i></div>
                <h3 class="card-title">Rédaction d'annonces</h3>
            </div>
            <p class="card-description">Générez des annonces immobilières percutantes en quelques secondes.</p>
            <div class="card-tags"><span class="tag">GPT-4</span><span class="tag">Annonces</span></div>
            <a href="#" class="card-action"><i class="fas fa-play"></i> Générer</a>
        </div>

        <div class="card" style="--card-accent:#3498db; --card-icon-bg:#e3f2fd;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-house-circle-check"></i></div>
                <h3 class="card-title">Estimation IA</h3>
            </div>
            <p class="card-description">Estimez un bien en quelques secondes avec l'IA et les données du marché local.</p>
            <div class="card-tags"><span class="tag">Estimation</span><span class="tag">Marché local</span></div>
            <a href="#" class="card-action"><i class="fas fa-play"></i> Estimer</a>
        </div>

        <div class="card" style="--card-accent:#27ae60; --card-icon-bg:#eafaf1;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-comments"></i></div>
                <h3 class="card-title">Scripts de vente</h3>
            </div>
            <p class="card-description">Scripts personnalisés pour vos prises de contact et rendez-vous vendeurs.</p>
            <div class="card-tags"><span class="tag">Scripts</span><span class="tag">Objections</span></div>
            <a href="#" class="card-action"><i class="fas fa-play"></i> Générer</a>
        </div>

        <div class="card" style="--card-accent:#e67e22; --card-icon-bg:#fef5e7;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-wand-magic-sparkles"></i></div>
                <h3 class="card-title">Contenus réseaux sociaux</h3>
            </div>
            <p class="card-description">Posts, stories et carrousels générés par IA pour vos réseaux.</p>
            <div class="card-tags"><span class="tag">Social</span><span class="tag">Contenu</span></div>
            <a href="#" class="card-action"><i class="fas fa-play"></i> Créer</a>
        </div>

    </div>
    <?php
}

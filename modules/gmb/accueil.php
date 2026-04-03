<?php
$pageTitle = "Google My Business";
$pageDescription = "Gérez votre fiche Google et vos avis clients";


function renderContent() {
    ?>
    <div class="page-header">
        <div class="breadcrumb"><a href="/admin/">Accueil</a> &rsaquo; Google My Business</div>
        <h1><i class="fab fa-google page-icon"></i> HUB <span class="page-title-accent">Google My Business</span></h1>
        <p>Gérez votre fiche Google et vos avis clients</p>
    </div>
    <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" class="search-input" placeholder="Rechercher…">
    </div>
    <div class="cards-container">

        <div class="card" style="--card-accent:#4285f4; --card-icon-bg:#e8f0fe;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-store"></i></div>
                <h3 class="card-title">Ma fiche GMB</h3>
            </div>
            <p class="card-description">Consultez et mettez à jour votre fiche Google My Business.</p>
            <div class="card-tags"><span class="tag">Fiche locale</span><span class="tag">NAP</span></div>
            <a href="#" class="card-action"><i class="fas fa-arrow-right"></i> Gérer</a>
        </div>

        <div class="card" style="--card-accent:#f39c12; --card-icon-bg:#fef9e7;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-star"></i></div>
                <h3 class="card-title">Avis clients</h3>
            </div>
            <p class="card-description">Consultez, répondez et analysez vos avis Google.</p>
            <div class="card-tags"><span class="tag">Réponses</span><span class="tag">Analyse</span></div>
            <a href="/admin/gmb/reviews.php" class="card-action"><i class="fas fa-arrow-right"></i> Consulter</a>
        </div>

        <div class="card" style="--card-accent:#27ae60; --card-icon-bg:#eafaf1;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-envelope-circle-check"></i></div>
                <h3 class="card-title">Demande d'avis</h3>
            </div>
            <p class="card-description">Envoyez des demandes d'avis automatiques à vos clients après une vente.</p>
            <div class="card-tags"><span class="tag">Automation</span><span class="tag">Email/SMS</span></div>
            <a href="#" class="card-action"><i class="fas fa-play"></i> Configurer</a>
        </div>

        <div class="card" style="--card-accent:#3498db; --card-icon-bg:#e3f2fd;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-chart-line"></i></div>
                <h3 class="card-title">Statistiques GMB</h3>
            </div>
            <p class="card-description">Suivez vos impressions, clics et appels générés par votre fiche Google.</p>
            <div class="card-tags"><span class="tag">Impressions</span><span class="tag">Appels</span></div>
            <a href="#" class="card-action"><i class="fas fa-arrow-right"></i> Analyser</a>
        </div>

    </div>
    <?php
}

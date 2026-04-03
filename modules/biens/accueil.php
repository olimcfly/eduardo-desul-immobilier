<?php
$pageTitle = "Biens";
$pageDescription = "Gérez votre portefeuille de biens immobiliers";


function renderContent() {
    ?>
    <div class="page-header">
        <h1><i class="fas fa-house page-icon"></i> HUB <span class="page-title-accent">Biens</span></h1>
        <p>Gérez votre portefeuille de biens immobiliers</p>
    </div>

    <div class="cards-container">

        <div class="card" style="--card-accent:#3498db; --card-icon-bg:#e3f2fd;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-list"></i></div>
                <h3 class="card-title">Catalogue des biens</h3>
            </div>
            <p class="card-description">Consultez et gérez tous vos biens actifs, en option et vendus.</p>
            <div class="card-tags"><span class="tag">Actifs</span><span class="tag">En option</span><span class="tag">Vendus</span></div>
            <a href="/admin/biens/catalogue" class="card-action"><i class="fas fa-arrow-right"></i> Consulter</a>
        </div>

        <div class="card" style="--card-accent:#27ae60; --card-icon-bg:#eafaf1;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-plus-circle"></i></div>
                <h3 class="card-title">Ajouter un bien</h3>
            </div>
            <p class="card-description">Créez une nouvelle fiche bien avec photos, description et caractéristiques.</p>
            <div class="card-tags"><span class="tag">Nouveau mandat</span></div>
            <a href="/admin/biens/nouveau" class="card-action"><i class="fas fa-plus"></i> Créer</a>
        </div>

        <div class="card" style="--card-accent:#f39c12; --card-icon-bg:#fef9e7;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-images"></i></div>
                <h3 class="card-title">Médias & photos</h3>
            </div>
            <p class="card-description">Gérez les photos, plans et vidéos de vos biens.</p>
            <div class="card-tags"><span class="tag">Photos</span><span class="tag">Plans</span><span class="tag">Vidéos</span></div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

        <div class="card" style="--card-accent:#e74c3c; --card-icon-bg:#fdedec;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-tags"></i></div>
                <h3 class="card-title">Diffusion annonces</h3>
            </div>
            <p class="card-description">Publiez vos biens sur les portails immobiliers en un clic.</p>
            <div class="card-tags"><span class="tag">SeLoger</span><span class="tag">LeBonCoin</span></div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

    </div>
    <?php
}

<?php
$pageTitle = "Social";
$pageDescription = "Gérez vos publications et réseaux sociaux";

require_once '../../admin/views/layout.php';

function renderContent() {
    ?>
    <div class="page-header">
        <div class="breadcrumb"><a href="/admin/">Accueil</a> &rsaquo; Social</div>
        <h1><i class="fas fa-share-nodes page-icon"></i> HUB <span class="page-title-accent">Social</span></h1>
        <p>Gérez vos publications et réseaux sociaux</p>
    </div>
    <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" class="search-input" placeholder="Rechercher une publication…">
    </div>
    <div class="cards-container">

        <div class="card" style="--card-accent:#3b5998; --card-icon-bg:#eaf0fb;">
            <div class="card-header">
                <div class="card-icon"><i class="fab fa-facebook-f"></i></div>
                <h3 class="card-title">Facebook</h3>
            </div>
            <p class="card-description">Planifiez et publiez vos posts sur votre page Facebook professionnelle.</p>
            <div class="card-tags"><span class="tag">Posts</span><span class="tag">Reels</span></div>
            <a href="#" class="card-action"><i class="fas fa-arrow-right"></i> Gérer</a>
        </div>

        <div class="card" style="--card-accent:#e1306c; --card-icon-bg:#fde8f0;">
            <div class="card-header">
                <div class="card-icon"><i class="fab fa-instagram"></i></div>
                <h3 class="card-title">Instagram</h3>
            </div>
            <p class="card-description">Partagez vos biens et votre expertise sur Instagram.</p>
            <div class="card-tags"><span class="tag">Stories</span><span class="tag">Carrousels</span></div>
            <a href="#" class="card-action"><i class="fas fa-arrow-right"></i> Gérer</a>
        </div>

        <div class="card" style="--card-accent:#0077b5; --card-icon-bg:#e8f4fd;">
            <div class="card-header">
                <div class="card-icon"><i class="fab fa-linkedin-in"></i></div>
                <h3 class="card-title">LinkedIn</h3>
            </div>
            <p class="card-description">Développez votre réseau professionnel et votre personal branding.</p>
            <div class="card-tags"><span class="tag">Articles</span><span class="tag">Réseau</span></div>
            <a href="#" class="card-action"><i class="fas fa-arrow-right"></i> Gérer</a>
        </div>

        <div class="card" style="--card-accent:#f39c12; --card-icon-bg:#fef9e7;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-calendar-days"></i></div>
                <h3 class="card-title">Calendrier éditorial</h3>
            </div>
            <p class="card-description">Planifiez vos publications sur tous les réseaux depuis un seul endroit.</p>
            <div class="card-tags"><span class="tag">Planning</span><span class="tag">Multi-réseau</span></div>
            <a href="/admin/social/sequences.php" class="card-action"><i class="fas fa-arrow-right"></i> Planifier</a>
        </div>

    </div>
    <?php
}

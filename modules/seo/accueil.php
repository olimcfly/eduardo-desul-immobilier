<?php
$pageTitle = "SEO";
$pageDescription = "Optimisez votre positionnement sur Google";


function renderContent() {
    ?>
    <div class="page-header">
        <div class="breadcrumb"><a href="/admin/">Accueil</a> &rsaquo; SEO</div>
        <h1><i class="fas fa-magnifying-glass-chart page-icon"></i> HUB <span class="page-title-accent">SEO</span></h1>
        <p>Optimisez votre positionnement sur Google</p>
    </div>
    <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" class="search-input" placeholder="Rechercher un mot-clé…">
    </div>
    <div class="cards-container">

        <div class="card" style="--card-accent:#3498db; --card-icon-bg:#e3f2fd;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-key"></i></div>
                <h3 class="card-title">Mots-clés</h3>
            </div>
            <p class="card-description">Suivez le positionnement de vos mots-clés cibles sur Google.</p>
            <div class="card-tags"><span class="tag">Top 10</span><span class="tag">Positions</span></div>
            <a href="/admin/seo/keywords.php" class="card-action"><i class="fas fa-arrow-right"></i> Consulter</a>
        </div>

        <div class="card" style="--card-accent:#27ae60; --card-icon-bg:#eafaf1;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-map-pin"></i></div>
                <h3 class="card-title">Fiches villes</h3>
            </div>
            <p class="card-description">Pages optimisées pour chaque commune de votre territoire.</p>
            <div class="card-tags"><span class="tag">Local SEO</span><span class="tag">Communes</span></div>
            <a href="#" class="card-action"><i class="fas fa-arrow-right"></i> Gérer</a>
        </div>

        <div class="card" style="--card-accent:#e74c3c; --card-icon-bg:#fdedec;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-sitemap"></i></div>
                <h3 class="card-title">Sitemap</h3>
            </div>
            <p class="card-description">Générez et soumettez votre sitemap à Google Search Console.</p>
            <div class="card-tags"><span class="tag">Indexation</span><span class="tag">GSC</span></div>
            <a href="/admin/seo/sitemap.php" class="card-action"><i class="fas fa-arrow-right"></i> Gérer</a>
        </div>

        <div class="card" style="--card-accent:#f39c12; --card-icon-bg:#fef9e7;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-gauge-high"></i></div>
                <h3 class="card-title">Performance technique</h3>
            </div>
            <p class="card-description">Vitesse, Core Web Vitals et audit technique de votre site.</p>
            <div class="card-tags"><span class="tag">Core Web Vitals</span><span class="tag">Vitesse</span></div>
            <a href="#" class="card-action"><i class="fas fa-arrow-right"></i> Auditer</a>
        </div>

    </div>
    <?php
}

<?php
$pageTitle = "Attirer";
$pageDescription = "Générez des vendeurs qualifiés sur votre territoire";


function renderContent() {
    ?>
    <div class="page-header">
        <h1><i class="fas fa-bullseye page-icon"></i> HUB <span class="page-title-accent">Attirer</span></h1>
        <p>Générez des vendeurs qualifiés sur votre territoire</p>
    </div>

    <div class="cards-container">

        <div class="card" style="--card-accent:#1abc9c; --card-icon-bg:#e8f8f5;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-magnifying-glass"></i></div>
                <h3 class="card-title">SEO local</h3>
            </div>
            <p class="card-description">Gagnez en visibilité organique sur votre territoire grâce aux fiches villes et aux mots-clés ciblés.</p>
            <div class="card-tags">
                <span class="tag">GMB</span>
                <span class="tag">Mots-clés</span>
                <span class="tag">Fiche ville</span>
            </div>
            <a href="?module=seo" class="card-action"><i class="fas fa-arrow-right"></i> Accéder</a>
        </div>

        <div class="card" style="--card-accent:#e74c3c; --card-icon-bg:#fdedec;">
            <div class="card-header">
                <div class="card-icon"><i class="fab fa-google"></i></div>
                <h3 class="card-title">Google Ads</h3>
            </div>
            <p class="card-description">Créez des campagnes payantes ciblées avec le wizard 5 étapes assisté par IA.</p>
            <div class="card-tags">
                <span class="tag">Wizard 5 étapes</span>
                <span class="tag">Perplexity IA</span>
            </div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

        <div class="card" style="--card-accent:#3b5998; --card-icon-bg:#eaf0fb;">
            <div class="card-header">
                <div class="card-icon"><i class="fab fa-facebook-f"></i></div>
                <h3 class="card-title">Facebook Ads</h3>
            </div>
            <p class="card-description">Diffusez des publicités sociales ciblées sur votre zone de chalandise.</p>
            <div class="card-tags">
                <span class="tag">Ciblage local</span>
                <span class="tag">Lookalike</span>
            </div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

        <div class="card" style="--card-accent:#f39c12; --card-icon-bg:#fef9e7;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-pen-nib"></i></div>
                <h3 class="card-title">Contenu & articles</h3>
            </div>
            <p class="card-description">Développez votre autorité locale avec du contenu de blog assisté par IA.</p>
            <div class="card-tags">
                <span class="tag">IA assistée</span>
                <span class="tag">Blog</span>
                <span class="tag">Autorité</span>
            </div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

    </div>
    <?php
}

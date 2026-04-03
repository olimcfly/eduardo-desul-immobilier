<?php
$pageTitle = "Capturer";
$pageDescription = "Transformez vos visiteurs en contacts qualifiés";

require_once '../../admin/views/layout.php';

function renderContent() {
    ?>
    <div class="page-header">
        <h1><i class="fas fa-inbox page-icon"></i> HUB <span class="page-title-accent">Capturer</span></h1>
        <p>Transformez vos visiteurs en contacts qualifiés</p>
    </div>

    <div class="cards-container">

        <div class="card" style="--card-accent:#3498db; --card-icon-bg:#e3f2fd;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-globe"></i></div>
                <h3 class="card-title">Site vitrine</h3>
            </div>
            <p class="card-description">Votre site web optimisé pour la capture de leads vendeurs et acquéreurs.</p>
            <div class="card-tags"><span class="tag">Landing page</span><span class="tag">SEO</span></div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

        <div class="card" style="--card-accent:#e74c3c; --card-icon-bg:#fdedec;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-calculator"></i></div>
                <h3 class="card-title">Estimateur en ligne</h3>
            </div>
            <p class="card-description">Outil d'estimation immobilière pour attirer des vendeurs potentiels.</p>
            <div class="card-tags"><span class="tag">IA assistée</span><span class="tag">Formulaire</span></div>
            <a href="?module=capture" class="card-action"><i class="fas fa-arrow-right"></i> Accéder</a>
        </div>

        <div class="card" style="--card-accent:#f39c12; --card-icon-bg:#fef9e7;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-envelope-open-text"></i></div>
                <h3 class="card-title">Email marketing</h3>
            </div>
            <p class="card-description">Séquences d'emails automatiques pour nurturer vos contacts.</p>
            <div class="card-tags"><span class="tag">Automation</span><span class="tag">Séquences</span></div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

        <div class="card" style="--card-accent:#27ae60; --card-icon-bg:#eafaf1;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-comment-dots"></i></div>
                <h3 class="card-title">Chatbot & formulaires</h3>
            </div>
            <p class="card-description">Capturez les leads 24h/24 avec des formulaires intelligents.</p>
            <div class="card-tags"><span class="tag">Chatbot</span><span class="tag">Formulaires</span></div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

    </div>
    <?php
}

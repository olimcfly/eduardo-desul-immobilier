<?php
$pageTitle = "Capture";
$pageDescription = "Pages et formulaires de capture de leads";


function renderContent() {
    ?>
    <div class="page-header">
        <h1><i class="fas fa-magnet page-icon"></i> HUB <span class="page-title-accent">Capture</span></h1>
        <p>Pages et formulaires de capture de leads</p>
    </div>

    <div class="cards-container">

        <div class="card" style="--card-accent:#3498db; --card-icon-bg:#e3f2fd;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-file-alt"></i></div>
                <h3 class="card-title">Formulaires de capture</h3>
            </div>
            <p class="card-description">Créez et gérez vos formulaires de capture de contacts vendeurs et acquéreurs.</p>
            <div class="card-tags"><span class="tag">Formulaires</span><span class="tag">Leads</span></div>
            <a href="/capture/form.php" class="card-action"><i class="fas fa-arrow-right"></i> Accéder</a>
        </div>

        <div class="card" style="--card-accent:#27ae60; --card-icon-bg:#eafaf1;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-hand-pointer"></i></div>
                <h3 class="card-title">Landing pages</h3>
            </div>
            <p class="card-description">Pages d'atterrissage optimisées pour la conversion de vos campagnes.</p>
            <div class="card-tags"><span class="tag">Landing</span><span class="tag">Conversion</span></div>
            <a href="/capture/" class="card-action"><i class="fas fa-arrow-right"></i> Voir</a>
        </div>

        <div class="card" style="--card-accent:#e74c3c; --card-icon-bg:#fdedec;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-circle-check"></i></div>
                <h3 class="card-title">Page de confirmation</h3>
            </div>
            <p class="card-description">Personnalisez la page de remerciement après soumission d'un formulaire.</p>
            <div class="card-tags"><span class="tag">Merci</span><span class="tag">Suivi</span></div>
            <a href="/capture/merci.php" class="card-action"><i class="fas fa-eye"></i> Aperçu</a>
        </div>

        <div class="card" style="--card-accent:#f39c12; --card-icon-bg:#fef9e7;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-chart-pie"></i></div>
                <h3 class="card-title">Statistiques de capture</h3>
            </div>
            <p class="card-description">Taux de conversion, sources de trafic et performance des formulaires.</p>
            <div class="card-tags"><span class="tag">Taux de conv.</span><span class="tag">Sources</span></div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

    </div>
    <?php
}

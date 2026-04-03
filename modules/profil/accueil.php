<?php
$pageTitle = "Mon profil";
$pageDescription = "Gérez votre identité de conseiller";


function renderContent() {
    $user = Auth::user();
    ?>
    <div class="page-header">
        <h1><i class="fas fa-user page-icon"></i> <span class="page-title-accent">Mon profil</span></h1>
        <p>Gérez votre identité de conseiller</p>
    </div>

    <div class="cards-container">

        <div class="card" style="--card-accent:#3498db; --card-icon-bg:#e3f2fd;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-id-card"></i></div>
                <h3 class="card-title">Identité</h3>
            </div>
            <p class="card-description">
                <strong><?= htmlspecialchars($user['name'] ?? 'Nom non renseigné') ?></strong><br>
                <?= htmlspecialchars($user['email'] ?? '') ?>
            </p>
            <div class="card-tags"><span class="tag">Conseiller</span></div>
            <a href="?module=parametres" class="card-action"><i class="fas fa-pencil"></i> Modifier</a>
        </div>

        <div class="card" style="--card-accent:#27ae60; --card-icon-bg:#eafaf1;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-chart-simple"></i></div>
                <h3 class="card-title">Mes performances</h3>
            </div>
            <p class="card-description">Résumé de votre activité : leads générés, mandats signés, avis reçus.</p>
            <div class="card-tags"><span class="tag">Stats</span><span class="tag">Objectifs</span></div>
            <a href="?module=optimiser" class="card-action"><i class="fas fa-arrow-right"></i> Voir</a>
        </div>

        <div class="card" style="--card-accent:#e74c3c; --card-icon-bg:#fdedec;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-lock"></i></div>
                <h3 class="card-title">Sécurité du compte</h3>
            </div>
            <p class="card-description">Modifiez votre mot de passe et activez la double authentification.</p>
            <div class="card-tags"><span class="tag">Mot de passe</span><span class="tag">2FA</span></div>
            <a href="?module=parametres" class="card-action"><i class="fas fa-arrow-right"></i> Configurer</a>
        </div>

    </div>
    <?php
}

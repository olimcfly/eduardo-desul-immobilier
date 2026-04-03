<?php
$pageTitle = "Paramètres";
$pageDescription = "Configurez votre compte et vos préférences";


function renderContent() {
    ?>
    <div class="page-header">
        <div class="breadcrumb"><a href="/admin/">Accueil</a> &rsaquo; Paramètres</div>
        <h1><i class="fas fa-gear page-icon"></i> <span class="page-title-accent">Paramètres</span></h1>
        <p>Configurez votre compte et vos préférences</p>
    </div>
    <div class="cards-container">

        <div class="card" style="--card-accent:#3498db; --card-icon-bg:#e3f2fd;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-user-gear"></i></div>
                <h3 class="card-title">Informations personnelles</h3>
            </div>
            <p class="card-description">Nom, email, téléphone, photo de profil et carte professionnelle.</p>
            <div class="card-tags"><span class="tag">Profil</span><span class="tag">Identité</span></div>
            <a href="#" class="card-action"><i class="fas fa-arrow-right"></i> Modifier</a>
        </div>

        <div class="card" style="--card-accent:#e74c3c; --card-icon-bg:#fdedec;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-lock"></i></div>
                <h3 class="card-title">Sécurité</h3>
            </div>
            <p class="card-description">Mot de passe, double authentification et sessions actives.</p>
            <div class="card-tags"><span class="tag">Mot de passe</span><span class="tag">2FA</span></div>
            <a href="#" class="card-action"><i class="fas fa-arrow-right"></i> Configurer</a>
        </div>

        <div class="card" style="--card-accent:#f39c12; --card-icon-bg:#fef9e7;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-bell"></i></div>
                <h3 class="card-title">Notifications</h3>
            </div>
            <p class="card-description">Gérez vos préférences de notifications email et SMS.</p>
            <div class="card-tags"><span class="tag">Email</span><span class="tag">SMS</span></div>
            <a href="#" class="card-action"><i class="fas fa-arrow-right"></i> Configurer</a>
        </div>

        <div class="card" style="--card-accent:#27ae60; --card-icon-bg:#eafaf1;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-plug"></i></div>
                <h3 class="card-title">Intégrations</h3>
            </div>
            <p class="card-description">Connectez vos outils : Google, Facebook, CRM, portails immobiliers.</p>
            <div class="card-tags"><span class="tag">API</span><span class="tag">Connexions</span></div>
            <a href="#" class="card-action"><i class="fas fa-arrow-right"></i> Gérer</a>
        </div>

    </div>
    <?php
}

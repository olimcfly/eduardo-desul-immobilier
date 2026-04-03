<?php
$pageTitle = "Convertir";
$pageDescription = "Transformez vos contacts en clients signés";

require_once '../../admin/views/layout.php';

function renderContent() {
    ?>
    <div class="page-header">
        <h1><i class="fas fa-arrow-trend-up page-icon"></i> HUB <span class="page-title-accent">Convertir</span></h1>
        <p>Transformez vos contacts en clients signés</p>
    </div>

    <div class="cards-container">

        <div class="card" style="--card-accent:#3498db; --card-icon-bg:#e3f2fd;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-address-book"></i></div>
                <h3 class="card-title">CRM Contacts</h3>
            </div>
            <p class="card-description">Gérez et qualifiez vos leads dans un pipeline structuré.</p>
            <div class="card-tags"><span class="tag">Pipeline</span><span class="tag">Qualification</span></div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

        <div class="card" style="--card-accent:#e74c3c; --card-icon-bg:#fdedec;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-calendar-check"></i></div>
                <h3 class="card-title">Prise de RDV</h3>
            </div>
            <p class="card-description">Automatisez la prise de rendez-vous vendeurs avec un agenda en ligne.</p>
            <div class="card-tags"><span class="tag">Agenda</span><span class="tag">Automation</span></div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

        <div class="card" style="--card-accent:#27ae60; --card-icon-bg:#eafaf1;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-file-signature"></i></div>
                <h3 class="card-title">Argumentaire mandat</h3>
            </div>
            <p class="card-description">Scripts et supports pour transformer un RDV en mandat exclusif.</p>
            <div class="card-tags"><span class="tag">Script</span><span class="tag">Exclusivité</span></div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

        <div class="card" style="--card-accent:#8e44ad; --card-icon-bg:#f5eef8;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-handshake"></i></div>
                <h3 class="card-title">Suivi post-RDV</h3>
            </div>
            <p class="card-description">Relances automatiques et séquences de nurturing après le premier contact.</p>
            <div class="card-tags"><span class="tag">Relance</span><span class="tag">Nurturing</span></div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

    </div>
    <?php
}

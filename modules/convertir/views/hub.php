<?php

declare(strict_types=1);

$current = 'hub';
require __DIR__ . '/_subnav.php';
?>
<div class="page-header">
    <h1><i class="fas fa-handshake page-icon"></i> HUB <span class="page-title-accent">Convertir</span></h1>
    <p>Transformez vos leads en rendez-vous puis en mandats signés.</p>
</div>

<div class="cards-container">
    <a class="card" href="/admin?module=convertir&action=parcours" style="--card-accent:#c9a84c; --card-icon-bg:#fef9e7; text-decoration:none; color:inherit; display:block;">
        <div class="card-header">
            <div class="card-icon"><i class="fas fa-route"></i></div>
            <h3 class="card-title">Parcours de conversion</h3>
        </div>
        <p class="card-description">Méthode en 5 étapes : qualification, script, objections, RDV et signature.</p>
        <div class="card-tags"><span class="tag">Process</span><span class="tag">Méthode</span></div>
        <span class="card-action"><i class="fas fa-arrow-right"></i> Ouvrir le parcours</span>
    </a>

    <a class="card" href="/admin?module=convertir&action=rdv" style="--card-accent:#3b82f6; --card-icon-bg:#e3f2fd; text-decoration:none; color:inherit; display:block;">
        <div class="card-header">
            <div class="card-icon"><i class="fas fa-calendar-days"></i></div>
            <h3 class="card-title">Prise de RDV</h3>
        </div>
        <p class="card-description">Vue agenda des leads en phase RDV avec actions de confirmation et replanification.</p>
        <div class="card-tags"><span class="tag">CRM</span><span class="tag">Agenda</span></div>
        <span class="card-action"><i class="fas fa-arrow-right"></i> Ouvrir l'agenda RDV</span>
    </a>

    <a class="card" href="/admin?module=convertir&action=suivi-post-rdv" style="--card-accent:#10b981; --card-icon-bg:#ecfdf5; text-decoration:none; color:inherit; display:block;">
        <div class="card-header">
            <div class="card-icon"><i class="fas fa-reply"></i></div>
            <h3 class="card-title">Suivi post-RDV</h3>
        </div>
        <p class="card-description">Séquence de relance structurée après un rendez-vous pour augmenter la signature.</p>
        <div class="card-tags"><span class="tag">Relance</span><span class="tag">Conversion</span></div>
        <span class="card-action"><i class="fas fa-arrow-right"></i> Ouvrir le suivi</span>
    </a>
</div>

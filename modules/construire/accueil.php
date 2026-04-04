<?php
$allowedActions = ['index', 'ancre', 'profils', 'offre', 'zone', 'synthese', 'actions'];
$action = isset($_GET['action']) ? preg_replace('/[^a-z_-]/', '', (string)$_GET['action']) : 'index';
if (!in_array($action, $allowedActions, true)) $action = 'index';

$actionTitles = [
    'ancre'    => 'Méthode ANCRE+ — Positionnement',
    'profils'  => 'NeuroPersona — Profils Clients',
    'offre'    => 'Offre Conseiller — Formulation',
    'zone'     => 'Zone de Prospection',
    'synthese' => 'Synthèse Stratégique',
    'actions'  => 'Actions du Jour',
];

$pageTitle       = $action === 'index' ? 'Construire' : ($actionTitles[$action] ?? 'Construire');
$pageDescription = 'Posez les bases solides de votre activité';

function renderContent()
{
    global $action;

    if ($action !== 'index') {
        $file = __DIR__ . '/' . $action . '.php';
        if (is_file($file)) {
            include $file;
            return;
        }
    }
    ?>
    <link rel="stylesheet" href="/admin/assets/css/seo.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'] . '/admin/assets/css/seo.css') ?>">

    <div class="seo-hub">
        <div class="seo-breadcrumb">Accueil › Construire</div>
        <h1>🧱 HUB Construire</h1>
        <p class="seo-subtitle">
            Posez les bases solides de votre activité : positionnement, personas, offre, zone et plan d'action.
        </p>

        <div class="seo-search-wrap">
            <input type="text"
                   id="construire-module-search"
                   placeholder="Rechercher un module Construire…"
                   oninput="filterConstruireModules(this.value)">
        </div>

        <div class="seo-grid" id="construire-modules-grid">
            <article class="seo-card"
                     data-module="méthode ancre positionnement accroche"
                     style="--accent:#3b82f6;--icon-bg:#dbeafe;">
                <div class="seo-card-head"><span class="icon">⚓</span><h3>Méthode ANCRE+</h3></div>
                <p>Générez 3 formulations d'accroche claires pour affirmer votre positionnement.</p>
                <div class="badges"><span>Positionnement</span><span>Message</span></div>
                <a href="/admin?module=construire&amp;action=ancre" class="btn btn-sm">Lancer</a>
                <small>Clarifiez votre promesse en moins de 10 minutes.</small>
            </article>

            <article class="seo-card"
                     data-module="neuro persona profils clients cibles"
                     style="--accent:#10b981;--icon-bg:#d1fae5;">
                <div class="seo-card-head"><span class="icon">🧠</span><h3>NeuroPersona</h3></div>
                <p>Identifiez vos 3 profils clients prioritaires pour mieux cibler vos actions.</p>
                <div class="badges"><span>Clients</span><span>Ciblage</span></div>
                <a href="/admin?module=construire&amp;action=profils" class="btn btn-sm">Définir</a>
                <small>Passez d'une communication générique à une approche précise.</small>
            </article>

            <article class="seo-card"
                     data-module="offre conseiller pitch proposition valeur"
                     style="--accent:#ef4444;--icon-bg:#fee2e2;">
                <div class="seo-card-head"><span class="icon">💼</span><h3>Offre Conseiller</h3></div>
                <p>Construisez votre pitch en 3 versions : courte, intermédiaire et détaillée.</p>
                <div class="badges"><span>Pitch</span><span>Valeur</span></div>
                <a href="/admin?module=construire&amp;action=offre" class="btn btn-sm">Construire</a>
                <small>Une offre lisible augmente vos prises de rendez-vous.</small>
            </article>

            <article class="seo-card"
                     data-module="zone prospection territoire local"
                     style="--accent:#f59e0b;--icon-bg:#fef3c7;">
                <div class="seo-card-head"><span class="icon">🗺️</span><h3>Zone de Prospection</h3></div>
                <p>Délimitez votre territoire en 3 niveaux pour prioriser vos efforts terrain.</p>
                <div class="badges"><span>Territoire</span><span>Priorités</span></div>
                <a href="/admin?module=construire&amp;action=zone" class="btn btn-sm">Cartographier</a>
                <small>Concentrez votre énergie sur les zones à plus fort potentiel.</small>
            </article>

            <article class="seo-card"
                     data-module="synthèse stratégique plan global"
                     style="--accent:#8b5cf6;--icon-bg:#ede9fe;">
                <div class="seo-card-head"><span class="icon">🧩</span><h3>Synthèse Stratégique</h3></div>
                <p>Obtenez une vue d'ensemble claire de votre positionnement et de vos axes de progression.</p>
                <div class="badges"><span>Vision</span><span>Stratégie</span></div>
                <a href="/admin?module=construire&amp;action=synthese" class="btn btn-sm">Synthétiser</a>
                <small>Un résumé actionnable pour piloter vos décisions.</small>
            </article>

            <article class="seo-card"
                     data-module="actions du jour to-do priorités"
                     style="--accent:#14b8a6;--icon-bg:#ccfbf1;">
                <div class="seo-card-head"><span class="icon">⚡</span><h3>Actions du Jour</h3></div>
                <p>Transformez votre stratégie en 3 à 5 actions concrètes à exécuter aujourd'hui.</p>
                <div class="badges"><span>Exécution</span><span>Productivité</span></div>
                <a href="/admin?module=construire&amp;action=actions" class="btn btn-sm">Planifier</a>
                <small>Passez rapidement de l'idée à la mise en œuvre.</small>
            </article>
        </div>
    </div>

    <script>
        function filterConstruireModules(query) {
            var q = (query || '').toLowerCase();
            var cards = document.querySelectorAll('#construire-modules-grid .seo-card');
            cards.forEach(function (card) {
                var text = (card.dataset.module || '').toLowerCase();
                card.style.display = text.includes(q) ? '' : 'none';
            });
        }
    </script>
    <?php
}

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

require_once '../../admin/views/layout.php';
require_once '../../core/services/AiService.php';

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
    <div class="page-header">
        <h1><i class="fas fa-layer-group page-icon"></i> HUB <span class="page-title-accent">Construire</span></h1>
        <p>Posez les bases solides de votre activité avec Noah IA</p>
    </div>

    <div class="cards-container">

        <div class="card" style="--card-accent:#e74c3c; --card-icon-bg:#fdedec;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-anchor"></i></div>
                <h3 class="card-title">Méthode ANCRE+</h3>
            </div>
            <p class="card-description">Générez 3 formulations d'accroche claires pour votre positionnement conseiller.</p>
            <div class="card-tags"><span class="tag">Noah IA</span><span class="tag">Positionnement</span></div>
            <a href="?module=construire&action=ancre" class="card-action"><i class="fas fa-arrow-right"></i> Accéder</a>
        </div>

        <div class="card" style="--card-accent:#3498db; --card-icon-bg:#e3f2fd;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-brain"></i></div>
                <h3 class="card-title">NeuroPersona</h3>
            </div>
            <p class="card-description">Identifiez vos 3 profils clients prioritaires sur votre territoire.</p>
            <div class="card-tags"><span class="tag">Noah IA</span><span class="tag">Profils clients</span></div>
            <a href="?module=construire&action=profils" class="card-action"><i class="fas fa-arrow-right"></i> Accéder</a>
        </div>

        <div class="card" style="--card-accent:#27ae60; --card-icon-bg:#eafaf1;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-briefcase"></i></div>
                <h3 class="card-title">Offre Conseiller</h3>
            </div>
            <p class="card-description">Construisez votre pitch commercial en 3 versions adaptées à vos personas.</p>
            <div class="card-tags"><span class="tag">Noah IA</span><span class="tag">Pitch</span></div>
            <a href="?module=construire&action=offre" class="card-action"><i class="fas fa-arrow-right"></i> Accéder</a>
        </div>

        <div class="card" style="--card-accent:#8e44ad; --card-icon-bg:#f5eef8;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-map-marked-alt"></i></div>
                <h3 class="card-title">Zone de Prospection</h3>
            </div>
            <p class="card-description">Délimitez votre territoire de prospection en 3 niveaux stratégiques.</p>
            <div class="card-tags"><span class="tag">Noah IA</span><span class="tag">Territoire</span></div>
            <a href="?module=construire&action=zone" class="card-action"><i class="fas fa-arrow-right"></i> Accéder</a>
        </div>

        <div class="card" style="--card-accent:#e67e22; --card-icon-bg:#fef5e7;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-layer-group"></i></div>
                <h3 class="card-title">Synthèse Stratégique</h3>
            </div>
            <p class="card-description">Résumez votre situation et votre stratégie en 100 mots percutants.</p>
            <div class="card-tags"><span class="tag">Noah IA</span><span class="tag">Stratégie</span></div>
            <a href="?module=construire&action=synthese" class="card-action"><i class="fas fa-arrow-right"></i> Accéder</a>
        </div>

        <div class="card" style="--card-accent:#16a085; --card-icon-bg:#e8f8f5;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-bolt"></i></div>
                <h3 class="card-title">Actions du Jour</h3>
            </div>
            <p class="card-description">Obtenez 3 à 5 actions concrètes et mesurables pour aujourd'hui.</p>
            <div class="card-tags"><span class="tag">Noah IA</span><span class="tag">Plan d'action</span></div>
            <a href="?module=construire&action=actions" class="card-action"><i class="fas fa-arrow-right"></i> Accéder</a>
        </div>

    </div>
    <?php
}

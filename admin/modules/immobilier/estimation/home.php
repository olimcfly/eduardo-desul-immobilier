<?php
/**
 * Page d'accueil du module Estimations
 * /admin/modules/immobilier/estimation/home.php
 */

if (!defined('ADMIN_ROUTER')) {
    die('Accès non autorisé');
}

require_once ROOT_PATH . '/components/modules/ModuleHomePage.php';

$pageTitle = 'Estimations Immobilières';

$moduleConfig = [
    'title'       => 'Gestion des Estimations',
    'description' => 'Créez, modifiez et suivez vos estimations immobilières en temps réel.',
    'icon'        => 'fa-calculator',
    'quick_access' => [
        [
            'title'       => 'Nouvelle estimation',
            'description' => 'Créez une nouvelle estimation pour un bien immobilier.',
            'icon'        => 'fa-plus-circle',
            'url'         => '?page=estimation-create',
        ],
        [
            'title'       => 'Mes estimations',
            'description' => 'Consultez et modifiez vos estimations existantes.',
            'icon'        => 'fa-list',
            'url'         => '?page=estimation',
        ],
        [
            'title'       => 'Modèles d\'estimation',
            'description' => 'Gérez vos modèles de calcul personnalisés.',
            'icon'        => 'fa-file-alt',
            'url'         => '?page=estimation-templates',
        ],
        [
            'title'       => 'Rapports d\'estimation',
            'description' => 'Téléchargez les rapports au format PDF.',
            'icon'        => 'fa-download',
            'url'         => '?page=estimation-reports',
        ],
    ],
    'future_features' => [
        'Intégration des données de marché en temps réel',
        'Comparaison avec les biens similaires',
        'Export des estimations en PDF/Excel',
        'Historique et tendances des estimations',
        'Partage sécurisé avec les clients',
    ],
];

renderModuleHomePage($moduleConfig);
?>

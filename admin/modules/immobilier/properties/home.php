<?php
/**
 * Page d'accueil du module Biens Immobiliers
 * /admin/modules/immobilier/properties/home.php
 */

if (!defined('ADMIN_ROUTER')) {
    die('Accès non autorisé');
}

require_once ROOT_PATH . '/components/modules/ModuleHomePage.php';

$pageTitle = 'Gestion des Biens';

$moduleConfig = [
    'title'       => 'Gestion des Biens Immobiliers',
    'description' => 'Ajoutez, modifiez et suivez tous vos biens immobiliers en gestion.',
    'icon'        => 'fa-home',
    'quick_access' => [
        [
            'title'       => 'Ajouter un bien',
            'description' => 'Créez une nouvelle fiche pour un bien immobilier.',
            'icon'        => 'fa-plus-circle',
            'url'         => '?page=properties-edit',
        ],
        [
            'title'       => 'Liste des biens',
            'description' => 'Consultez et gérez tous vos biens en portefeuille.',
            'icon'        => 'fa-list',
            'url'         => '?page=properties',
        ],
        [
            'title'       => 'Recherche avancée',
            'description' => 'Filtrez les biens par critères spécifiques.',
            'icon'        => 'fa-search',
            'url'         => '?page=properties-search',
        ],
        [
            'title'       => 'Prise de rendez-vous',
            'description' => 'Planifiez des visites et rendez-vous.',
            'icon'        => 'fa-calendar-alt',
            'url'         => '?page=rdv',
        ],
    ],
    'future_features' => [
        'Galerie photo et vidéo interactive',
        'Plans et mesures des pièces',
        'Suivi des visites et demandes',
        'Export des listes de biens',
        'Intégration portails immobiliers',
    ],
];

renderModuleHomePage($moduleConfig);
?>

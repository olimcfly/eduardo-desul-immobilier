<?php
/**
 * Configuration centralisée des modules IMMO LOCAL+
 * /admin/config/modules-config.php
 *
 * Définit la structure, les descriptions, et les pages d'accueil de chaque module
 */

if (!defined('ADMIN_ROUTER')) {
    return [];
}

$modulesConfig = [
    'dashboard' => [
        'label'       => 'Tableau de bord',
        'description' => 'Vue d\'ensemble de vos activités',
        'icon'        => 'fa-tachometer-alt',
        'permission'  => 'all',
    ],
    'estimation' => [
        'label'       => 'Estimations',
        'description' => 'Créer et gérer des estimations immobilières',
        'icon'        => 'fa-calculator',
        'permission'  => 'view_estimation',
        'home_page'   => '/admin/modules/immobilier/estimation/home.php',
    ],
    'properties' => [
        'label'       => 'Biens',
        'description' => 'Liste des biens en gestion',
        'icon'        => 'fa-home',
        'permission'  => 'view_properties',
        'home_page'   => '/admin/modules/immobilier/properties/home.php',
        'submenu'     => [
            'properties'   => ['label' => 'Liste des biens', 'url' => '?page=properties'],
            'properties-edit' => ['label' => 'Ajouter un bien', 'url' => '?page=properties-edit'],
            'rdv'          => ['label' => 'Rendez-vous', 'url' => '?page=rdv'],
        ],
    ],
    'crm' => [
        'label'       => 'Clients',
        'description' => 'Gestion des propriétaires et locataires',
        'icon'        => 'fa-users',
        'permission'  => 'view_crm',
        'submenu'     => [
            'crm'   => ['label' => 'Clients', 'url' => '?page=crm'],
            'leads' => ['label' => 'Prospects', 'url' => '?page=leads'],
        ],
    ],
    'calendar' => [
        'label'       => 'Agenda',
        'description' => 'Rendez-vous et tâches',
        'icon'        => 'fa-calendar-alt',
        'permission'  => 'view_calendar',
    ],
    'reports' => [
        'label'       => 'Rapports',
        'description' => 'Statistiques et exports',
        'icon'        => 'fa-chart-line',
        'permission'  => 'view_reports',
    ],
    'settings' => [
        'label'       => 'Paramètres',
        'description' => 'Configuration du compte',
        'icon'        => 'fa-cog',
        'permission'  => 'settings',
    ],

    // Autres modules (ancienne structure, à conserver pour compatibilité)
    'marketing' => [
        'label'       => 'Marketing',
        'description' => 'Gestion du marketing et prospects',
        'icon'        => 'fa-bullhorn',
        'permission'  => 'view_marketing',
    ],
    'seo' => [
        'label'       => 'SEO',
        'description' => 'Optimisation SEO',
        'icon'        => 'fa-search',
        'permission'  => 'view_seo',
    ],
    'social' => [
        'label'       => 'Réseaux Sociaux',
        'description' => 'Gestion réseaux sociaux',
        'icon'        => 'fa-share-alt',
        'permission'  => 'view_social',
    ],
];

return $modulesConfig;
?>

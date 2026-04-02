<?php
/**
 * Page d'accueil du module CRM (Clients)
 * /admin/modules/marketing/crm/home.php
 */

if (!defined('ADMIN_ROUTER')) {
    die('Accès non autorisé');
}

require_once ROOT_PATH . '/components/modules/ModuleHomePage.php';

$pageTitle = 'Gestion des Clients';

$moduleConfig = [
    'title'       => 'Gestion des Clients et Prospects',
    'description' => 'Suivez vos propriétaires, locataires, prospects et contacts de manière centralisée.',
    'icon'        => 'fa-users',
    'quick_access' => [
        [
            'title'       => 'Ajouter un client',
            'description' => 'Créez une nouvelle fiche client ou prospect.',
            'icon'        => 'fa-user-plus',
            'url'         => '?page=crm-create',
        ],
        [
            'title'       => 'Liste des clients',
            'description' => 'Gérez tous vos clients et leurs contacts.',
            'icon'        => 'fa-list',
            'url'         => '?page=crm',
        ],
        [
            'title'       => 'Prospects',
            'description' => 'Suivi des prospects en conversion.',
            'icon'        => 'fa-user-tie',
            'url'         => '?page=leads',
        ],
        [
            'title'       => 'Messagerie',
            'description' => 'Communiquez avec vos clients directement.',
            'icon'        => 'fa-comments',
            'url'         => '?page=messagerie',
        ],
    ],
    'future_features' => [
        'Segmentation des clients avancée',
        'Historique complet des interactions',
        'Notes et annotations sur les clients',
        'Templates de communication',
        'Intégration e-mail et SMS',
    ],
];

renderModuleHomePage($moduleConfig);
?>

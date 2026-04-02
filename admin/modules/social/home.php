<?php
/**
 * Page d'accueil du module Réseaux Sociaux
 * /admin/modules/social/home.php
 */

if (!defined('ADMIN_ROUTER')) {
    die('Accès non autorisé');
}

require_once ROOT_PATH . '/components/modules/ModuleHomePage.php';

$pageTitle = 'Réseaux Sociaux';

$moduleConfig = [
    'title'       => 'Gestion Réseaux Sociaux',
    'description' => 'Gérez votre présence sur tous les réseaux sociaux depuis une interface unique.',
    'icon'        => 'fa-share-alt',
    'quick_access' => [
        [
            'title'       => 'Google My Business',
            'description' => 'Optimisez votre fiche Google Business.',
            'icon'        => 'fab fa-google',
            'url'         => '?page=gmb',
        ],
        [
            'title'       => 'TikTok',
            'description' => 'Créez et gérez du contenu TikTok viral.',
            'icon'        => 'fab fa-tiktok',
            'url'         => '?page=tiktok',
        ],
        [
            'title'       => 'Facebook',
            'description' => 'Gérez votre page Facebook et publicités.',
            'icon'        => 'fab fa-facebook',
            'url'         => '?page=facebook',
        ],
        [
            'title'       => 'Calendrier de contenu',
            'description' => 'Planifiez vos publications à l\'avance.',
            'icon'        => 'fa-calendar-check',
            'url'         => '?page=social-calendar',
        ],
    ],
    'future_features' => [
        'Planification de publications',
        'Analyse des performances',
        'Gestion multi-comptes',
        'Suivi des mentions et commentaires',
        'Génération de contenu IA',
    ],
];

renderModuleHomePage($moduleConfig);
?>

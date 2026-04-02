<?php
/**
 * Page d'accueil du module SEO
 * /admin/modules/seo/home.php
 */

if (!defined('ADMIN_ROUTER')) {
    die('Accès non autorisé');
}

require_once ROOT_PATH . '/components/modules/ModuleHomePage.php';

$pageTitle = 'Optimisation SEO';

$moduleConfig = [
    'title'       => 'Optimisation SEO',
    'description' => 'Améliorez votre visibilité sur les moteurs de recherche.',
    'icon'        => 'fa-search',
    'quick_access' => [
        [
            'title'       => 'Audit SEO',
            'description' => 'Analysez les performances SEO de votre site.',
            'icon'        => 'fa-clipboard-check',
            'url'         => '?page=seo-audit',
        ],
        [
            'title'       => 'Keywords',
            'description' => 'Gérez vos mots-clés et leur positionnement.',
            'icon'        => 'fa-key',
            'url'         => '?page=seo-keywords',
        ],
        [
            'title'       => 'Backlinks',
            'description' => 'Suivez vos liens retours et autorité.',
            'icon'        => 'fa-link',
            'url'         => '?page=seo-backlinks',
        ],
        [
            'title'       => 'Pages locales',
            'description' => 'Optimisez vos pages pour la recherche locale.',
            'icon'        => 'fa-map-marker-alt',
            'url'         => '?page=seo-local',
        ],
    ],
    'future_features' => [
        'Optimisation on-page automatique',
        'Suivi du positionnement en temps réel',
        'Analyse des concurrents',
        'Suggestions d\'amélioration SEO',
        'Rapports mensuels détaillés',
    ],
];

renderModuleHomePage($moduleConfig);
?>

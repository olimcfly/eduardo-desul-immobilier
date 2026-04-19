<?php
// config.php

// Configuration par défaut
$defaultConfig = [
    'pageTitle' => 'Titre par défaut',
    'metaDesc' => 'Description par défaut',
    'metaKeywords' => 'mots-clés, par, défaut',
    'extraCss' => ['/public/assets/css/style.css'],
    'extraJs' => []
];

// Configurations spécifiques aux pages
$pageConfigs = [
    'biens' => [
        'pageTitle' => 'Nos biens immobiliers à Bordeaux — Eduardo Desul | Vente & Location',
        'metaDesc' => 'Découvrez notre sélection de biens immobiliers à Bordeaux et dans la métropole bordelaise : appartements, maisons, terrains et biens de prestige.',
        'metaKeywords' => 'biens immobiliers Bordeaux, appartements à vendre Bordeaux, maisons Bordeaux, immobilier Bordeaux Métropole, acheter à Bordeaux, location Bordeaux',
        'extraCss' => ['/public/assets/css/style.css'],
        'extraJs' => []
    ],
    'contact' => [
        'pageTitle' => 'Contactez-nous — Eduardo Desul | Immobilier à Bordeaux',
        'metaDesc' => 'Contactez-nous pour votre projet immobilier à Bordeaux et dans la métropole bordelaise : vente, achat, estimation et accompagnement sur mesure.',
        'metaKeywords' => 'contact immobilier Bordeaux, agent immobilier Bordeaux, Eduardo Desul contact, conseiller immobilier Bordeaux',
        'extraCss' => ['/public/assets/css/style.css'],
        'extraJs' => []
    ],
    'estimation' => [
        'pageTitle' => 'Estimation gratuite à Bordeaux — Eduardo Desul',
        'metaDesc' => 'Obtenez une estimation gratuite de votre bien immobilier à Bordeaux et dans la métropole bordelaise.',
        'metaKeywords' => 'estimation immobilière Bordeaux, estimation gratuite Bordeaux, avis de valeur Bordeaux, Eduardo Desul estimation',
        'extraCss' => ['/public/assets/css/style.css'],
        'extraJs' => []
    ],
    'guide' => [
        'pageTitle' => 'Guide immobilier Bordeaux — Eduardo Desul',
        'metaDesc' => 'Découvrez notre guide immobilier pour mieux vendre, acheter et comprendre le marché à Bordeaux et dans la métropole bordelaise.',
        'metaKeywords' => 'guide immobilier Bordeaux, vendre à Bordeaux, acheter à Bordeaux, marché immobilier Bordeaux, Eduardo Desul guide',
        'extraCss' => ['/public/assets/css/style.css'],
        'extraJs' => []
    ],
    'secteurs' => [
        'pageTitle' => 'Secteurs immobiliers à Bordeaux — Eduardo Desul',
        'metaDesc' => 'Découvrez les secteurs, quartiers et villes autour de Bordeaux pour votre projet immobilier.',
        'metaKeywords' => 'secteurs immobiliers Bordeaux, quartiers Bordeaux, villes autour de Bordeaux, immobilier Bordeaux Métropole',
        'extraCss' => ['/public/assets/css/style.css'],
        'extraJs' => []
    ],
    'home' => [
        'pageTitle' => 'Immobilier à Bordeaux — Eduardo Desul | Vente, Achat, Estimation',
        'metaDesc' => 'Bienvenue sur le site d’Eduardo Desul, votre conseiller immobilier à Bordeaux et dans la métropole bordelaise pour vendre, acheter ou faire estimer votre bien.',
        'metaKeywords' => 'immobilier Bordeaux, conseiller immobilier Bordeaux, vente Bordeaux, achat Bordeaux, estimation Bordeaux, Eduardo Desul',
        'extraCss' => ['/public/assets/css/style.css'],
        'extraJs' => []
    ]
];

function getPageConfig($pageName) {
    global $defaultConfig, $pageConfigs;

    // Retourner la configuration par défaut si la page n'est pas trouvée
    if (!isset($pageConfigs[$pageName])) {
        return $defaultConfig;
    }

    // Fusionner la configuration par défaut avec la configuration spécifique à la page
    return array_merge($defaultConfig, $pageConfigs[$pageName]);
}
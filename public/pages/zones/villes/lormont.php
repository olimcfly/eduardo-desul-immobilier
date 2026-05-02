<?php
$ville = [
    'nom'         => 'Lormont',
    'type'        => 'ville',
    'prix'        => '3 050',
    'tendance'    => '+0,9%',
    'delai'       => '52 jours',
    'image'       => '/assets/images/chartrons.jpg',
    'description' => 'Au pied des coteaux face à Bordeaux, Lormont combine vues panoramiques, quartiers résidentiels et accès rapide au centre-ville.',
    'metaDesc'    => 'Immobilier à Lormont (33310) : estimation et vente avec un conseiller local. Analyse du marché, quartiers et prix sur la rive droite bordelaise.',
    'marche'      => "Lormont attire les acheteurs sensibles au cadre (hauteur de Garonne, espaces verts) tout en conservant des niveaux de prix plus accessibles que Bordeaux centre. Le parc résidentiel est mixte : grands ensembles rénovés, maisons sur les hauteurs et petits collectifs récents. Le marché est relativement fluide sur les biens bien présentés et correctement estimés.",
    'faq'         => [
        ['q' => 'Lormont est-elle une commune familiale ?', 'a' => 'Oui, la ville compte de nombreuses écoles, équipements sportifs et parcs. La clientèle familiale est très présente sur le segment maisons.'],
        ['q' => 'Comment se positionnent les prix par rapport à Bordeaux ?', 'a' => 'En moyenne inférieurs à Bordeaux intramuros, avec des écarts importants selon les secteurs (bas de Lormont / hauteurs).'],
    ],
];
require __DIR__ . '/../_ville-secteur.php';

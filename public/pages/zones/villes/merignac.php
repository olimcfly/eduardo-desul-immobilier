<?php
$ville = [
    'nom'         => 'Mérignac',
    'type'        => 'ville',
    'prix'        => '3 200',
    'tendance'    => '+3,1%',
    'delai'       => '42 jours',
    'image'       => '/assets/images/merignac.jpg',
    'description' => '2e ville de Gironde, Mérignac offre un marché immobilier accessible avec une forte dynamique économique portée par l\'aéroport et ses zones d\'activités.',
    'metaDesc'    => 'Expert immobilier à Mérignac. Estimation, vente et achat de maisons et appartements. Eduardo De Sul, conseiller indépendant EXP France en Gironde.',
    'marche'      => "Mérignac propose un marché plus accessible que Bordeaux avec des prix autour de 3 200 €/m². Les quartiers résidentiels comme Capeyron et les Pins offrent de belles maisons avec jardin entre 2 900 et 3 800 €/m². La proximité de l'aéroport et des grands axes routiers en fait une destination prisée des actifs.",
    'faq'         => [
        ['q' => 'Quels sont les prix de l\'immobilier à Mérignac ?', 'a' => 'Les prix varient entre 2 700 et 3 800 €/m² selon le quartier. Les secteurs proches du tramway (ligne A) sont les plus recherchés.'],
        ['q' => 'Mérignac est-elle bien desservie par les transports ?', 'a' => 'Oui, la ligne A du tramway relie Mérignac à Bordeaux en moins de 20 minutes. L\'aéroport international est également accessible rapidement.'],
    ],
];
require __DIR__ . '/../_ville-secteur.php';

<?php
$ville = [
    'nom'         => 'Pessac',
    'type'        => 'ville',
    'prix'        => '3 400',
    'tendance'    => '+1,4%',
    'delai'       => '44 jours',
    'image'       => '/assets/images/pessac.jpg',
    'description' => 'Pessac associe université, espaces verts et quartiers résidentiels prisés, à l’ouest de Bordeaux, avec un marché immobilier actif sur les maisons et les appartements.',
    'metaDesc'    => 'Immobilier à Pessac (33600) : estimation, vente et achat avec Eduardo Desul. Expertise du marché local, Cité Frugès et secteurs résidentiels.',
    'marche'      => "Pessac attire familles, universitaires et actifs grâce au campus, au tram et à la qualité de vie. Les maisons avec jardin restent très demandées sur les secteurs pavillonnaires, tandis que les appartements proches des axes structurants et du campus bénéficient d'une bonne liquidité. Les prix varient sensiblement entre le nord, le centre-ville et les zones plus résidentielles : l'estimation repose sur des comparables intra-commune.",
    'faq'         => [
        ['q' => 'Pessac est-elle bien desservie par les transports ?', 'a' => "Oui : tram, bus et liaison rapide vers Bordeaux structurent l'attractivité, surtout pour les appartements bien placés."],
        ['q' => 'Quels sont les secteurs les plus recherchés ?', 'a' => "Les quartiers résidentiels au charme patrimonial (dont la zone UNESCO de la Cité Frugès) et les secteurs pavillonnaires calmes concentrent une forte demande pour les maisons."],
    ],
];
require __DIR__ . '/../_ville-secteur.php';

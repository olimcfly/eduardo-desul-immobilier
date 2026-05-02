<?php
/**
 * Liens de recherche Google Maps par catégorie — Mérignac (33700).
 * Remplacer progressivement les URLs de recherche par des liens de fiche (g.page / maps.app.goo.gl) une fois identifiées.
 *
 * @return array{ville: string, slug: string, postal: string, categories: list<array{slug: string, title: string, icon: string, items: list<array{name: string, gmb: string, web: string}>}>}
 */
return [
    'ville'   => 'Mérignac',
    'slug'    => 'merignac',
    'postal'  => '33700',
    'categories' => [
        [
            'slug'  => 'boulangerie',
            'title' => 'Boulangerie',
            'icon'  => 'fa-bread-slice',
            'items' => [
                ['name' => 'Boulangeries artisanales', 'gmb' => 'https://www.google.com/maps/search/Boulangerie+M%C3%A9rignac+33700', 'web' => ''],
            ],
        ],
        [
            'slug'  => 'restauration',
            'title' => 'Restauration',
            'icon'  => 'fa-utensils',
            'items' => [
                ['name' => 'Restaurants du centre', 'gmb' => 'https://www.google.com/maps/search/Restaurant+M%C3%A9rignac+33700', 'web' => ''],
                ['name' => 'Sushi & brasseries', 'gmb' => 'https://www.google.com/maps/search/Restaurant+asiatique+M%C3%A9rignac', 'web' => ''],
            ],
        ],
        [
            'slug'  => 'cafe-bar',
            'title' => 'Café / Bar',
            'icon'  => 'fa-mug-hot',
            'items' => [
                ['name' => 'Cafés & bars', 'gmb' => 'https://www.google.com/maps/search/Caf%C3%A9+Bar+M%C3%A9rignac', 'web' => ''],
            ],
        ],
        [
            'slug'  => 'epicerie',
            'title' => 'Épicerie',
            'icon'  => 'fa-basket-shopping',
            'items' => [
                ['name' => 'Commerces alimentaires', 'gmb' => 'https://www.google.com/maps/search/%C3%89picerie+M%C3%A9rignac+33700', 'web' => ''],
            ],
        ],
        [
            'slug'  => 'mode',
            'title' => 'Mode',
            'icon'  => 'fa-shirt',
            'items' => [
                ['name' => 'Boutiques de mode', 'gmb' => 'https://www.google.com/maps/search/Magasin+de+mode+M%C3%A9rignac', 'web' => ''],
            ],
        ],
        [
            'slug'  => 'coiffure-beaute',
            'title' => 'Coiffure / Beauté',
            'icon'  => 'fa-cut',
            'items' => [
                ['name' => 'Salons de coiffure', 'gmb' => 'https://www.google.com/maps/search/Coiffeur+M%C3%A9rignac+33700', 'web' => ''],
                ['name' => 'Instituts de beauté', 'gmb' => 'https://www.google.com/maps/search/Institut+de+beaut%C3%A9+M%C3%A9rignac', 'web' => ''],
            ],
        ],
        [
            'slug'  => 'sante',
            'title' => 'Santé',
            'icon'  => 'fa-heart-pulse',
            'items' => [
                ['name' => 'Pharmacies', 'gmb' => 'https://www.google.com/maps/search/Pharmacie+M%C3%A9rignac+33700', 'web' => ''],
                ['name' => 'Médecins généralistes', 'gmb' => 'https://www.google.com/maps/search/M%C3%A9decin+g%C3%A9n%C3%A9raliste+M%C3%A9rignac', 'web' => ''],
            ],
        ],
        [
            'slug'  => 'artisanat',
            'title' => 'Artisanat',
            'icon'  => 'fa-hammer',
            'items' => [
                ['name' => 'Menuisiers', 'gmb' => 'https://www.google.com/maps/search/Menuisier+M%C3%A9rignac+33700', 'web' => ''],
                ['name' => 'Plombiers', 'gmb' => 'https://www.google.com/maps/search/Plombier+M%C3%A9rignac+33700', 'web' => ''],
            ],
        ],
        [
            'slug'  => 'services',
            'title' => 'Services',
            'icon'  => 'fa-screwdriver-wrench',
            'items' => [
                ['name' => 'Garages automobiles', 'gmb' => 'https://www.google.com/maps/search/Garage+auto+M%C3%A9rignac+33700', 'web' => ''],
                ['name' => 'Agences immobilières', 'gmb' => 'https://www.google.com/maps/search/Agence+immobili%C3%A8re+M%C3%A9rignac', 'web' => 'https://eduardo-desul-immobilier.fr/'],
            ],
        ],
        [
            'slug'  => 'loisirs-culture',
            'title' => 'Loisirs / Culture',
            'icon'  => 'fa-masks-theater',
            'items' => [
                ['name' => 'Cinémas', 'gmb' => 'https://www.google.com/maps/search/Cin%C3%A9ma+M%C3%A9rignac+33700', 'web' => ''],
                ['name' => 'Musées & loisirs', 'gmb' => 'https://www.google.com/maps/search/Mus%C3%A9e+M%C3%A9rignac', 'web' => ''],
            ],
        ],
    ],
];

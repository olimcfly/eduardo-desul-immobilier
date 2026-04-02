<?php

return [
    'launchpad' => [
        'title' => 'Construire',
        'subtitle' => 'Poser les bases solides de votre activité immobilière',
        'icon' => 'fa-cube',
        '3r' => [
            'realite' => [
                'title' => 'Réalité',
                'text' => '80% des agents immobiliers échouent dans les 2 premières années.',
            ],
            'resultat' => [
                'title' => 'Résultat recherché',
                'text' => 'Une stratégie claire et des fondations inébranlables.',
            ],
            'risque' => [
                'title' => 'Risque à éviter',
                'text' => 'Se lancer sans plan précis = gaspillage de temps.',
            ],
        ],
        'mere' => [
            'motivation' => [
                'title' => 'Pourquoi c’est important ?',
                'text' => 'Sans bases solides, même les meilleures techniques échouent.',
            ],
            'explication' => [
                'title' => 'Ce qu’il faut comprendre',
                'text' => 'Votre activité doit reposer sur une vision et des processus.',
            ],
            'resultat' => [
                'title' => 'Ce que vous allez obtenir',
                'text' => 'Un plan d’action personnalisé.',
            ],
            'exercice' => [
                'title' => 'Exercice rapide',
                'text' => 'Notez 3 objectifs pour les 6 prochains mois.',
            ],
        ],
        'actions' => [
            'Je pars de zéro',
            'J’ai déjà une stratégie',
            'Je veux affiner ma cible',
        ],
        'action_routes' => [
            'choice_0' => '?page=launchpad&step=fondations',
            'choice_1' => '?page=launchpad&step=diagnostic',
            'choice_2' => '?page=launchpad&step=positionnement',
        ],
        'has_free_field' => true,
        'dashboard_url' => '?page=launchpad',
        'welcome_url' => '?page=launchpad',
    ],
    'seo' => [
        'title' => 'Attirer',
        'subtitle' => 'Attirer des prospects immobiliers qualifiés en continu',
        'icon' => 'fa-magnet',
        '3r' => [
            'realite' => [
                'title' => 'Réalité',
                'text' => 'La majorité des agences publie du contenu sans cap ni conversion.',
            ],
            'resultat' => [
                'title' => 'Résultat recherché',
                'text' => 'Une stratégie d’attraction locale qui génère des demandes concrètes.',
            ],
            'risque' => [
                'title' => 'Risque à éviter',
                'text' => 'Créer du trafic non qualifié qui ne se transforme pas en mandats.',
            ],
        ],
        'mere' => [
            'motivation' => [
                'title' => 'Pourquoi c’est important ?',
                'text' => 'Un flux constant de prospects réduit la dépendance à la prospection froide.',
            ],
            'explication' => [
                'title' => 'Ce qu’il faut comprendre',
                'text' => 'Le bon message doit rencontrer la bonne audience au bon moment.',
            ],
            'resultat' => [
                'title' => 'Ce que vous allez obtenir',
                'text' => 'Un plan d’attraction ciblé selon vos zones et vos offres.',
            ],
            'exercice' => [
                'title' => 'Exercice rapide',
                'text' => 'Identifiez 3 questions clients fréquentes à transformer en contenus.',
            ],
        ],
        'actions' => [
            'Je débute totalement',
            'J’ai déjà du contenu',
            'Je veux plus de leads qualifiés',
        ],
        'action_routes' => [
            'choice_0' => '?page=seo&view=basics',
            'choice_1' => '?page=seo&view=optimisation',
            'choice_2' => '?page=seo&view=conversion',
        ],
        'has_free_field' => true,
        'dashboard_url' => '?page=seo',
        'welcome_url' => '?page=seo',
    ],
];

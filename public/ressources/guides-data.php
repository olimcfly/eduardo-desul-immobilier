<?php

return [
    'vendeur' => [
        'label' => 'Vendeur',
        'icon' => '🏠',
        'description' => 'Des guides ciblés selon votre situation de vente.',
        'guides' => [
            [
                'slug' => 'vendre-residence-principale',
                'title' => 'Vendre sa résidence principale',
                'excerpt' => 'Préparer votre bien, fixer le bon prix et vendre rapidement sans sacrifier la valeur.',
                'points' => [
                    'Comment estimer votre bien au juste prix dès le départ.',
                    'Checklist avant mise en vente : diagnostics, documents, présentation.',
                    'Plan de commercialisation et stratégie de négociation.',
                ],
            ],
            [
                'slug' => 'vendre-bien-locatif',
                'title' => 'Vendre un bien locatif',
                'excerpt' => 'Arbitrer au bon moment et vendre en tenant compte du locataire, de la rentabilité et de la fiscalité.',
                'points' => [
                    'Vente occupée vs vente libre : impacts sur le prix et les délais.',
                    'Informations obligatoires à communiquer à l’acquéreur.',
                    'Pilotage du calendrier pour limiter la vacance ou les pertes.',
                ],
            ],
            [
                'slug' => 'vendre-apres-succession',
                'title' => 'Vendre après succession',
                'excerpt' => 'Un cadre clair pour gérer la vente entre héritiers, le notaire et les étapes administratives.',
                'points' => [
                    'Documents à réunir avant la mise sur le marché.',
                    'Organisation de la décision entre indivisaires.',
                    'Points de vigilance sur les délais et les coûts.',
                ],
            ],
        ],
    ],
    'acheteur' => [
        'label' => 'Acheteur',
        'icon' => '🔑',
        'description' => 'Des guides pratiques pour sécuriser votre achat immobilier.',
        'guides' => [
            [
                'slug' => 'premier-achat',
                'title' => 'Réussir son premier achat',
                'excerpt' => 'De la capacité d’emprunt à l’acte authentique, toutes les étapes pour un premier achat serein.',
                'points' => [
                    'Définir vos critères prioritaires et votre budget global.',
                    'Constituer un dossier bancaire solide pour négocier votre prêt.',
                    'Analyser un bien et faire une offre pertinente.',
                ],
            ],
            [
                'slug' => 'acheter-pour-investir',
                'title' => 'Acheter pour investir',
                'excerpt' => 'Identifier les secteurs porteurs, estimer la rentabilité et limiter le risque locatif.',
                'points' => [
                    'Lire un secteur : tension locative, vacance, typologie des locataires.',
                    'Calculer le rendement net et anticiper les charges.',
                    'Sécuriser l’achat avec une stratégie long terme.',
                ],
            ],
            [
                'slug' => 'achat-revente',
                'title' => 'Achat-revente : mode d’emploi',
                'excerpt' => 'Comment enchainer vente et achat avec un calendrier maîtrisé et un financement sécurisé.',
                'points' => [
                    'Synchroniser compromis, financement et déménagement.',
                    'Réduire les risques de double charge ou de prêt relais long.',
                    'Préparer un plan B en cas de décalage des signatures.',
                ],
            ],
        ],
    ],
];

<?php
/**
 * CONFIGURATION DES TEMPLATES ET BLOCS
 *
 * Fichier de config statique (côté DEV) qui définit :
 * - Quels templates sont disponibles
 * - Quels blocs composent chaque template
 * - La structure et les champs de chaque bloc
 *
 * Les VALEURS des blocs sont stockées en DB (modifiables par CLIENT)
 * La STRUCTURE des templates est définie ici (contrôlée par DEV)
 */

return [
    // ========================================
    // TEMPLATES DISPONIBLES
    // ========================================

    'templates' => [

        // ─── HOME ────────────────────────────────────────
        'home' => [
            'name' => 'Accueil',
            'description' => 'Page d\'accueil haut de gamme - Eduardo De Sul',
            'icon' => 'fas fa-home',
            'blocks' => [
                'hero' => [
                    'type' => 'home_hero',
                    'label' => 'Hero principal',
                    'description' => 'Section héro avec titre, sous-titre, 2 CTA et badge',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Titre principal', 'required' => true],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre'],
                        'background_image' => ['type' => 'image', 'label' => 'Image de fond'],
                        'cta_primary_text' => ['type' => 'text', 'label' => 'CTA primaire - Texte'],
                        'cta_primary_url' => ['type' => 'url', 'label' => 'CTA primaire - URL'],
                        'cta_secondary_text' => ['type' => 'text', 'label' => 'CTA secondaire - Texte'],
                        'cta_secondary_url' => ['type' => 'url', 'label' => 'CTA secondaire - URL'],
                        'badge' => ['type' => 'text', 'label' => 'Badge (ex: "Depuis 15 ans")'],
                    ]
                ],
                'services' => [
                    'type' => 'home_services',
                    'label' => 'Services',
                    'description' => 'Titre + 3 services (icône, titre, description, lien)',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre section', 'required' => true],
                        'card_1_icon' => ['type' => 'text', 'label' => 'Service 1 - Icône'],
                        'card_1_title' => ['type' => 'text', 'label' => 'Service 1 - Titre'],
                        'card_1_description' => ['type' => 'textarea', 'label' => 'Service 1 - Description'],
                        'card_1_link' => ['type' => 'url', 'label' => 'Service 1 - Lien'],
                        'card_2_icon' => ['type' => 'text', 'label' => 'Service 2 - Icône'],
                        'card_2_title' => ['type' => 'text', 'label' => 'Service 2 - Titre'],
                        'card_2_description' => ['type' => 'textarea', 'label' => 'Service 2 - Description'],
                        'card_2_link' => ['type' => 'url', 'label' => 'Service 2 - Lien'],
                        'card_3_icon' => ['type' => 'text', 'label' => 'Service 3 - Icône'],
                        'card_3_title' => ['type' => 'text', 'label' => 'Service 3 - Titre'],
                        'card_3_description' => ['type' => 'textarea', 'label' => 'Service 3 - Description'],
                        'card_3_link' => ['type' => 'url', 'label' => 'Service 3 - Lien'],
                    ]
                ],
                'advisor_intro' => [
                    'type' => 'home_advisor',
                    'label' => 'Présentation Conseiller',
                    'description' => 'Présentation du conseiller avec photo et bio courte',
                    'fields' => [
                        'photo' => ['type' => 'image', 'label' => 'Photo du conseiller'],
                        'name' => ['type' => 'text', 'label' => 'Nom', 'required' => true],
                        'title' => ['type' => 'text', 'label' => 'Titre/Fonction'],
                        'bio_short' => ['type' => 'textarea', 'label' => 'Bio courte (2-3 lignes)'],
                        'cta_text' => ['type' => 'text', 'label' => 'CTA - Texte'],
                        'cta_url' => ['type' => 'url', 'label' => 'CTA - URL'],
                    ]
                ],
                'social_proof' => [
                    'type' => 'home_social_proof',
                    'label' => 'Preuve sociale',
                    'description' => 'Nombre d\'avis, note, CTA Google Reviews',
                    'fields' => [
                        'stars' => ['type' => 'number', 'label' => 'Note (ex: 4.8)', 'required' => true],
                        'count' => ['type' => 'number', 'label' => 'Nombre d\'avis', 'required' => true],
                        'cta_text' => ['type' => 'text', 'label' => 'Texte CTA Google', 'value' => 'Voir nos avis'],
                        'cta_url' => ['type' => 'url', 'label' => 'Lien Google Reviews'],
                    ]
                ],
                'sectors' => [
                    'type' => 'home_sectors',
                    'label' => 'Secteurs d\'intervention',
                    'description' => 'Titre + liste de secteurs avec liens',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre section'],
                        'items' => ['type' => 'repeater', 'label' => 'Secteurs', 'item_fields' => [
                            'name' => ['type' => 'text', 'label' => 'Nom secteur'],
                            'slug' => ['type' => 'text', 'label' => 'URL slug'],
                        ]],
                    ]
                ],
                'cta_final' => [
                    'type' => 'home_cta_final',
                    'label' => 'Appel à l\'action final',
                    'description' => 'CTA final avec message de rassurance',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre principal', 'required' => true],
                        'subtext' => ['type' => 'textarea', 'label' => 'Sous-texte'],
                        'cta_text' => ['type' => 'text', 'label' => 'CTA - Texte'],
                        'cta_url' => ['type' => 'url', 'label' => 'CTA - URL'],
                        'reassurance' => ['type' => 'text', 'label' => 'Message rassurance (ex: "Sans engagement")'],
                    ]
                ],
            ]
        ],

        // ─── ACHETER ─────────────────────────────────────
        'acheter' => [
            'name' => 'Acheter',
            'description' => 'Page pour acheter un bien avec hero et liste propriétés',
            'icon' => 'fas fa-shopping-cart',
            'blocks' => [
                'hero' => [
                    'type' => 'acheter_hero',
                    'label' => 'Hero principal',
                    'description' => 'Section héro avec titre, sous-titre et 2 CTA',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Titre principal', 'required' => true],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre'],
                        'background_image' => ['type' => 'image', 'label' => 'Image de fond'],
                        'cta_primary_text' => ['type' => 'text', 'label' => 'CTA primaire - Texte'],
                        'cta_primary_url' => ['type' => 'url', 'label' => 'CTA primaire - URL'],
                        'cta_secondary_text' => ['type' => 'text', 'label' => 'CTA secondaire - Texte'],
                        'cta_secondary_url' => ['type' => 'url', 'label' => 'CTA secondaire - URL'],
                    ]
                ],
                'pain_points' => [
                    'type' => 'acheter_pain_points',
                    'label' => 'Défis de l\'acheteur',
                    'description' => 'Afficher les principaux défis avec solutions',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre section'],
                        'items' => ['type' => 'repeater', 'label' => 'Défis', 'item_fields' => [
                            'icon' => ['type' => 'text', 'label' => 'Icône'],
                            'title' => ['type' => 'text', 'label' => 'Défi'],
                            'solution' => ['type' => 'textarea', 'label' => 'Notre solution'],
                        ]],
                    ]
                ],
                'advisor' => [
                    'type' => 'acheter_advisor',
                    'label' => 'Pourquoi nous choisir',
                    'description' => 'Présentation du conseiller et de son approche',
                    'fields' => [
                        'photo' => ['type' => 'image', 'label' => 'Photo du conseiller'],
                        'name' => ['type' => 'text', 'label' => 'Nom du conseiller', 'required' => true],
                        'title' => ['type' => 'text', 'label' => 'Titre/Fonction'],
                        'intro' => ['type' => 'textarea', 'label' => 'Introduction courte'],
                        'benefits' => ['type' => 'repeater', 'label' => 'Avantages', 'item_fields' => [
                            'icon' => ['type' => 'text', 'label' => 'Icône'],
                            'text' => ['type' => 'text', 'label' => 'Avantage'],
                        ]],
                        'cta_text' => ['type' => 'text', 'label' => 'CTA - Texte'],
                        'cta_url' => ['type' => 'url', 'label' => 'CTA - URL'],
                    ]
                ],
                'steps' => [
                    'type' => 'acheter_steps',
                    'label' => 'Processus d\'achat',
                    'description' => 'Les étapes pour acheter un bien',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre section'],
                        'items' => ['type' => 'repeater', 'label' => 'Étapes', 'item_fields' => [
                            'title' => ['type' => 'text', 'label' => 'Titre étape'],
                            'description' => ['type' => 'textarea', 'label' => 'Description'],
                        ]],
                    ]
                ],
                'listings' => [
                    'type' => 'acheter_listings',
                    'label' => 'Propriétés récentes',
                    'description' => 'Aperçu des propriétés à vendre',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre section'],
                        'description' => ['type' => 'textarea', 'label' => 'Description'],
                        'cta_text' => ['type' => 'text', 'label' => 'CTA - Voir plus'],
                        'cta_url' => ['type' => 'url', 'label' => 'CTA - URL'],
                    ]
                ],
                'guide' => [
                    'type' => 'acheter_guide',
                    'label' => 'Guide du buyer',
                    'description' => 'Ressources et guide pour l\'acheteur',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre section'],
                        'items' => ['type' => 'repeater', 'label' => 'Ressources', 'item_fields' => [
                            'icon' => ['type' => 'text', 'label' => 'Icône'],
                            'title' => ['type' => 'text', 'label' => 'Titre'],
                            'description' => ['type' => 'textarea', 'label' => 'Description'],
                        ]],
                    ]
                ],
                'cta_final' => [
                    'type' => 'acheter_cta_final',
                    'label' => 'Appel à l\'action final',
                    'description' => 'CTA final pour contact/visite',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre principal', 'required' => true],
                        'subtext' => ['type' => 'textarea', 'label' => 'Sous-texte'],
                        'cta_text' => ['type' => 'text', 'label' => 'CTA - Texte'],
                        'cta_url' => ['type' => 'url', 'label' => 'CTA - URL'],
                    ]
                ],
            ]
        ],

        // ─── VENDRE ──────────────────────────────────────
        'vendre' => [
            'name' => 'Vendre',
            'description' => 'Page pour vendre avec processus et estimation',
            'icon' => 'fas fa-tag',
            'blocks' => [
                'hero' => [
                    'type' => 'vendre_hero',
                    'label' => 'Hero principal',
                    'description' => 'Section héro avec titre, sous-titre et 2 CTA',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Titre principal', 'required' => true],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre'],
                        'background_image' => ['type' => 'image', 'label' => 'Image de fond'],
                        'cta_primary_text' => ['type' => 'text', 'label' => 'CTA primaire - Texte'],
                        'cta_primary_url' => ['type' => 'url', 'label' => 'CTA primaire - URL'],
                        'cta_secondary_text' => ['type' => 'text', 'label' => 'CTA secondaire - Texte'],
                        'cta_secondary_url' => ['type' => 'url', 'label' => 'CTA secondaire - URL'],
                    ]
                ],
                'pain_points' => [
                    'type' => 'vendre_pain_points',
                    'label' => 'Défis du vendeur',
                    'description' => 'Afficher les principaux défis avec solutions',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre section'],
                        'items' => ['type' => 'repeater', 'label' => 'Défis', 'item_fields' => [
                            'icon' => ['type' => 'text', 'label' => 'Icône'],
                            'title' => ['type' => 'text', 'label' => 'Défi'],
                            'solution' => ['type' => 'textarea', 'label' => 'Notre solution'],
                        ]],
                    ]
                ],
                'advisor' => [
                    'type' => 'vendre_advisor',
                    'label' => 'Pourquoi nous choisir',
                    'description' => 'Présentation du conseiller et de son approche',
                    'fields' => [
                        'photo' => ['type' => 'image', 'label' => 'Photo du conseiller'],
                        'name' => ['type' => 'text', 'label' => 'Nom du conseiller', 'required' => true],
                        'title' => ['type' => 'text', 'label' => 'Titre/Fonction'],
                        'intro' => ['type' => 'textarea', 'label' => 'Introduction courte'],
                        'benefits' => ['type' => 'repeater', 'label' => 'Avantages', 'item_fields' => [
                            'icon' => ['type' => 'text', 'label' => 'Icône'],
                            'text' => ['type' => 'text', 'label' => 'Avantage'],
                        ]],
                        'cta_text' => ['type' => 'text', 'label' => 'CTA - Texte'],
                        'cta_url' => ['type' => 'url', 'label' => 'CTA - URL'],
                    ]
                ],
                'steps' => [
                    'type' => 'vendre_steps',
                    'label' => 'Processus de vente',
                    'description' => 'Les étapes pour vendre un bien',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre section'],
                        'items' => ['type' => 'repeater', 'label' => 'Étapes', 'item_fields' => [
                            'title' => ['type' => 'text', 'label' => 'Titre étape'],
                            'description' => ['type' => 'textarea', 'label' => 'Description'],
                        ]],
                    ]
                ],
                'guide' => [
                    'type' => 'vendre_guide',
                    'label' => 'Guide du vendeur',
                    'description' => 'Ressources et guide pour le vendeur',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre section'],
                        'items' => ['type' => 'repeater', 'label' => 'Ressources', 'item_fields' => [
                            'icon' => ['type' => 'text', 'label' => 'Icône'],
                            'title' => ['type' => 'text', 'label' => 'Titre'],
                            'description' => ['type' => 'textarea', 'label' => 'Description'],
                        ]],
                    ]
                ],
                'social_proof' => [
                    'type' => 'vendre_social_proof',
                    'label' => 'Preuve sociale',
                    'description' => 'Avis clients et témoignages',
                    'fields' => [
                        'stars' => ['type' => 'number', 'label' => 'Note (ex: 4.8)', 'required' => true],
                        'count' => ['type' => 'number', 'label' => 'Nombre d\'avis', 'required' => true],
                        'cta_text' => ['type' => 'text', 'label' => 'Texte CTA', 'value' => 'Voir les avis'],
                        'cta_url' => ['type' => 'url', 'label' => 'Lien avis'],
                    ]
                ],
                'cta_final' => [
                    'type' => 'vendre_cta_final',
                    'label' => 'Appel à l\'action final',
                    'description' => 'CTA final pour estimation/contact',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre principal', 'required' => true],
                        'subtext' => ['type' => 'textarea', 'label' => 'Sous-texte'],
                        'cta_text' => ['type' => 'text', 'label' => 'CTA - Texte'],
                        'cta_url' => ['type' => 'url', 'label' => 'CTA - URL'],
                    ]
                ],
            ]
        ],

        // ─── ESTIMER ─────────────────────────────────────
        'estimer' => [
            'name' => 'Estimer',
            'description' => 'Page d\'estimation gratuite de bien immobilier',
            'icon' => 'fas fa-calculator',
            'blocks' => [
                'hero' => [
                    'type' => 'estimer_hero',
                    'label' => 'Hero principal',
                    'description' => 'Section héro avec titre et sous-titre',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Titre principal', 'required' => true],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre'],
                        'background_image' => ['type' => 'image', 'label' => 'Image de fond'],
                    ]
                ],
                'form_estimation' => [
                    'type' => 'estimer_form',
                    'label' => 'Formulaire d\'estimation',
                    'description' => 'Formulaire pour estimer un bien',
                    'fields' => [
                        'form_title' => ['type' => 'text', 'label' => 'Titre du formulaire'],
                        'form_description' => ['type' => 'textarea', 'label' => 'Description du formulaire'],
                    ]
                ],
                'method' => [
                    'type' => 'estimer_method',
                    'label' => 'Méthode d\'estimation',
                    'description' => 'Explication de la méthode d\'estimation',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre section'],
                        'items' => ['type' => 'repeater', 'label' => 'Étapes', 'item_fields' => [
                            'icon' => ['type' => 'text', 'label' => 'Icône'],
                            'title' => ['type' => 'text', 'label' => 'Titre'],
                            'description' => ['type' => 'textarea', 'label' => 'Description'],
                        ]],
                    ]
                ],
                'why_free' => [
                    'type' => 'estimer_why_free',
                    'label' => 'Pourquoi c\'est gratuit',
                    'description' => 'Explication sur la gratuité de l\'estimation',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre section'],
                        'description' => ['type' => 'textarea', 'label' => 'Description'],
                        'icon' => ['type' => 'text', 'label' => 'Icône'],
                    ]
                ],
                'social_proof' => [
                    'type' => 'estimer_social_proof',
                    'label' => 'Preuve sociale',
                    'description' => 'Avis clients et témoignages',
                    'fields' => [
                        'stars' => ['type' => 'number', 'label' => 'Note (ex: 4.8)', 'required' => true],
                        'count' => ['type' => 'number', 'label' => 'Nombre d\'avis', 'required' => true],
                        'cta_text' => ['type' => 'text', 'label' => 'Texte CTA'],
                        'cta_url' => ['type' => 'url', 'label' => 'Lien avis'],
                    ]
                ],
                'cta_final' => [
                    'type' => 'estimer_cta_final',
                    'label' => 'Appel à l\'action final',
                    'description' => 'CTA final pour demander une estimation complète',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre principal', 'required' => true],
                        'subtext' => ['type' => 'textarea', 'label' => 'Sous-texte'],
                        'cta_text' => ['type' => 'text', 'label' => 'CTA - Texte'],
                        'cta_url' => ['type' => 'url', 'label' => 'CTA - URL'],
                    ]
                ],
            ]
        ],

        // ─── SECTEURS ────────────────────────────────────
        'secteurs' => [
            'name' => 'Secteurs',
            'description' => 'Page de présentation des secteurs d\'intervention',
            'icon' => 'fas fa-map-marker-alt',
            'blocks' => [
                'hero' => [
                    'type' => 'secteurs_hero',
                    'label' => 'Hero principal',
                    'description' => 'Section héro avec titre et sous-titre',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Titre principal', 'required' => true],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre'],
                        'background_image' => ['type' => 'image', 'label' => 'Image de fond'],
                    ]
                ],
                'sectors_grid' => [
                    'type' => 'secteurs_grid',
                    'label' => 'Grille des secteurs',
                    'description' => 'Liste des secteurs d\'intervention avec descriptions',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre section'],
                        'items' => ['type' => 'repeater', 'label' => 'Secteurs', 'item_fields' => [
                            'name' => ['type' => 'text', 'label' => 'Nom du secteur'],
                            'slug' => ['type' => 'text', 'label' => 'URL slug'],
                            'description' => ['type' => 'textarea', 'label' => 'Description courte'],
                            'icon' => ['type' => 'text', 'label' => 'Icône'],
                        ]],
                    ]
                ],
                'advisor' => [
                    'type' => 'secteurs_advisor',
                    'label' => 'Présentation conseiller',
                    'description' => 'Présentation du conseiller spécialisé',
                    'fields' => [
                        'photo' => ['type' => 'image', 'label' => 'Photo du conseiller'],
                        'name' => ['type' => 'text', 'label' => 'Nom', 'required' => true],
                        'title' => ['type' => 'text', 'label' => 'Titre/Fonction'],
                        'intro' => ['type' => 'textarea', 'label' => 'Introduction'],
                        'cta_text' => ['type' => 'text', 'label' => 'CTA - Texte'],
                        'cta_url' => ['type' => 'url', 'label' => 'CTA - URL'],
                    ]
                ],
                'cta_final' => [
                    'type' => 'secteurs_cta_final',
                    'label' => 'Appel à l\'action final',
                    'description' => 'CTA final',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre principal', 'required' => true],
                        'subtext' => ['type' => 'textarea', 'label' => 'Sous-texte'],
                        'cta_text' => ['type' => 'text', 'label' => 'CTA - Texte'],
                        'cta_url' => ['type' => 'url', 'label' => 'CTA - URL'],
                    ]
                ],
            ]
        ],

        // ─── FINANCEMENT ─────────────────────────────────
        'financement' => [
            'name' => 'Financement',
            'description' => 'Page d\'aide au financement immobilier',
            'icon' => 'fas fa-piggy-bank',
            'blocks' => [
                'hero' => [
                    'type' => 'financement_hero',
                    'label' => 'Hero principal',
                    'description' => 'Section héro avec titre et sous-titre',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Titre principal', 'required' => true],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre'],
                        'background_image' => ['type' => 'image', 'label' => 'Image de fond'],
                        'cta_text' => ['type' => 'text', 'label' => 'CTA - Texte'],
                        'cta_url' => ['type' => 'url', 'label' => 'CTA - URL'],
                    ]
                ],
                'intro' => [
                    'type' => 'financement_intro',
                    'label' => 'Introduction',
                    'description' => 'Introduction au financement immobilier',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre section'],
                        'description' => ['type' => 'textarea', 'label' => 'Description'],
                    ]
                ],
                'steps' => [
                    'type' => 'financement_steps',
                    'label' => 'Processus de financement',
                    'description' => 'Les étapes pour financer',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre section'],
                        'items' => ['type' => 'repeater', 'label' => 'Étapes', 'item_fields' => [
                            'title' => ['type' => 'text', 'label' => 'Titre étape'],
                            'description' => ['type' => 'textarea', 'label' => 'Description'],
                        ]],
                    ]
                ],
                'guide' => [
                    'type' => 'financement_guide',
                    'label' => 'Guide du financement',
                    'description' => 'Ressources et guides',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre section'],
                        'items' => ['type' => 'repeater', 'label' => 'Ressources', 'item_fields' => [
                            'icon' => ['type' => 'text', 'label' => 'Icône'],
                            'title' => ['type' => 'text', 'label' => 'Titre'],
                            'description' => ['type' => 'textarea', 'label' => 'Description'],
                        ]],
                    ]
                ],
                'partner' => [
                    'type' => 'financement_partner',
                    'label' => 'Partenaire bancaire',
                    'description' => 'Présentation du partenaire/réseau bancaire',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre section'],
                        'description' => ['type' => 'textarea', 'label' => 'Description'],
                        'logo' => ['type' => 'image', 'label' => 'Logo partenaire'],
                    ]
                ],
                'cta_final' => [
                    'type' => 'financement_cta_final',
                    'label' => 'Appel à l\'action final',
                    'description' => 'CTA final',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre principal', 'required' => true],
                        'subtext' => ['type' => 'textarea', 'label' => 'Sous-texte'],
                        'cta_text' => ['type' => 'text', 'label' => 'CTA - Texte'],
                        'cta_url' => ['type' => 'url', 'label' => 'CTA - URL'],
                    ]
                ],
            ]
        ],

        // ─── BLOG ────────────────────────────────────────
        'blog' => [
            'name' => 'Blog',
            'description' => 'Page d\'archive et liste du blog',
            'icon' => 'fas fa-newspaper',
            'blocks' => [
                'hero' => [
                    'type' => 'blog_hero',
                    'label' => 'Hero principal',
                    'description' => 'Section héro avec titre',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Titre principal', 'required' => true],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre'],
                        'background_image' => ['type' => 'image', 'label' => 'Image de fond'],
                    ]
                ],
                'posts' => [
                    'type' => 'blog_posts',
                    'label' => 'Liste des articles',
                    'description' => 'Affichage dynamique des articles',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre section'],
                        'posts_per_page' => ['type' => 'number', 'label' => 'Articles par page'],
                    ]
                ],
                'categories' => [
                    'type' => 'blog_categories',
                    'label' => 'Catégories',
                    'description' => 'Liste des catégories de blog',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre section'],
                        'show_count' => ['type' => 'checkbox', 'label' => 'Afficher le nombre d\'articles'],
                    ]
                ],
                'cta_final' => [
                    'type' => 'blog_cta_final',
                    'label' => 'Appel à l\'action final',
                    'description' => 'CTA pour newsletter ou contact',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre principal', 'required' => true],
                        'description' => ['type' => 'textarea', 'label' => 'Description'],
                        'cta_text' => ['type' => 'text', 'label' => 'CTA - Texte'],
                        'cta_url' => ['type' => 'url', 'label' => 'CTA - URL'],
                    ]
                ],
            ]
        ],

        // ─── LANDING ─────────────────────────────────────
        'landing' => [
            'name' => 'Landing Page',
            'description' => 'Page landing minimaliste',
            'icon' => 'fas fa-rocket',
            'blocks' => [
                'hero' => [
                    'type' => 'hero',
                    'label' => 'Hero principal',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Titre', 'required' => true],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre'],
                        'background_image' => ['type' => 'image', 'label' => 'Image de fond'],
                    ]
                ],
                'benefits' => [
                    'type' => 'features',
                    'label' => 'Points clés',
                    'fields' => [
                        'section_title' => ['type' => 'text', 'label' => 'Titre'],
                        'items' => ['type' => 'repeater', 'label' => 'Points', 'item_fields' => [
                            'icon' => ['type' => 'text', 'label' => 'Icône'],
                            'title' => ['type' => 'text', 'label' => 'Titre'],
                            'description' => ['type' => 'textarea', 'label' => 'Description'],
                        ]],
                    ]
                ],
                'form' => [
                    'type' => 'form',
                    'label' => 'Formulaire contact',
                    'fields' => [
                        'form_title' => ['type' => 'text', 'label' => 'Titre formulaire'],
                        'form_type' => ['type' => 'select', 'label' => 'Type formulaire', 'options' => ['contact' => 'Contact', 'estimation' => 'Estimation']],
                    ]
                ],
            ]
        ],

        // ─── LEGAL ───────────────────────────────────────
        'legal' => [
            'name' => 'Pages légales',
            'description' => 'Pour RGPD, CGU, Mentions légales',
            'icon' => 'fas fa-file-alt',
            'blocks' => [
                'title' => [
                    'type' => 'heading',
                    'label' => 'Titre de la page',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Titre', 'required' => true],
                        'subtitle' => ['type' => 'text', 'label' => 'Sous-titre (optionnel)'],
                    ]
                ],
                'content' => [
                    'type' => 'richtext',
                    'label' => 'Contenu principal',
                    'fields' => [
                        'html_content' => ['type' => 'richtext', 'label' => 'Contenu (HTML autorisé)'],
                    ]
                ],
            ]
        ],

        // ─── CONTACT ─────────────────────────────────────
        'contact' => [
            'name' => 'Contact',
            'description' => 'Page de contact avec formulaire et localisation',
            'icon' => 'fas fa-envelope',
            'blocks' => [
                'hero' => [
                    'type' => 'contact_hero',
                    'label' => 'Hero principal',
                    'description' => 'Section héro avec titre et sous-titre',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Titre', 'required' => true],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre'],
                        'background_image' => ['type' => 'image', 'label' => 'Image de fond'],
                    ]
                ],
                'contact_info' => [
                    'type' => 'contact_info',
                    'label' => 'Informations de contact',
                    'description' => 'Afficher les infos de contact (téléphone, email, heures)',
                    'fields' => [
                        'phone' => ['type' => 'tel', 'label' => 'Téléphone', 'required' => true],
                        'email' => ['type' => 'email', 'label' => 'Email', 'required' => true],
                        'address' => ['type' => 'text', 'label' => 'Adresse'],
                        'hours' => ['type' => 'textarea', 'label' => 'Horaires d\'ouverture'],
                    ]
                ],
                'contact_form' => [
                    'type' => 'contact_form',
                    'label' => 'Formulaire contact',
                    'description' => 'Formulaire de contact',
                    'fields' => [
                        'form_title' => ['type' => 'text', 'label' => 'Titre du formulaire'],
                        'form_description' => ['type' => 'textarea', 'label' => 'Description'],
                    ]
                ],
                'map' => [
                    'type' => 'contact_map',
                    'label' => 'Localisation',
                    'description' => 'Carte Google Maps avec localisation',
                    'fields' => [
                        'address' => ['type' => 'text', 'label' => 'Adresse complète'],
                        'map_embed' => ['type' => 'text', 'label' => 'Code iframe Google Maps'],
                    ]
                ],
                'social_proof' => [
                    'type' => 'contact_social_proof',
                    'label' => 'Preuve sociale',
                    'description' => 'Avis clients et témoignages',
                    'fields' => [
                        'stars' => ['type' => 'number', 'label' => 'Note (ex: 4.8)', 'required' => true],
                        'count' => ['type' => 'number', 'label' => 'Nombre d\'avis', 'required' => true],
                        'cta_text' => ['type' => 'text', 'label' => 'Texte CTA'],
                        'cta_url' => ['type' => 'url', 'label' => 'Lien avis'],
                    ]
                ],
            ]
        ],
    ],

    // ========================================
    // TYPES DE BLOCS (Réutilisables)
    // ========================================

    'block_types' => [
        'hero' => [
            'name' => 'Hero',
            'renderer' => 'blocks/hero.php'
        ],
        'features' => [
            'name' => 'Fonctionnalités/Services',
            'renderer' => 'blocks/features.php'
        ],
        'cta' => [
            'name' => 'Appel à l\'action',
            'renderer' => 'blocks/cta.php'
        ],
        'testimonials' => [
            'name' => 'Témoignages',
            'renderer' => 'blocks/testimonials.php'
        ],
        'filters' => [
            'name' => 'Filtres de recherche',
            'renderer' => 'blocks/filters.php'
        ],
        'steps' => [
            'name' => 'Étapes',
            'renderer' => 'blocks/steps.php'
        ],
        'faq' => [
            'name' => 'Questions fréquentes',
            'renderer' => 'blocks/faq.php'
        ],
        'form' => [
            'name' => 'Formulaire',
            'renderer' => 'blocks/form.php'
        ],
        'map' => [
            'name' => 'Carte',
            'renderer' => 'blocks/map.php'
        ],
        'heading' => [
            'name' => 'Titre',
            'renderer' => 'blocks/heading.php'
        ],
        'richtext' => [
            'name' => 'Contenu texte enrichi',
            'renderer' => 'blocks/richtext.php'
        ],
    ]
];

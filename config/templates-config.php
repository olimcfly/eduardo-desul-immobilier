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
                    'type' => 'hero',
                    'label' => 'Hero principal',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Titre', 'required' => true],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre'],
                        'background_image' => ['type' => 'image', 'label' => 'Image de fond'],
                        'background_color' => ['type' => 'color', 'label' => 'Couleur fond'],
                    ]
                ],
                'filters' => [
                    'type' => 'filters',
                    'label' => 'Filtres de recherche',
                    'description' => 'Affichage automatique via module Biens',
                    'fields' => [
                        'description' => ['type' => 'textarea', 'label' => 'Texte d\'introduction'],
                    ]
                ],
                'cta' => [
                    'type' => 'cta',
                    'label' => 'Appel à l\'action final',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre'],
                        'description' => ['type' => 'textarea', 'label' => 'Description'],
                        'button_text' => ['type' => 'text', 'label' => 'Texte bouton'],
                        'button_url' => ['type' => 'url', 'label' => 'URL bouton'],
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
                    'type' => 'hero',
                    'label' => 'Hero principal',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Titre', 'required' => true],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre'],
                        'background_image' => ['type' => 'image', 'label' => 'Image de fond'],
                    ]
                ],
                'steps' => [
                    'type' => 'steps',
                    'label' => 'Étapes du processus',
                    'description' => 'Processus en étapes numérotées',
                    'fields' => [
                        'section_title' => ['type' => 'text', 'label' => 'Titre section'],
                        'items' => ['type' => 'repeater', 'label' => 'Étapes', 'item_fields' => [
                            'title' => ['type' => 'text', 'label' => 'Titre étape'],
                            'description' => ['type' => 'textarea', 'label' => 'Description'],
                        ]],
                    ]
                ],
                'estimation_cta' => [
                    'type' => 'cta',
                    'label' => 'CTA Estimation',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre'],
                        'description' => ['type' => 'textarea', 'label' => 'Description'],
                        'button_text' => ['type' => 'text', 'label' => 'Texte bouton'],
                        'button_url' => ['type' => 'url', 'label' => 'URL bouton'],
                    ]
                ],
                'faq' => [
                    'type' => 'faq',
                    'label' => 'Questions fréquentes',
                    'fields' => [
                        'items' => ['type' => 'repeater', 'label' => 'FAQ', 'item_fields' => [
                            'question' => ['type' => 'text', 'label' => 'Question'],
                            'answer' => ['type' => 'richtext', 'label' => 'Réponse'],
                        ]],
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
                    'type' => 'hero',
                    'label' => 'Hero principal',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Titre', 'required' => true],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre'],
                        'background_image' => ['type' => 'image', 'label' => 'Image de fond'],
                    ]
                ],
                'contact_form' => [
                    'type' => 'form',
                    'label' => 'Formulaire contact',
                    'fields' => [
                        'form_title' => ['type' => 'text', 'label' => 'Titre du formulaire'],
                        'form_description' => ['type' => 'textarea', 'label' => 'Description'],
                    ]
                ],
                'map' => [
                    'type' => 'map',
                    'label' => 'Localisation',
                    'fields' => [
                        'address' => ['type' => 'text', 'label' => 'Adresse'],
                        'phone' => ['type' => 'tel', 'label' => 'Téléphone'],
                        'email' => ['type' => 'email', 'label' => 'Email'],
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

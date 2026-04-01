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
            'description' => 'Page d\'accueil avec hero, services, CTA et témoignages',
            'icon' => 'fas fa-home',
            'blocks' => [
                'hero' => [
                    'type' => 'hero',
                    'label' => 'Hero principal',
                    'description' => 'Section héro avec titre, sous-titre et image de fond',
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Titre', 'required' => true],
                        'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre'],
                        'background_image' => ['type' => 'image', 'label' => 'Image de fond'],
                        'button_text' => ['type' => 'text', 'label' => 'Texte du bouton'],
                        'button_url' => ['type' => 'url', 'label' => 'URL du bouton'],
                        'background_color' => ['type' => 'color', 'label' => 'Couleur fond (fallback)'],
                    ]
                ],
                'services' => [
                    'type' => 'features',
                    'label' => 'Services',
                    'description' => 'Section avec liste de services/bénéfices',
                    'fields' => [
                        'section_title' => ['type' => 'text', 'label' => 'Titre section'],
                        'section_subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre section'],
                        'items' => ['type' => 'repeater', 'label' => 'Services', 'item_fields' => [
                            'icon' => ['type' => 'text', 'label' => 'Icône (emoji ou class FontAwesome)'],
                            'title' => ['type' => 'text', 'label' => 'Titre'],
                            'description' => ['type' => 'textarea', 'label' => 'Description'],
                        ]],
                    ]
                ],
                'cta' => [
                    'type' => 'cta',
                    'label' => 'Appel à l\'action',
                    'description' => 'Section CTA avec texte et bouton',
                    'fields' => [
                        'headline' => ['type' => 'text', 'label' => 'Titre principal'],
                        'description' => ['type' => 'textarea', 'label' => 'Description'],
                        'button_text' => ['type' => 'text', 'label' => 'Texte du bouton'],
                        'button_url' => ['type' => 'url', 'label' => 'URL du bouton'],
                        'background_color' => ['type' => 'color', 'label' => 'Couleur fond'],
                    ]
                ],
                'testimonials' => [
                    'type' => 'testimonials',
                    'label' => 'Témoignages',
                    'description' => 'Section avec témoignages clients',
                    'fields' => [
                        'section_title' => ['type' => 'text', 'label' => 'Titre section'],
                        'items' => ['type' => 'repeater', 'label' => 'Témoignages', 'item_fields' => [
                            'name' => ['type' => 'text', 'label' => 'Nom client'],
                            'role' => ['type' => 'text', 'label' => 'Rôle/Situation'],
                            'text' => ['type' => 'textarea', 'label' => 'Témoignage'],
                            'image' => ['type' => 'image', 'label' => 'Photo'],
                        ]],
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

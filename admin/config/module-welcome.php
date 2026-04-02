<?php
/**
 * Configuration des pages de bienvenue pour chaque module
 * Retourne un array associatif avec les données de chaque module
 */

return [

    // ═══════════════════════════════════════════════
    // MODULE : CONSTRUIRE
    // ═══════════════════════════════════════════════
    'construire' => [
        'title'    => 'Construire',
        'subtitle' => 'Poser les bases solides de votre activité immobilière',
        'icon'     => 'fa-cube',
        'color'    => '#2563EB',  // Bleu
        'gradient' => 'linear-gradient(135deg, #1e3a8a 0%, #2563EB 100%)',
        'dashboard_url' => '/admin/modules/construire/dashboard.php',
        'welcome_url'   => '/admin/modules/construire/welcome.php',

        '3r' => [
            'realite' => [
                'title' => '📊 Réalité',
                'text'  => '80% des agents immobiliers échouent dans les 2 premières années faute de structure.',
                'icon'  => 'fa-chart-line',
                'color' => '#DC2626'
            ],
            'resultat' => [
                'title' => '🎯 Résultat recherché',
                'text'  => 'Une stratégie claire et des fondations inébranlables pour votre activité.',
                'icon'  => 'fa-bullseye',
                'color' => '#16A34A'
            ],
            'risque' => [
                'title' => '⚠️ Risque à éviter',
                'text'  => 'Se lancer sans plan précis = gaspillage de temps, d\'énergie et d\'argent.',
                'icon'  => 'fa-triangle-exclamation',
                'color' => '#D97706'
            ]
        ],

        'mere' => [
            'motivation' => [
                'title' => '💡 Pourquoi c\'est important ?',
                'text'  => 'Sans bases solides, même les meilleures techniques de vente échouent. La structure est la fondation de tout succès durable dans l\'immobilier.',
                'icon'  => 'fa-lightbulb'
            ],
            'explication' => [
                'title' => '📖 Ce qu\'il faut comprendre',
                'text'  => 'Votre activité doit reposer sur une vision claire, des processus définis et une identité de marché. Sans cela, vous naviguez à vue.',
                'icon'  => 'fa-book-open'
            ],
            'resultat' => [
                'title' => '✅ Ce que vous allez obtenir',
                'text'  => 'Un plan d\'action personnalisé, une feuille de route précise et les outils pour mesurer votre progression semaine après semaine.',
                'icon'  => 'fa-circle-check'
            ],
            'exercice' => [
                'title' => '✏️ Exercice rapide (5 minutes)',
                'text'  => 'Prenez une feuille et notez 3 objectifs concrets pour les 6 prochains mois. Soyez précis : "Signer 2 mandats par mois" vaut mieux que "Avoir plus de clients".',
                'icon'  => 'fa-pencil',
                'action_label' => 'Faire l\'exercice maintenant'
            ]
        ],

        'actions' => [
            ['label' => '🚀 Je pars de zéro',            'value' => 'zero',      'url' => '/admin/modules/construire/step-zero.php'],
            ['label' => '📋 J\'ai déjà une stratégie',   'value' => 'strategie', 'url' => '/admin/modules/construire/step-strategie.php'],
            ['label' => '🎯 Je veux affiner ma cible',   'value' => 'cible',     'url' => '/admin/modules/construire/step-cible.php'],
            ['label' => '🔄 Je veux tout reprendre',     'value' => 'reprendre', 'url' => '/admin/modules/construire/step-reprendre.php'],
        ],

        'has_free_field'   => true,
        'free_field_label' => 'Décrivez votre situation actuelle en une phrase',
        'free_field_placeholder' => 'Ex: J\'ai 2 ans d\'expérience mais je manque de clients réguliers...'
    ],

    // ═══════════════════════════════════════════════
    // MODULE : ATTIRER
    // ═══════════════════════════════════════════════
    'attirer' => [
        'title'    => 'Attirer',
        'subtitle' => 'Générer un flux constant de prospects qualifiés',
        'icon'     => 'fa-magnet',
        'color'    => '#7C3AED',
        'gradient' => 'linear-gradient(135deg, #4c1d95 0%, #7C3AED 100%)',
        'dashboard_url' => '/admin/modules/attirer/dashboard.php',
        'welcome_url'   => '/admin/modules/attirer/welcome.php',

        '3r' => [
            'realite' => [
                'title' => '📊 Réalité',
                'text'  => '70% des agents passent plus de temps à chercher des clients qu\'à les servir.',
                'icon'  => 'fa-chart-line',
                'color' => '#DC2626'
            ],
            'resultat' => [
                'title' => '🎯 Résultat recherché',
                'text'  => 'Un système d\'attraction automatique qui vous amène des prospects chaque semaine.',
                'icon'  => 'fa-bullseye',
                'color' => '#16A34A'
            ],
            'risque' => [
                'title' => '⚠️ Risque à éviter',
                'text'  => 'Dépendre uniquement du bouche-à-oreille = revenus imprévisibles et stress permanent.',
                'icon'  => 'fa-triangle-exclamation',
                'color' => '#D97706'
            ]
        ],

        'mere' => [
            'motivation' => [
                'title' => '💡 Pourquoi c\'est important ?',
                'text'  => 'Un agent qui attire naturellement les clients travaille moins dur et gagne plus. L\'attraction remplace la prospection agressive.',
                'icon'  => 'fa-lightbulb'
            ],
            'explication' => [
                'title' => '📖 Ce qu\'il faut comprendre',
                'text'  => 'L\'attraction repose sur votre positionnement, votre visibilité digitale et votre réputation. Ces 3 piliers se construisent méthodiquement.',
                'icon'  => 'fa-book-open'
            ],
            'resultat' => [
                'title' => '✅ Ce que vous allez obtenir',
                'text'  => 'Un pipeline de prospects qualifiés, des outils de capture automatisés et une présence digitale qui travaille pour vous 24h/24.',
                'icon'  => 'fa-circle-check'
            ],
            'exercice' => [
                'title' => '✏️ Exercice rapide (10 minutes)',
                'text'  => 'Identifiez vos 3 derniers clients et notez comment ils vous ont trouvé. Ce simple exercice révèle votre canal d\'attraction principal.',
                'icon'  => 'fa-pencil',
                'action_label' => 'Analyser mes sources clients'
            ]
        ],

        'actions' => [
            ['label' => '📱 Je veux démarrer sur les réseaux', 'value' => 'reseaux',  'url' => '/admin/modules/attirer/step-reseaux.php'],
            ['label' => '🌐 J\'ai déjà une présence en ligne',  'value' => 'online',   'url' => '/admin/modules/attirer/step-online.php'],
            ['label' => '📧 Je veux faire de l\'email',         'value' => 'email',    'url' => '/admin/modules/attirer/step-email.php'],
            ['label' => '🤝 Je préfère le networking physique', 'value' => 'network',  'url' => '/admin/modules/attirer/step-network.php'],
        ],

        'has_free_field'   => true,
        'free_field_label' => 'D\'où viennent vos clients aujourd\'hui ?',
        'free_field_placeholder' => 'Ex: Principalement par recommandation de mes anciens clients...'
    ],

    // ═══════════════════════════════════════════════
    // MODULE : CONVERTIR
    // ═══════════════════════════════════════════════
    'convertir' => [
        'title'    => 'Convertir',
        'subtitle' => 'Transformer vos prospects en clients signés',
        'icon'     => 'fa-handshake',
        'color'    => '#059669',
        'gradient' => 'linear-gradient(135deg, #064e3b 0%, #059669 100%)',
        'dashboard_url' => '/admin/modules/convertir/dashboard.php',
        'welcome_url'   => '/admin/modules/convertir/welcome.php',

        '3r' => [
            'realite' => [
                'title' => '📊 Réalité',
                'text'  => 'Un agent non formé à la conversion perd en moyenne 60% de ses prospects qualifiés.',
                'icon'  => 'fa-chart-line',
                'color' => '#DC2626'
            ],
            'resultat' => [
                'title' => '🎯 Résultat recherché',
                'text'  => 'Un taux de conversion supérieur à 30% avec un process de suivi structuré.',
                'icon'  => 'fa-bullseye',
                'color' => '#16A34A'
            ],
            'risque' => [
                'title' => '⚠️ Risque à éviter',
                'text'  => 'Relancer trop tôt ou trop tard = perdre la confiance et rater la vente définitivement.',
                'icon'  => 'fa-triangle-exclamation',
                'color' => '#D97706'
            ]
        ],

        'mere' => [
            'motivation' => [
                'title' => '💡 Pourquoi c\'est important ?',
                'text'  => 'La conversion est l\'étape où votre chiffre d\'affaires se concrétise. Améliorer ce taux de 10% double souvent vos revenus sans plus de prospects.',
                'icon'  => 'fa-lightbulb'
            ],
            'explication' => [
                'title' => '📖 Ce qu\'il faut comprendre',
                'text'  => 'Convertir n\'est pas "vendre" au sens traditionnel. C\'est accompagner votre prospect vers la meilleure décision pour lui. La confiance est la clé.',
                'icon'  => 'fa-book-open'
            ],
            'resultat' => [
                'title' => '✅ Ce que vous allez obtenir',
                'text'  => 'Des scripts adaptés, un CRM de suivi intelligent et des séquences de relance qui respectent votre prospect tout en maximisant les conversions.',
                'icon'  => 'fa-circle-check'
            ],
            'exercice' => [
                'title' => '✏️ Exercice rapide (15 minutes)',
                'text'  => 'Listez vos 5 derniers prospects non convertis. Pour chacun, identifiez l\'objection principale. Les patterns qui émergent sont vos axes d\'amélioration.',
                'icon'  => 'fa-pencil',
                'action_label' => 'Analyser mes prospects perdus'
            ]
        ],

        'actions' => [
            ['label' => '📞 Améliorer mes appels de découverte', 'value' => 'appels',    'url' => '/admin/modules/convertir/step-appels.php'],
            ['label' => '📝 Créer mes scripts de suivi',         'value' => 'scripts',   'url' => '/admin/modules/convertir/step-scripts.php'],
            ['label' => '🔄 Optimiser mes relances',             'value' => 'relances',  'url' => '/admin/modules/convertir/step-relances.php'],
            ['label' => '📊 Analyser mon pipeline actuel',       'value' => 'pipeline',  'url' => '/admin/modules/convertir/step-pipeline.php'],
        ],

        'has_free_field'   => true,
        'free_field_label' => 'Quel est votre principal blocage à la conversion ?',
        'free_field_placeholder' => 'Ex: Mes prospects disparaissent après le premier rendez-vous...'
    ],

    // ═══════════════════════════════════════════════
    // MODULE : FIDÉLISER
    // ═══════════════════════════════════════════════
    'fideliser' => [
        'title'    => 'Fidéliser',
        'subtitle' => 'Créer une base de clients ambassadeurs',
        'icon'     => 'fa-heart',
        'color'    => '#DC2626',
        'gradient' => 'linear-gradient(135deg, #7f1d1d 0%, #DC2626 100%)',
        'dashboard_url' => '/admin/modules/fideliser/dashboard.php',
        'welcome_url'   => '/admin/modules/fideliser/welcome.php',

        '3r' => [
            'realite' => [
                'title' => '📊 Réalité',
                'text'  => 'Acquérir un nouveau client coûte 5x plus cher que fidéliser un client existant.',
                'icon'  => 'fa-chart-line',
                'color' => '#DC2626'
            ],
            'resultat' => [
                'title' => '🎯 Résultat recherché',
                'text'  => 'Un réseau d\'anciens clients qui vous recommandent activement sans que vous le demandiez.',
                'icon'  => 'fa-bullseye',
                'color' => '#16A34A'
            ],
            'risque' => [
                'title' => '⚠️ Risque à éviter',
                'text'  => 'Disparaître après la signature = zéro recommandation et réputation ternie sur le long terme.',
                'icon'  => 'fa-triangle-exclamation',
                'color' => '#D97706'
            ]
        ],

        'mere' => [
            'motivation' => [
                'title' => '💡 Pourquoi c\'est important ?',
                'text'  => 'Vos meilleurs clients sont vos meilleurs commerciaux. Un système de fidélisation transforme chaque transaction en source de revenus récurrents.',
                'icon'  => 'fa-lightbulb'
            ],
            'explication' => [
                'title' => '📖 Ce qu\'il faut comprendre',
                'text'  => 'La fidélisation commence dès la signature, pas après. L\'expérience client post-vente est le moment le plus important pour créer un ambassadeur.',
                'icon'  => 'fa-book-open'
            ],
            'resultat' => [
                'title' => '✅ Ce que vous allez obtenir',
                'text'  => 'Un programme de suivi clients automatisé, des templates de communication et un système de collecte d\'avis qui booste votre réputation.',
                'icon'  => 'fa-circle-check'
            ],
            'exercice' => [
                'title' => '✏️ Exercice rapide (5 minutes)',
                'text'  => 'Choisissez 3 anciens clients. Envoyez-leur un message personnalisé aujourd\'hui sans rien demander. Observez les réponses dans 48h.',
                'icon'  => 'fa-pencil',
                'action_label' => 'Reconnecter avec mes anciens clients'
            ]
        ],

        'actions' => [
            ['label' => '💌 Créer un programme de suivi',       'value' => 'suivi',     'url' => '/admin/modules/fideliser/step-suivi.php'],
            ['label' => '⭐ Collecter des avis et témoignages', 'value' => 'avis',      'url' => '/admin/modules/fideliser/step-avis.php'],
            ['label' => '🎁 Mettre en place un programme VIP',  'value' => 'vip',       'url' => '/admin/modules/fideliser/step-vip.php'],
            ['label' => '📊 Analyser ma base clients actuelle', 'value' => 'analyse',   'url' => '/admin/modules/fideliser/step-analyse.php'],
        ],

        'has_free_field'   => true,
        'free_field_label' => 'Comment maintenez-vous le contact avec vos anciens clients ?',
        'free_field_placeholder' => 'Ex: Seulement quand ils me contactent pour un nouveau projet...'
    ],

    // ═══════════════════════════════════════════════
    // MODULE : SCALER
    // ═══════════════════════════════════════════════
    'scaler' => [
        'title'    => 'Scaler',
        'subtitle' => 'Automatiser et multiplier vos performances',
        'icon'     => 'fa-rocket',
        'color'    => '#F59E0B',
        'gradient' => 'linear-gradient(135deg, #78350f 0%, #F59E0B 100%)',
        'dashboard_url' => '/admin/modules/scaler/dashboard.php',
        'welcome_url'   => '/admin/modules/scaler/welcome.php',

        '3r' => [
            'realite' => [
                'title' => '📊 Réalité',
                'text'  => 'Les top agents gagnent 10x plus non pas en travaillant 10x plus, mais en automatisant intelligemment.',
                'icon'  => 'fa-chart-line',
                'color' => '#DC2626'
            ],
            'resultat' => [
                'title' => '🎯 Résultat recherché',
                'text'  => 'Un business immobilier qui tourne avec des systèmes, pas uniquement avec votre énergie.',
                'icon'  => 'fa-bullseye',
                'color' => '#16A34A'
            ],
            'risque' => [
                'title' => '⚠️ Risque à éviter',
                'text'  => 'Vouloir tout automatiser trop vite = systèmes bâclés et expérience client dégradée.',
                'icon'  => 'fa-triangle-exclamation',
                'color' => '#D97706'
            ]
        ],

        'mere' => [
            'motivation' => [
                'title' => '💡 Pourquoi c\'est important ?',
                'text'  => 'Scaler votre activité signifie décorréler votre temps de vos revenus. C\'est la liberté financière et professionnelle pour tout agent ambitieux.',
                'icon'  => 'fa-lightbulb'
            ],
            'explication' => [
                'title' => '📖 Ce qu\'il faut comprendre',
                'text'  => 'Scaler nécessite d\'abord des processus stables. On n\'automatise que ce qui fonctionne déjà manuellement. La technologie amplifie, elle ne corrige pas.',
                'icon'  => 'fa-book-open'
            ],
            'resultat' => [
                'title' => '✅ Ce que vous allez obtenir',
                'text'  => 'Des workflows automatisés, des tableaux de bord de performance et une stratégie de délégation pour multiplier votre capacité sans multiplier votre temps.',
                'icon'  => 'fa-circle-check'
            ],
            'exercice' => [
                'title' => '✏️ Exercice rapide (20 minutes)',
                'text'  => 'Listez les 10 tâches répétitives que vous faites chaque semaine. Classez-les de la plus chronophage à la moins importante. Ce sont vos premières cibles d\'automatisation.',
                'icon'  => 'fa-pencil',
                'action_label' => 'Identifier mes tâches à automatiser'
            ]
        ],

        'actions' => [
            ['label' => '🤖 Automatiser ma prospection',       'value' => 'prospection', 'url' => '/admin/modules/scaler/step-prospection.php'],
            ['label' => '📊 Créer mes tableaux de bord',       'value' => 'dashboard',   'url' => '/admin/modules/scaler/step-dashboard.php'],
            ['label' => '👥 Déléguer et créer une équipe',     'value' => 'delegation',  'url' => '/admin/modules/scaler/step-delegation.php'],
            ['label' => '🔗 Connecter mes outils entre eux',   'value' => 'integration', 'url' => '/admin/modules/scaler/step-integration.php'],
            ['label' => '📈 Analyser mes métriques clés',      'value' => 'metriques',   'url' => '/admin/modules/scaler/step-metriques.php'],
        ],

        'has_free_field'   => true,
        'free_field_label' => 'Quelle est votre priorité d\'automatisation numéro 1 ?',
        'free_field_placeholder' => 'Ex: Je veux automatiser mes relances de prospects après un premier contact...'
    ],
];

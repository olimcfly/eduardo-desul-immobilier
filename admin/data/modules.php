<?php
// admin/data/modules.php
// Liste de modules — utilisé par sidebar et hub
return [
    // SECTION : Construire
    'construire' => [
        'slug' => 'construire',
        'title' => 'MODULE 1 : CONSTRUIRE',
        'section' => 'Construire',
        'route' => '/admin/index.php?module=construire',
        'icon' => 'fa-lightbulb',
        'description' => "Pose les fondations marketing qui attirent les bons clients sans gaspiller des mois.",
        'available' => true,
        'hub_order' => 10,
        'meta' => [
            'motivation' => "Tu travailles dur. Tu publies, tu prospectes, tu rappelles. Mais à la fin du mois, tu ne sais pas vraiment d'où viennent tes clients — et tu ne sais pas comment en avoir plus. Tu recommences à zéro chaque mois. C'est épuisant, et c'est évitable.",
            'explanation' => "Le vrai problème n'est pas le manque de travail — c'est l'absence de système. Sans persona défini, tu parles à tout le monde et tu ne touches personne. Sans positionnement clair, tu te noies dans la masse des agents qui font la même chose. Un écosystème marketing commence par une seule question : pour qui es-tu la meilleure option, et pourquoi ?",
            'recipe' => [
                'Définis ton persona principal (âge, situation, peur, désir, objectif).',
                'Formule ton positionnement en une phrase (je suis le spécialiste de [X] pour [Y] à [Z]).',
                "Identifie les 3 problèmes que tu résous mieux que n'importe qui.",
                'Choisis ton territoire géographique et thématique.',
                'Crée ta ligne éditoriale en 5 sujets récurrents.',
            ],
            'exercise' => "Écris en 3 phrases : qui tu aides, quel est leur problème n°1, et pourquoi ils devraient te choisir toi plutôt qu'un autre. Si tu butes, c'est ton système qui manque — pas ton talent.",
        ],
    ],

    // SECTION : Attirer
    'attirer' => [
        'slug' => 'attirer',
        'title' => 'MODULE 2 : ATTIRER',
        'section' => 'Attirer',
        'route' => '/admin/index.php?module=attirer',
        'icon' => 'fa-bullseye',
        'description' => "Attire des prospects qualifiés en continu — sans payer de la pub ou courir après les gens.",
        'available' => true,
        'hub_order' => 20,
        'meta' => [
            'motivation' => "Tu passes du temps à créer du contenu. Tu publies sur Instagram, tu fais des vidéos, tu écris des posts. Résultat : des likes, quelques commentaires, et... le silence. Personne ne te contacte. Tu te demandes si ça sert à quelque chose. Ça sert — mais tu vises mal.",
            'explanation' => "Attirer, ce n'est pas publier beaucoup. C'est publier ce que ton persona cherche activement. La différence entre un post qui décore et un post qui génère un lead, c'est l'intention. Le contenu SEO répond à une question précise que quelqu'un tape sur Google à 23h. Le contenu réseau social crée une relation sur la durée.",
            'recipe' => [
                'Définis 5 types de contenus récurrents (conseil, marché, témoignage, FAQ, actualité).',
                "Crée un prompt personnalisé pour chaque type avec ton style et ton persona.",
                "Génère un mois de contenu en 2h chaque 1er du mois.",
                "Relecture + personnalisation : 30 min pour tout valider.",
                "Programme tout avec un outil de publication automatique.",
            ],
            'exercise' => "Tape ce prompt dans ChatGPT : « Tu es un agent immobilier expert à Bordeaux. Rédige un post LinkedIn de 150 mots pour des propriétaires qui hésitent à vendre, en expliquant pourquoi le marché actuel est favorable. Ton style est direct, bienveillant, et orienté conseil. » Adapte et publie-le aujourd'hui.",
        ],
    ],

    // SECTION : Capturer
    'capturer' => [
        'slug' => 'capturer',
        'title' => 'MODULE 3 : CAPTURER',
        'section' => 'Capturer',
        'route' => '/admin/index.php?module=capturer',
        'icon' => 'fa-magnet',
        'description' => "Convertis les visiteurs en prospects en automatisant la capture et la qualification.",
        'available' => true,
        'hub_order' => 30,
        'meta' => [
            'motivation' => "Tes contenus attirent, mais trop peu de visiteurs laissent leurs coordonnées. Sans système de capture, tu dépends du hasard et tu rates des opportunités récurrentes.",
            'explanation' => "Capturer, c'est créer des points de conversion clairs et simples : formulaires courts, lead magnets pertinents, CTA visibles et pages dédiées. L'objectif n'est pas de tout capturer, mais de capturer les bonnes personnes avec un coût d'effort minimal.",
            'recipe' => [
                "Crée 3 lead magnets ultra-ciblés (guide quartier, checklist vente, simulateur de prix).",
                "Réduis ton formulaire à 2–3 champs essentiels (nom, téléphone/email, besoin).",
                "Déploie une landing page optimisée pour mobile et A/B teste le titre.",
                "Installe un suivi UTM + évènements pour savoir d'où viennent tes leads.",
                "Automatise l'envoi d'un email/sms de bienvenue + qualification en 24h.",
            ],
            'exercise' => "Crée maintenant un mini-lead magnet : un PDF 1 page '5 erreurs qui font perdre 10% du prix' pour ton secteur. Mets-le derrière un formulaire (nom + email) et partage le lien dans ton prochain post. Mesure les inscriptions après 48h.",
        ],
    ],

    // SECTION : Convertir
    'convertir' => [
        'slug' => 'convertir',
        'title' => 'MODULE 4 : CONVERTIR',
        'section' => 'Convertir',
        'route' => '/admin/index.php?module=convertir',
        'icon' => 'fa-handshake',
        'description' => "Améliore ton taux de transformation grâce à des scénarios de contact, argumentaires et scripts de vente.",
        'available' => true,
        'hub_order' => 40,
        'meta' => [
            'motivation' => "Tu as des leads mais beaucoup s'éteignent après le premier contact. Des rendez-vous manqués, des objections mal traitées, une perte de confiance — tout cela finit par baisser ton taux de signature.",
            'explanation' => "Convertir, c'est avoir un process reproductible : qualification rapide, script d'entretien, gestion des objections et proposition claire de valeur. Les meilleurs taux viennent d'équipes (ou d'agents) qui répètent une séquence optimisée.",
            'recipe' => [
                "Définis un script d'appel en 5 étapes : intro, qualification, valeur, objection, RDV/CTA.",
                "Prépare 10 réponses aux objections courantes (prix, timing, comparaison).",
                "Standardise le follow-up : 3 relances multicanales sur 10 jours.",
                "Crée des templates d'email pour chaque étape (confirmation, suivi, proposition).",
                "Forme-toi ou ton équipe 1x/mois sur la pratique des scripts (roleplay).",
            ],
            'exercise' => "Rédige un script d'appel de 60 secondes : présentation, 2 questions de qualification, valeur clé, proposition de RDV. Enregistre-toi en le lisant, puis fais 3 appels tests cette semaine en suivant strictement le script.",
        ],
    ],

    // SECTION : Optimiser
    'optimiser' => [
        'slug' => 'optimiser',
        'title' => 'MODULE 5 : OPTIMISER',
        'section' => 'Optimiser',
        'route' => '/admin/index.php?module=optimiser',
        'icon' => 'fa-chart-line',
        'description' => "Mesure et améliore la performance (trafic, conversions, SEO technique).",
        'available' => true,
        'hub_order' => 50,
        'meta' => [
            'motivation' => "Tu publies et tu transformes parfois, mais tu n'as pas de tableau de bord clair. Sans données actionnables, tu répliques des actions chanceuses au lieu d'améliorer ce qui marche.",
            'explanation' => "Optimiser, c'est mettre en place des KPIs simples (trafic qualifié, taux de conversion lead, taux de RDV signé) et des routines d'analyse. Les petites optimisations (titre de page, formulaire, timing d'email) ont un effet direct et mesurable sur le business.",
            'recipe' => [
                "Définis 5 KPIs prioritaires et crée un dashboard simple (Google Sheets / Data Studio).",
                "Installe suivi Google Analytics + events (lead submit, RDV pris, clics CTA).",
                "Analyse 1 conversion perdue par semaine : parcours, friction, correction.",
                "Fais un test A/B par mois (titre, CTA, longueur du formulaire).",
                "Planifie une revue de performance mensuelle avec actions concrètes (3 actions).",
            ],
            'exercise' => "Configure un tableau simple : nombre de visiteurs, leads générés, taux de conversion, RDV pris. Remplis les 30 derniers jours et identifie 1 point de friction à corriger ce mois-ci.",
        ],
    ],

    // SECTION : Fidéliser
    'fideliser' => [
        'slug' => 'fideliser',
        'title' => 'MODULE 6 : FIDÉLISER',
        'section' => 'Fidéliser',
        'route' => '/admin/index.php?module=fideliser',
        'icon' => 'fa-heart',
        'description' => "Transforme clients en ambassadeurs par des process de suivi et de recommandation.",
        'available' => true,
        'hub_order' => 60,
        'meta' => [
            'motivation' => "Signer une transaction, c'est bien. Obtenir des recommandations et des retours réguliers, c'est multiplier ton acquisition sans coût publicitaire. Beaucoup d'agents s'arrêtent après la vente — tu peux faire mieux.",
            'explanation' => "Fidéliser, c'est automatiser les petits gestes qui créent de la valeur sur le long terme : onboarding client, suivi post-vente, demande d'avis et programme de parrainage. Ces actions augmentent la rétention et créent des introductions qualifiées.",
            'recipe' => [
                "Crée un workflow post-vente : message de remerciement, guide de suivi, check-in à 30/90 jours.",
                "Automatise la demande d'avis (Google/Pages) après livraison/acte.",
                "Mets en place un programme de parrainage simple (récompense claire).",
                "Garde une base contact segmentée pour envoi de contenus utiles (anniversaires, nouveautés quartier).",
                "Planifie 2 actions de valeur par an pour anciens clients (info marché, bilan).",
            ],
            'exercise' => "Rédige le message de remerciement automatisé à envoyer 48h après la signature et crée une checklist '30 jours après' (visuel, contact, conseils). Déploie l'automatisation cette semaine.",
        ],
    ],

    // SECTION : Assistant IA
    'assistant_ia' => [
        'slug' => 'assistant_ia',
        'title' => 'MODULE 7 : ASSISTANT IA',
        'section' => 'Assistant IA',
        'route' => '/admin/index.php?module=assistant_ia',
        'icon' => 'fa-robot',
        'description' => "Outils IA pour création de contenu, prompts, automatisation et estimation rapide.",
        'available' => true,
        'hub_order' => 70,
        'meta' => [
            'motivation' => '',
            'explanation' => '',
            'recipe' => [],
            'exercise' => '',
        ],
    ],

    // MODULE : Estimation IA (module spécialisé)
    'estimation_ia' => [
        'slug' => 'estimation_ia',
        'title' => 'MODULE ESTIMATION : ANALYSE IA',
        'section' => 'Outils',
        'route' => '/admin/index.php?module=estimation_ia',
        'icon' => 'fa-calculator',
        'description' => "Génère une estimation immobilière intelligente sous forme de fourchette de prix avec analyse pédagogique.",
        'available' => true,
        'hub_order' => 80,
        'meta' => [
            'role' => "Tu es un expert immobilier local, spécialisé dans l'analyse de marché et l'estimation de biens.",
            'objective' => "Générer une estimation immobilière intelligente sous forme de fourchette de prix, avec analyse pédagogique et transition orientée conversion.",
            'inputs' => [
                'Ville : {ville}',
                'Type de bien : {type_bien}',
                'Superficie : {surface} m²',
                'Budget estimé par le propriétaire : {budget}',
            ],
            'method' => [
                'Prendre en compte le prix moyen au m² de la ville.',
                'Adapter selon le type de bien et la surface.',
                'Créer une fourchette réaliste avec variation de ±10 à 20%.',
                'Rester pédagogique et ne jamais promettre une fiabilité à 100%.',
            ],
            'output' => [
                '🏠 ESTIMATION : prix bas, prix moyen, prix haut + prix au m².',
                '📍 ANALYSE DU MARCHÉ : tendance, niveau de demande, biens recherchés.',
                '🧠 EXPLICATION : limites de l’automatique + contexte expert.',
                '🎯 RECOMMANDATION : 2 à 3 conseils actionnables.',
                '🚀 TRANSITION : téléchargement, rapport détaillé, prise de RDV.',
            ],
            'required_messages' => [
                'Dans certains cas (succession, divorce, vente officielle), seule une expertise immobilière réalisée par un professionnel est reconnue.',
                "La vraie valeur d’un bien se construit toujours entre un vendeur et un acheteur.",
            ],
            'style' => 'Clair, pédagogique, rassurant, professionnel et accessible.',
        ],
    ],
];

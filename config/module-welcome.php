<?php
/**
 * Configuration des pages d'accueil module (welcome).
 */
return [
    'launchpad' => [
        'title' => 'Construire votre fondation',
        'subtitle' => 'Posez les bases de votre activité avant de déployer vos outils.',
        'three_r' => [
            ['title' => 'Réalité', 'text' => 'Sans fondation claire, chaque action marketing part dans tous les sens.'],
            ['title' => 'Résultat recherché', 'text' => 'Avoir un plan simple, priorisé et actionnable dès cette semaine.'],
            ['title' => 'Risque à éviter', 'text' => 'Accumuler des outils sans stratégie concrète.'],
        ],
        'mere' => [
            ['title' => 'Motivation', 'text' => 'Une base solide réduit le stress et accélère les décisions.'],
            ['title' => 'Explication', 'text' => 'Ce module vous aide à définir cible, offre et ordre des actions.'],
            ['title' => 'Résultat', 'text' => 'Vous repartez avec une trajectoire claire pour vos 30 prochains jours.'],
            ['title' => 'Exercice', 'text' => 'Choisissez votre priorité du moment avant de continuer.'],
        ],
        'choices' => [
            ['id' => 'zero', 'label' => 'Je pars de zéro', 'hint' => 'Je veux structurer toute ma base.'],
            ['id' => 'existing', 'label' => 'J’ai déjà quelque chose', 'hint' => 'Je veux organiser ce qui existe.'],
            ['id' => 'improve', 'label' => 'Je veux améliorer', 'hint' => 'Je cherche un plan plus efficace.'],
            ['id' => 'fast', 'label' => 'Je veux aller vite', 'hint' => 'Donnez-moi un ordre d’action rapide.'],
        ],
        'free_text' => true,
    ],
    'seo' => [
        'title' => 'Attirer du trafic qualifié',
        'subtitle' => 'Transformez votre visibilité locale en demandes concrètes.',
        'three_r' => [
            ['title' => 'Réalité', 'text' => 'Les vendeurs recherchent déjà des réponses locales sur Google.'],
            ['title' => 'Résultat recherché', 'text' => 'Générer un flux régulier de visiteurs qualifiés.'],
            ['title' => 'Risque à éviter', 'text' => 'Publier sans intention ni ciblage local précis.'],
        ],
        'mere' => [
            ['title' => 'Motivation', 'text' => 'Le trafic organique diminue votre dépendance aux annonces payantes.'],
            ['title' => 'Explication', 'text' => 'Ce module aligne mots-clés, pages et optimisation terrain.'],
            ['title' => 'Résultat', 'text' => 'Vous obtenez une base SEO locale plus lisible et performante.'],
            ['title' => 'Exercice', 'text' => 'Sélectionnez un objectif trafic principal avant de démarrer.'],
        ],
        'choices' => [
            ['id' => 'audit', 'label' => 'Je fais un audit rapide', 'hint' => 'Voir les priorités immédiatement.'],
            ['id' => 'content', 'label' => 'J’ai déjà du contenu', 'hint' => 'Optimiser l’existant en premier.'],
            ['id' => 'boost', 'label' => 'Je veux améliorer', 'hint' => 'Monter en visibilité locale.'],
            ['id' => 'quick', 'label' => 'Je veux aller vite', 'hint' => 'Plan d’actions essentielles en 15 min.'],
        ],
        'free_text' => true,
    ],
];

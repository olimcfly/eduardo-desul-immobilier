<?php

return [
    'google' => [
        'rules' => [
            'Pas de keyword stuffing',
            'Contenu utile et local',
            'Ton naturel et humain',
        ],
        'forbidden' => [
            '100%',
            'garanti',
            'meilleur',
        ],
    ],
    'facebook' => [
        'rules' => [
            'Prioriser le storytelling et l\'interaction',
            'Ton conversationnel et authentique',
            'Ajouter une question ouverte pour générer des commentaires',
        ],
        'forbidden' => [
            'spam',
            'clickbait trompeur',
            'promesses irréalistes',
        ],
    ],
    'tiktok' => [
        'rules' => [
            'Hook clair dans les 3 premières secondes',
            'Format court, dynamique et orienté valeur',
            'Langage simple, direct et incarné',
        ],
        'forbidden' => [
            'intro longue',
            'jargon complexe',
            'promesses non vérifiables',
        ],
    ],
    'linkedin' => [
        'rules' => [
            'Positionnement expert et pédagogique',
            'Argumentation structurée et chiffrée si possible',
            'CTA professionnel discret',
        ],
        'forbidden' => [
            'ton agressif',
            'superlatifs excessifs',
            'hashtags hors sujet',
        ],
    ],
    'default' => [
        'rules' => [
            'Précision, clarté, utilité',
            'Ton humain et professionnel',
            'Respect strict du contexte local immobilier',
        ],
        'forbidden' => [
            'allégations non vérifiées',
            'promesses garanties',
            'expressions manipulatoires',
        ],
    ],
];

<?php

declare(strict_types=1);

function renderHelpVideosPage(HelpCenterService $service): void
{
    $apiVideos = [
        [
            'id' => 'api_openai',
            'title' => 'Configurer OpenAI',
            'description' => 'Connectez votre clé API OpenAI pour générer du contenu automatiquement avec l'IA.',
            'icon' => '🤖',
            'category' => 'ia',
        ],
        [
            'id' => 'api_google_maps',
            'title' => 'Intégrer Google Maps',
            'description' => 'Ajoutez les cartes interactives et géolocalisez vos propriétés immobilières.',
            'icon' => '🗺️',
            'category' => 'geolocalisation',
        ],
        [
            'id' => 'api_google_psi',
            'title' => 'Analyser les performances avec PageSpeed Insights',
            'description' => 'Mesurez la vitesse de votre site et obtenez des recommandations d\'optimisation.',
            'icon' => '⚡',
            'category' => 'performance',
        ],
        [
            'id' => 'api_gsc',
            'title' => 'Connecter Google Search Console',
            'description' => 'Suivez vos positions SEO et les requêtes de recherche qui vous ramènent du trafic.',
            'icon' => '🔍',
            'category' => 'seo',
        ],
        [
            'id' => 'api_gmb',
            'title' => 'Synchroniser Google My Business',
            'description' => 'Gérez votre fiche locale GMB directement depuis le dashboard.',
            'icon' => '📍',
            'category' => 'local',
        ],
        [
            'id' => 'api_facebook',
            'title' => 'Connecter Facebook Business',
            'description' => 'Publiez et pilotez vos campagnes Facebook depuis le CMS.',
            'icon' => 'f',
            'category' => 'social',
        ],
        [
            'id' => 'api_instagram',
            'title' => 'Intégrer Instagram',
            'description' => 'Publiez vos propriétés sur Instagram et suivez l\'engagement.',
            'icon' => '📸',
            'category' => 'social',
        ],
        [
            'id' => 'api_cloudinary',
            'title' => 'Optimiser les images avec Cloudinary',
            'description' => 'Compressez et livrez vos images automatiquement dans les formats optimisés.',
            'icon' => '🖼️',
            'category' => 'medias',
        ],
        [
            'id' => 'api_dataforseo',
            'title' => 'Analyser la concurrence avec DataForSEO',
            'description' => 'Comparez votre SEO avec vos concurrents locaux et nationaux.',
            'icon' => '📊',
            'category' => 'seo',
        ],
        [
            'id' => 'api_telegram',
            'title' => 'Mettre en place le Bot Telegram',
            'description' => 'Gérez votre site immobilier directement depuis Telegram sur votre téléphone.',
            'icon' => '💬',
            'category' => 'automation',
        ],
    ];
    ?>
    <style>
        .videos-page { display: grid; gap: 28px; }
        .videos-hero { background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%); border-radius: 16px; padding: 24px 20px; color: #fff; box-shadow: 0 4px 20px rgba(15, 34, 55, .18); }
        .videos-hero h1 { margin: 0 0 10px; font-size: clamp(24px, 4vw, 30px); line-height: 1.24; }
        .videos-hero p { margin: 0; color: rgba(255,255,255,.78); font-size: 15px; line-height: 1.65; }
        .videos-grid { display: grid; grid-template-columns: 1fr; gap: 14px; }
        @media (min-width: 768px) { .videos-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 1024px) { .videos-grid { grid-template-columns: repeat(3, 1fr); } }
        .video-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 8px rgba(15,23,42,.08); border: 1px solid #e2e8f0; transition: transform .18s ease, box-shadow .18s ease; display: flex; flex-direction: column; }
        .video-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(15,23,42,.10); }
        .video-thumbnail { background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%); aspect-ratio: 16/9; display: flex; align-items: center; justify-content: center; font-size: 3rem; position: relative; }
        .video-thumbnail::before { content: '▶'; position: absolute; width: 60px; height: 60px; border-radius: 50%; background: rgba(255,255,255,.85); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: #2563eb; }
        .video-content { padding: 14px 14px; flex: 1; display: flex; flex-direction: column; }
        .video-category { font-size: 11px; font-weight: 700; color: #666; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
        .video-title { margin: 0 0 6px; font-size: 15px; color: #0f172a; font-weight: 600; line-height: 1.4; }
        .video-description { margin: 0; font-size: 13px; color: #475569; line-height: 1.5; flex: 1; }
        .video-status { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; background: #fef3c7; color: #92400e; padding: 4px 8px; border-radius: 4px; margin-top: 10px; width: max-content; }
        @media (min-width: 768px) { .videos-hero { padding: 2rem 2.1rem; } .videos-hero h1 { font-size: 2rem; } }
    </style>

    <section class="videos-page">
        <header class="videos-hero">
            <h1>📹 Vidéos de Démonstration</h1>
            <p>Apprenez à configurer chaque API avec des tutoriels visuels.</p>
        </header>

        <div class="videos-grid">
            <?php foreach ($apiVideos as $video): ?>
            <div class="video-card">
                <div class="video-thumbnail"><?= htmlspecialchars($video['icon']) ?></div>
                <div class="video-content">
                    <span class="video-category"><?= htmlspecialchars($video['category']) ?></span>
                    <h3 class="video-title"><?= htmlspecialchars($video['title']) ?></h3>
                    <p class="video-description"><?= htmlspecialchars($video['description']) ?></p>
                    <span class="video-status">
                        <i class="fas fa-clock"></i> Vidéo en préparation
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}

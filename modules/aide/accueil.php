<?php

declare(strict_types=1);

require_once __DIR__ . '/service.php';
require_once __DIR__ . '/index.php';
require_once __DIR__ . '/article.php';
require_once __DIR__ . '/api.php';
require_once __DIR__ . '/videos.php';

$pageTitle = 'Centre d’aide intelligent';
$pageDescription = 'Aide contextuelle, recherche et recommandations par module.';

function renderContent(): void
{
    $action = preg_replace('/[^a-z-]/', '', (string) ($_GET['action'] ?? 'index'));
    $context = preg_replace('/[^a-z0-9_-]/', '', mb_strtolower((string) ($_GET['context'] ?? '')));

    $service = new HelpCenterService(db());

    if ($action === 'api') {
        handleHelpApi($service, $context);
        return;
    }

    if ($action === 'article') {
        renderHelpArticlePage($service, $context);
        return;
    }

    if ($action === 'videos') {
        renderHelpVideosPage($service);
        return;
    }

    if ($action === 'index') {
        ?>
        <style>
            .help-page { display: grid; gap: 22px; }
            .help-hero { background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%); border-radius: 16px; padding: 24px 20px; color: #fff; box-shadow: 0 4px 20px rgba(15, 34, 55, .18); }
            .help-hero h1 { margin: 0 0 10px; font-size: clamp(24px, 4vw, 30px); line-height: 1.24; color: #fff; }
            .help-hero p { margin: 0; color: rgba(255,255,255,.78); font-size: 15px; line-height: 1.65; }
            .help-modules { display: grid; grid-template-columns: 1fr; gap: 12px; }
            @media (min-width: 768px) { .help-modules { grid-template-columns: repeat(2, 1fr); } }
            .help-card { background: #fff; border-radius: 16px; padding: 18px; box-shadow: 0 1px 8px rgba(15,23,42,.08); border: 1px solid #e2e8f0; transition: transform .18s ease, box-shadow .18s ease; text-decoration: none; color: inherit; }
            .help-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(15,23,42,.10); }
            .help-card-head { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
            .help-card-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
            .help-card h3 { margin: 0; font-size: 16px; color: #0f172a; font-weight: 600; }
            .help-card p { margin: 0; font-size: 14px; color: #475569; line-height: 1.6; }
            .help-action { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: #2563eb; margin-top: 8px; }
            .help-final-cta { background: #fff; border: 1px solid #e8edf4; border-radius: 14px; padding: 1.05rem 1rem; display: grid; gap: .7rem; }
            .help-final-cta h2 { margin: 0; font-size: 1.2rem; color: #111827; font-weight: 700; }
            .help-btn { display: inline-flex; align-items: center; gap: .5rem; width: max-content; text-decoration: none; background: #c9a84c; color: #10253c; font-weight: 700; border-radius: 10px; padding: .58rem .92rem; margin-top: .7rem; }
            @media (min-width: 768px) { .help-hero { padding: 2rem 2.1rem; } .help-hero h1 { font-size: 2rem; } }
        </style>

        <section class="help-page">
            <header class="help-hero">
                <h1>Centre d’aide intelligent</h1>
                <p>Trouvez rapidement les bonnes actions selon votre module actif.</p>
            </header>

            <div class="help-modules">
                <a href="/admin/?module=aide&action=search" class="help-card">
                    <div class="help-card-head">
                        <div class="help-card-icon" style="background:#eafaf1;color:#16a34a;"><i class="fas fa-search"></i></div>
                        <h3>Rechercher</h3>
                    </div>
                    <p>Explorez la base de connaissance avec un moteur de recherche puissant.</p>
                    <span class="help-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
                </a>

                <a href="/admin/?module=aide&action=categories" class="help-card">
                    <div class="help-card-head">
                        <div class="help-card-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-layer-group"></i></div>
                        <h3>Catégories</h3>
                    </div>
                    <p>Parcourez les articles organisés par thème et module.</p>
                    <span class="help-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
                </a>

                <a href="/admin/?module=aide&action=context" class="help-card">
                    <div class="help-card-head">
                        <div class="help-card-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-compass"></i></div>
                        <h3>Aide contextuelle</h3>
                    </div>
                    <p>Aide automatique adaptée à votre module actuel.</p>
                    <span class="help-action"><i class="fas fa-arrow-right"></i> Voir</span>
                </a>

                <a href="/admin/?module=aide&action=videos" class="help-card">
                    <div class="help-card-head">
                        <div class="help-card-icon" style="background:#fdedec;color:#dc2626;"><i class="fas fa-video"></i></div>
                        <h3>Vidéos Tutorials</h3>
                    </div>
                    <p>Démonstrations visuelles pour configurer vos APIs.</p>
                    <span class="help-action"><i class="fas fa-arrow-right"></i> Voir</span>
                </a>
            </div>

            <section class="help-final-cta">
                <div>
                    <h2>Besoin d’aide?</h2>
                    <p>Commencez par la recherche ou explorez les catégories disponibles.</p>
                </div>
                <a href="/admin/?module=aide&action=search" class="help-btn"><i class="fas fa-rocket"></i> Démarrer</a>
            </section>
        </section>
        <?php
        return;
    }

    renderHelpIndexPage($service, $context);
}

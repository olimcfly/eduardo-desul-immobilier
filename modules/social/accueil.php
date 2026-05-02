<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/_bootstrap.php';
require_once __DIR__ . '/repositories/SequenceRepository.php';
require_once __DIR__ . '/repositories/PostRepository.php';
require_once __DIR__ . '/services/StrategyService.php';
require_once __DIR__ . '/services/PublishService.php';
require_once __DIR__ . '/services/SchedulerService.php';
require_once __DIR__ . '/services/SequenceService.php';
require_once __DIR__ . '/services/MediaService.php';
require_once __DIR__ . '/controllers/SequenceController.php';
require_once __DIR__ . '/controllers/PostController.php';
require_once __DIR__ . '/controllers/SocialController.php';

$pageTitle = 'Social';
$pageDescription = 'Séquences, publication et journal social';

$sequenceRepository = new SequenceRepository(db());
$postRepository = new PostRepository(db());
$strategyService = new StrategyService();
$sequenceController = new SequenceController($sequenceRepository, $postRepository);
$postController = new PostController($postRepository, $strategyService);
$socialController = new SocialController($sequenceRepository, $postRepository, $strategyService);

$action = preg_replace('/[^a-z-]/', '', (string) ($_GET['action'] ?? 'index'));
$allowedActions = ['index', 'sequences', 'journal', 'post', 'post-form', 'kit', 'save-sequence', 'save-post', 'delete-post', 'toggle-sequence', 'duplicate-sequence'];
if (!in_array($action, $allowedActions, true)) {
    $action = 'index';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (in_array($action, ['save-sequence', 'toggle-sequence', 'duplicate-sequence'], true)) {
        $sequenceController->handle($action);
    }
    if (in_array($action, ['save-post', 'delete-post'], true)) {
        $postController->handle($action);
    }
}

require_once __DIR__ . '/../../admin/views/layout.php';

function renderSocialHub(): void
{
    ?>
    <section class="hub-page">

        <header class="hub-hero">
            <div class="hub-hero-badge"><i class="fas fa-share-nodes"></i> Réseaux sociaux</div>
            <h1>Publiez régulièrement pour rester visible</h1>
            <p>Planifiez vos contenus, automatisez vos séquences et entretenez la relation avec votre audience locale.</p>
        </header>

        <div class="hub-modules-grid">
            <a href="?module=social&action=sequences" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#eafaf1;color:#16a34a;"><i class="fas fa-layer-group"></i></div>
                    <h3>Séquences</h3>
                </div>
                <p>Planifiez des séries de posts automatiques sur vos réseaux.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
            </a>

            <a href="?module=social&action=journal" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-newspaper"></i></div>
                    <h3>Journal</h3>
                </div>
                <p>Suivez tous vos posts publiés et leur historique.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
            </a>

            <a href="?module=social&action=post-form" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-pen-to-square"></i></div>
                    <h3>Nouveau post</h3>
                </div>
                <p>Rédigez et planifiez un post en quelques secondes.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Créer</span>
            </a>

            <a href="?module=social&action=kit" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-toolbox"></i></div>
                    <h3>Kit de contenus</h3>
                </div>
                <p>Accédez à des modèles et inspirations pour vos publications.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
            </a>
        </div>

        <section class="hub-final-cta" aria-label="Progression social">
            <div>
                <h2>Progression : Séquences → Posts → Journal → Amplifier</h2>
                <p>Commencez par une séquence, puis publiez régulièrement.</p>
            </div>
            <a href="?module=social&action=sequences" class="hub-btn hub-btn--gold"><i class="fas fa-rocket"></i> Créer ma première séquence</a>
        </section>

    </section>
    <?php
}

function renderContent(): void
{
    global $socialController, $action;

    $cssPath = __DIR__ . '/assets/social.css';
    if (is_file($cssPath)) {
        echo '<style data-social-inline="1">' . file_get_contents($cssPath) . '</style>';
    }

    switch ($action) {
        case 'journal':
            $socialController->journal();
            break;
        case 'post':
            $socialController->postDetail((int) ($_GET['id'] ?? 0));
            break;
        case 'post-form':
            $socialController->postForm((int) ($_GET['id'] ?? 0));
            break;
        case 'kit':
            require __DIR__ . '/views/kit.php';
            break;
        case 'sequences':
            $socialController->sequences();
            break;
        default:
            renderSocialHub();
            break;
    }

    $jsPath = __DIR__ . '/assets/social.js';
    if (is_file($jsPath)) {
        echo '<script>' . file_get_contents($jsPath) . '</script>';
    }
}

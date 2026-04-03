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

$action = preg_replace('/[^a-z-]/', '', (string) ($_GET['action'] ?? 'sequences'));
$allowedActions = ['index', 'sequences', 'journal', 'post', 'post-form', 'save-sequence', 'save-post', 'delete-post', 'toggle-sequence', 'duplicate-sequence'];
if (!in_array($action, $allowedActions, true)) {
    $action = 'sequences';
}

if ($action === 'index') {
    $action = 'sequences';
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

function renderContent(): void
{
    global $socialController, $action;

    echo '<link rel="stylesheet" href="/modules/social/assets/social.css?v=' . (int) @filemtime(__DIR__ . '/assets/social.css') . '">';

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
        default:
            $socialController->sequences();
            break;
    }

    echo '<script src="/modules/social/assets/social.js?v=' . (int) @filemtime(__DIR__ . '/assets/social.js') . '"></script>';
}

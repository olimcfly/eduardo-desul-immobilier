<?php

declare(strict_types=1);

require_once __DIR__ . '/service.php';
require_once __DIR__ . '/index.php';
require_once __DIR__ . '/article.php';
require_once __DIR__ . '/api.php';

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

    renderHelpIndexPage($service, $context);
}

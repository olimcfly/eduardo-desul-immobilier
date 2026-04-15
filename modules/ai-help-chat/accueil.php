<?php

declare(strict_types=1);

require_once __DIR__ . '/permissions.php';
require_once __DIR__ . '/service.php';
require_once __DIR__ . '/api.php';
require_once __DIR__ . '/widget.php';
require_once __DIR__ . '/admin-settings.php';
require_once __DIR__ . '/index.php';

$pageTitle = 'Chat d’aide IA';
$pageDescription = 'Assistant contextuel interne connecté aux ressources CRM.';

function renderContent(): void
{
    $service = new AiHelpChatService(db());
    $action = preg_replace('/[^a-z_]/', '', (string) ($_GET['action'] ?? 'index'));

    if ($action === 'api') {
        handleAiHelpChatApi($service);
        return;
    }

    if ($action === 'settings') {
        renderAiHelpChatAdminSettings($service);
        return;
    }

    renderAiHelpChatIndex($service);
}

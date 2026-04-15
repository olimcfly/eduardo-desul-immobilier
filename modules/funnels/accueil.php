<?php
// modules/funnels/accueil.php

require_once MODULES_PATH . '/funnels/services/FunnelService.php';

function renderContent(): string
{
    $db      = \Database::getInstance();
    $service = new FunnelService($db);

    $action = $_GET['action'] ?? 'list';
    $id     = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    switch ($action) {
        case 'wizard':
            return renderWizard($service, $id);
        case 'edit':
            return renderEdit($service, $id);
        case 'stats':
            return renderStats($service, $id);
        default:
            return renderList($service);
    }
}

function renderList(FunnelService $service): string
{
    $filters = [
        'status' => $_GET['status'] ?? '',
        'canal'  => $_GET['canal'] ?? '',
        'ville'  => $_GET['ville'] ?? '',
    ];

    $funnels = $service->getAll(array_filter($filters));
    $canaux  = FunnelService::CANAUX;

    ob_start();
    require __DIR__ . '/views/list.php';
    return ob_get_clean();
}

function renderWizard(FunnelService $service, int $id = 0): string
{
    $step     = (int) ($_GET['step'] ?? 1);
    $funnel   = $id ? $service->getById($id) : [];
    $canaux   = FunnelService::CANAUX;
    $templates = FunnelService::TEMPLATES;

    $viewFile = __DIR__ . "/views/wizard/step{$step}_" . match($step) {
        1 => 'canal',
        2 => 'template',
        3 => 'config',
        4 => 'preview',
        default => 'canal',
    } . '.php';

    if (!file_exists($viewFile)) {
        $viewFile = __DIR__ . '/views/wizard/step1_canal.php';
    }

    ob_start();
    require $viewFile;
    return ob_get_clean();
}

function renderEdit(FunnelService $service, int $id): string
{
    $funnel = $service->getById($id);
    if (!$funnel) {
        return '<div class="alert alert-danger">Funnel introuvable.</div>';
    }

    $canaux    = FunnelService::CANAUX;
    $templates = FunnelService::TEMPLATES;
    $sequences = (new \SequenceCrmService(\Database::getInstance()))->getAllSequences();

    ob_start();
    require __DIR__ . '/views/edit.php';
    return ob_get_clean();
}

function renderStats(FunnelService $service, int $id): string
{
    $funnel = $service->getById($id);
    if (!$funnel) {
        return '<div class="alert alert-danger">Funnel introuvable.</div>';
    }

    $stats = $service->getStats($id);

    ob_start();
    require __DIR__ . '/views/stats.php';
    return ob_get_clean();
}

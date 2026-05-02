<?php

declare(strict_types=1);

$convertirAction = preg_replace('/[^a-z0-9_-]/i', '', (string) ($_GET['action'] ?? ''));
$convertirAction = $convertirAction === '' ? 'hub' : strtolower($convertirAction);

$convertirTitles = [
    'hub' => 'Convertir en mandats',
    'parcours' => 'Parcours de conversion',
    'qualifier' => 'Étape 1 — Qualification',
    'script-appel' => 'Étape 2 — Script d\'appel',
    'objections' => 'Étape 3 — Gestion des objections',
    'rdv' => 'Étape 4 — Prise de RDV',
    'signature' => 'Étape 5 — Signature mandat',
    'suivi-post-rdv' => 'Suivi post-RDV',
];

$pageTitle = $convertirTitles[$convertirAction] ?? 'Convertir en mandats';
$pageDescription = 'Transformez vos prospects en clients signés.';

$GLOBALS['convertir_action'] = $convertirAction;

function renderContent(): void
{
    $action = (string) ($GLOBALS['convertir_action'] ?? 'hub');

    switch ($action) {
        case 'rdv':
            require __DIR__ . '/rdv.php';
            return;
        case 'suivi-post-rdv':
            require __DIR__ . '/views/suivi-post-rdv.php';
            return;
        case 'parcours':
            require __DIR__ . '/views/parcours.php';
            return;
        case 'qualifier':
            require __DIR__ . '/views/qualifier.php';
            return;
        case 'script-appel':
            require __DIR__ . '/views/script-appel.php';
            return;
        case 'objections':
            require __DIR__ . '/views/objections.php';
            return;
        case 'signature':
            require __DIR__ . '/views/signature.php';
            return;
        case 'hub':
        default:
            require __DIR__ . '/views/hub.php';
            return;
    }
}

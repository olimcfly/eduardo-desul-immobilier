<?php

declare(strict_types=1);

$optimiserView = preg_replace('/[^a-z0-9_-]/i', '', (string) ($_GET['view'] ?? ''));
$optimiserView = $optimiserView === '' ? 'hub' : strtolower($optimiserView);

$optimiserTitles = [
    'hub' => 'Optimiser',
    'parcours' => 'Parcours d\'optimisation',
    'guide' => 'Parcours d\'optimisation',
    'etape-analytics' => 'Étape 1 — Analytics',
    'etape-kpis' => 'Étape 2 — KPIs',
    'etape-dashboard' => 'Étape 3 — Dashboard',
    'etape-tests' => 'Étape 4 — Tests',
    'etape-analyse' => 'Étape 5 — Analyse',
    'analytics' => 'Tableau de bord Analytics',
    'rapport-mensuel' => 'Rapport mensuel',
    'ab-testing' => 'A/B Testing',
    'recommandations' => 'Recommandations IA',
];
$pageTitle = $optimiserTitles[$optimiserView] ?? 'Optimiser';
$pageDescription = 'Pilotage acquisition, analytics et performance.';

$GLOBALS['optimiser_view'] = $optimiserView;

function renderContent(): void
{
    $v = (string) ($GLOBALS['optimiser_view'] ?? 'hub');

    switch ($v) {
        case 'analytics':
            require __DIR__ . '/analytics.php';
            return;
        case 'rapport-mensuel':
            require_once __DIR__ . '/services/MonthlyReportService.php';
            require __DIR__ . '/views/rapport-mensuel.php';
            return;
        case 'ab-testing':
            require __DIR__ . '/views/ab-testing.php';
            return;
        case 'recommandations':
            require __DIR__ . '/views/recommandations.php';
            return;
        case 'parcours':
        case 'guide':
            require __DIR__ . '/views/guide-parcours.php';
            return;
        case 'etape-analytics':
            require __DIR__ . '/views/etape-analytics.php';
            return;
        case 'etape-kpis':
            require __DIR__ . '/views/etape-kpis.php';
            return;
        case 'etape-dashboard':
            require __DIR__ . '/views/etape-dashboard.php';
            return;
        case 'etape-tests':
            require __DIR__ . '/views/etape-tests.php';
            return;
        case 'etape-analyse':
            require __DIR__ . '/views/etape-analyse.php';
            return;
        case 'hub':
        default:
            require __DIR__ . '/index.php';
            return;
    }
}

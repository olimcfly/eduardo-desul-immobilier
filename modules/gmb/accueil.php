<?php
$pageTitle = 'Google My Business';
$pageDescription = 'Pilotez votre fiche, vos avis et vos performances locales.';

$allowedViews = ['index', 'fiche', 'avis', 'demande-avis', 'statistiques'];
$view = $_GET['view'] ?? 'index';
if (!in_array($view, $allowedViews, true)) {
    $view = 'index';
}

require_once '../../admin/views/layout.php';

function renderContent(): void
{
    global $view;
    $viewFile = __DIR__ . '/' . $view . '.php';

    echo '<link rel="stylesheet" href="/modules/gmb/assets/gmb.css?v=' . filemtime(__DIR__ . '/assets/gmb.css') . '">';

    if (is_file($viewFile)) {
        require $viewFile;
    } else {
        require __DIR__ . '/index.php';
    }

    echo '<script src="/modules/gmb/assets/gmb.js?v=' . filemtime(__DIR__ . '/assets/gmb.js') . '"></script>';
}

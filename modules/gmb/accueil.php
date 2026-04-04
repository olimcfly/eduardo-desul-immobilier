<?php
$pageTitle = 'Google My Business';
$pageDescription = 'Pilotez votre fiche, vos avis et vos performances locales.';

$allowedViews = ['index', 'fiche', 'avis', 'demande-avis', 'statistiques'];
$view = $_GET['view'] ?? 'index';
if (!in_array($view, $allowedViews, true)) {
    $view = 'index';
}

function gmbAssetVersion(string $absolutePath): int
{
    return is_file($absolutePath) ? (int) filemtime($absolutePath) : 1;
}

function renderContent(): void
{
    global $view;
    $viewFile = __DIR__ . '/' . $view . '.php';

    $publicCssPath = $_SERVER['DOCUMENT_ROOT'] . '/admin/assets/css/gmb.css';
    $publicJsPath = $_SERVER['DOCUMENT_ROOT'] . '/admin/assets/js/gmb.js';

    echo '<link rel="stylesheet" href="/admin/assets/css/gmb.css?v=' . gmbAssetVersion($publicCssPath) . '">';

    if (is_file($viewFile)) {
        require $viewFile;
    } else {
        require __DIR__ . '/index.php';
    }

    echo '<script src="/admin/assets/js/gmb.js?v=' . gmbAssetVersion($publicJsPath) . '"></script>';
}

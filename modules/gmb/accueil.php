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

    $cssPath = __DIR__ . '/assets/gmb.css';
    $jsPath = __DIR__ . '/assets/gmb.js';

    echo '<link rel="stylesheet" href="/modules/gmb/assets/gmb.css?v=' . gmbAssetVersion($cssPath) . '">';
    echo '<script>window.GMB_CSRF_TOKEN = ' . json_encode(csrfToken(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ';</script>';

    if (is_file($viewFile)) {
        require $viewFile;
    } else {
        require __DIR__ . '/index.php';
    }

    echo '<script src="/modules/gmb/assets/gmb.js?v=' . gmbAssetVersion($jsPath) . '"></script>';
}

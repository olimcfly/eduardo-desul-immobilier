<?php

declare(strict_types=1);

$pageTitle = 'SEO';
$pageDescription = 'Hub SEO';

$allowedActions = ['index', 'keywords', 'villes', 'sitemap', 'performance'];
$action = isset($_GET['action']) ? preg_replace('/[^a-z-]/', '', (string)$_GET['action']) : 'index';
if (!in_array($action, $allowedActions, true)) {
    $action = 'index';
}

require_once __DIR__ . '/services/SeoService.php';
require_once __DIR__ . '/services/KeywordTracker.php';
require_once __DIR__ . '/services/SitemapGenerator.php';
require_once __DIR__ . '/services/PerformanceAudit.php';

$seoCurrentAction = $action;

require_once '../../admin/views/layout.php';

function renderContent(): void
{
    global $seoCurrentAction;

    $fileMap = [
        'index' => __DIR__ . '/index.php',
        'keywords' => __DIR__ . '/mots-cles.php',
        'villes' => __DIR__ . '/fiches-villes.php',
        'sitemap' => __DIR__ . '/sitemap.php',
        'performance' => __DIR__ . '/performance.php',
    ];

    $file = $fileMap[$seoCurrentAction] ?? $fileMap['index'];

    echo '<link rel="stylesheet" href="/admin/assets/css/seo.css?v=' . (int)@filemtime($_SERVER['DOCUMENT_ROOT'] . '/admin/assets/css/seo.css') . '">';
    require $file;
    echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
    echo '<script src="/admin/assets/js/seo.js?v=' . (int)@filemtime($_SERVER['DOCUMENT_ROOT'] . '/admin/assets/js/seo.js') . '"></script>';
}

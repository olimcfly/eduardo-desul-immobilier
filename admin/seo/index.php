<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';

$action = $_GET['action'] ?? 'dashboard';
if (!in_array($action, SEO_ALLOWED_ACTIONS, true)) {
    $action = 'dashboard';
}

$pageTitle = match ($action) {
    'dashboard' => 'Dashboard SEO',
    'editor' => 'Éditeur d\'article',
    'keywords' => 'Mots-clés',
    'serp' => 'Simulation SERP',
    'silo' => 'Silo Pilier',
    default => 'SEO',
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!seo_verify_csrf((string)$csrfToken)) {
        http_response_code(419);
        exit('Token CSRF invalide.');
    }
}

require_once __DIR__ . '/views/' . $action . '.php';

<?php
declare(strict_types=1);

// ── Charger la fonction de gestion de session ────────────────
require_once __DIR__ . '/session-helper.php';

// ── Démarrer la session ──────────────────────────────────────
startAdminSession();

// ── Vérifier authentification ────────────────────────────────
if (!isAdminLoggedIn()) {
    redirectAdmin('/admin/login');
}

// ── Définir les constantes ──────────────────────────────────
define('ROOT_PATH', dirname(__DIR__));

// ── Charger le bootstrap du projet ──────────────────────────
require_once ROOT_PATH . '/core/bootstrap.php';

// ── Déterminer le module demandé ────────────────────────────
$module = $_GET['module'] ?? 'dashboard';
$module = preg_replace('/[^a-z0-9_-]/i', '', $module);

// ── Charger la vue du module ou le dashboard par défaut ─────
$pageTitle = 'Tableau de bord';

// Vérifier si c'est un module valide
$modulePath = ROOT_PATH . '/modules/' . $module . '/accueil.php';
if (is_file($modulePath) && $module !== 'dashboard') {
    require $modulePath;
} else {
    // Dashboard par défaut
    $pageTitle = 'Tableau de bord';
    require __DIR__ . '/views/dashboard/index.php';
}

// ── Charger le layout principal ──────────────────────────────
require __DIR__ . '/views/layout.php';
?>

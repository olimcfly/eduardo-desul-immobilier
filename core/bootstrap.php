<?php
// ============================================================
// BOOTSTRAP — Point d'entrée unique
// ============================================================

// ── Autoload configs ─────────────────────────────────────────
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';

// ── Helpers ──────────────────────────────────────────────────
require_once __DIR__ . '/helpers/helpers.php';
require_once __DIR__ . '/helpers/sanitize.php';
require_once __DIR__ . '/helpers/auth.php';

// ── Session ──────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFE,
        'path'     => '/',
        'domain'   => '',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

// ── Error handling ───────────────────────────────────────────
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
    ini_set('log_errors', 1);
    ini_set('error_log', STORAGE_PATH . '/logs/php_errors.log');
}

// ── Headers sécurité ─────────────────────────────────────────
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

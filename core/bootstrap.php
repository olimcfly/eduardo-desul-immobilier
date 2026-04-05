<?php
// ============================================================
// BOOTSTRAP — Point d'entrée unique
// ============================================================

// ── Composer autoload ────────────────────────────────────────
$_autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($_autoload)) {
    require_once $_autoload;
}
unset($_autoload);

// ── Charger les variables d'environnement (.env) ─────────────
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, ';') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim((string) $key);
        if ($key === '') {
            continue;
        }

        $value = trim((string) $value);
        $firstChar = $value[0] ?? '';
        $lastChar = $value !== '' ? substr($value, -1) : '';
        if (($firstChar === '"' && $lastChar === '"') || ($firstChar === "'" && $lastChar === "'")) {
            $value = substr($value, 1, -1);
        }

        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv($key . '=' . $value);
    }
}

// ── Autoload configs ─────────────────────────────────────────
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';

// ── Core classes ─────────────────────────────────────────────
require_once __DIR__ . '/Session.php';
require_once __DIR__ . '/Auth.php';

// ── Helpers ──────────────────────────────────────────────────
require_once __DIR__ . '/helpers/helpers.php';
require_once __DIR__ . '/helpers/sanitize.php';
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/helpers/cms.php';
require_once dirname(__DIR__) . '/includes/settings.php';
require_once __DIR__ . '/services/LeadService.php';

// ── Session ──────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_set_cookie_params([
        'lifetime' => SESSION_LIFE,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
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

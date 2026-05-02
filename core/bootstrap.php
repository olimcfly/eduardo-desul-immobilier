<?php
declare(strict_types=1);

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

        if (
            $line === ''
            || str_starts_with($line, '#')
            || str_starts_with($line, ';')
            || !str_contains($line, '=')
        ) {
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

        if (
            ($firstChar === '"' && $lastChar === '"')
            || ($firstChar === "'" && $lastChar === "'")
        ) {
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
require_once __DIR__ . '/Router.php';

// ── Helpers ──────────────────────────────────────────────────
require_once __DIR__ . '/helpers/helpers.php';
require_once __DIR__ . '/helpers/sanitize.php';
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/services/PageContentService.php';
require_once __DIR__ . '/helpers/cms.php';
require_once dirname(__DIR__) . '/includes/settings.php';
require_once __DIR__ . '/services/MailService.php';
require_once __DIR__ . '/services/LeadService.php';
require_once __DIR__ . '/services/InstantEstimationService.php';
require_once __DIR__ . '/services/EstimationTunnelService.php';
require_once __DIR__ . '/services/ConversionTrackingService.php';
require_once __DIR__ . '/services/EmailSequenceService.php';
require_once __DIR__ . '/services/TelegramBotService.php';
require_once __DIR__ . '/services/AgentService.php';
require_once __DIR__ . '/controllers/ZoneController.php';
require_once __DIR__ . '/controllers/LandingPageController.php';

// ── Error handling ───────────────────────────────────────────
if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
    ini_set('log_errors', '1');
    if (defined('STORAGE_PATH')) {
        ini_set('error_log', STORAGE_PATH . '/logs/php_errors.log');
    }
}

// ── Session ──────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_name(defined('SESSION_NAME') ? SESSION_NAME : 'app_session');

    session_set_cookie_params([
        'lifetime' => defined('SESSION_LIFE') ? (int) SESSION_LIFE : 28800,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    session_start();
}

// ── Headers sécurité ─────────────────────────────────────────
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}
<?php
// ============================================================
// CONFIG GLOBALE — Core (legacy)
// ============================================================

define('APP_NAME',      $_ENV['APP_NAME'] ?? 'Eduardo Desul Immobilier');

$configuredAppUrl = trim((string) ($_ENV['APP_URL'] ?? ''));
$appHost          = strtolower((string) parse_url($configuredAppUrl, PHP_URL_HOST));
$looksLocalUrl    = $configuredAppUrl === ''
    || $appHost === ''
    || in_array($appHost, ['localhost', '127.0.0.1', '::1'], true);

if ($looksLocalUrl) {
    $forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    $isHttps        = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        || $forwardedProto === 'https';
    $scheme         = $isHttps ? 'https' : 'http';
    $host           = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));

    if ($host !== '') {
        $configuredAppUrl = $scheme . '://' . $host;
    } else {
        $configuredAppUrl = 'https://eduardo-desul-immobilier.fr';
    }
}

define('APP_URL',       rtrim($configuredAppUrl, '/'));
define('APP_EMAIL',     $_ENV['APP_EMAIL'] ?? '');
define('APP_PHONE',     $_ENV['APP_PHONE'] ?? '');
define('APP_ADDRESS',   $_ENV['APP_ADDRESS'] ?? '');
define('APP_CITY',      $_ENV['APP_CITY'] ?? 'Bordeaux');
define('APP_SIRET',     $_ENV['APP_SIRET'] ?? '');

define('ADVISOR_NAME',  $_ENV['ADVISOR_NAME'] ?? '');
define('ADVISOR_CARTE', $_ENV['ADVISOR_CARTE'] ?? '');
define('ADVISOR_RSAC',  $_ENV['ADVISOR_RSAC'] ?? '');

defined('ROOT_PATH') || define('ROOT_PATH',     $_ENV['ROOT_PATH'] ?? dirname(__DIR__, 2));
define('PUBLIC_PATH',   ROOT_PATH . '/public');
define('STORAGE_PATH',  ROOT_PATH . '/storage');
define('MODULES_PATH',  ROOT_PATH . '/modules');
define('CORE_PATH',     ROOT_PATH . '/core');

define('UPLOAD_PATH',   STORAGE_PATH . '/uploads');
define('UPLOAD_URL',    APP_URL . '/storage/uploads');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMG',   ['jpg', 'jpeg', 'png', 'webp']);

define('SESSION_NAME',  'edo_immo_sess');
define('SESSION_LIFE',  3600 * 8);

define('APP_ENV',       $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG',     filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));

define('BIENS_PER_PAGE',    12);
define('BLOG_PER_PAGE',     10);
define('CONTACTS_PER_PAGE', 25);

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Europe/Paris');
setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fr');

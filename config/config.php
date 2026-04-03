<?php
// ============================================================
// CONFIG GLOBALE — CRM Immobilier
// ============================================================

define('APP_NAME',    $_ENV['APP_NAME']    ?? 'CRM Immobilier');
define('APP_URL',     $_ENV['APP_URL']     ?? 'http://localhost');
define('APP_EMAIL',   $_ENV['APP_EMAIL']   ?? '');
define('APP_DEBUG',   filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('APP_ENV',     $_ENV['APP_ENV']     ?? 'production');

define('ADVISOR_NAME', $_ENV['ADVISOR_NAME'] ?? '');
define('APP_PHONE',   $_ENV['APP_PHONE']   ?? '');
define('APP_ADDRESS', $_ENV['APP_ADDRESS'] ?? '');
define('APP_CITY',    $_ENV['APP_CITY']    ?? '');
define('APP_SIRET',   $_ENV['APP_SIRET']   ?? '');

define('PUBLIC_PATH', ROOT_PATH . '/public');
define('CORE_PATH',   ROOT_PATH . '/core');
define('ADMIN_PATH',  ROOT_PATH . '/admin');
define('UPLOAD_PATH', PUBLIC_PATH . '/assets/images/uploads');
define('UPLOAD_URL',  APP_URL . '/assets/images/uploads');

define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMG',   ['jpg', 'jpeg', 'png', 'webp']);

define('BIENS_PER_PAGE',    12);
define('BLOG_PER_PAGE',     10);
define('CONTACTS_PER_PAGE', 25);

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Europe/Paris');
setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fr');

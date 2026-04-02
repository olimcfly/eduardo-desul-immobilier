<?php
// ============================================================
// CONFIG GLOBALE — Eduardo Desul Immobilier
// ============================================================

define('APP_NAME',    $_ENV['APP_NAME']    ?? 'Eduardo Desul Immobilier');
define('APP_URL',     $_ENV['APP_URL']     ?? 'https://eduardo-desul-immobilier.fr');
define('APP_EMAIL',   $_ENV['APP_EMAIL']   ?? 'contact@eduardo-desul-immobilier.fr');
define('APP_DEBUG',   filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('APP_ENV',     $_ENV['APP_ENV']     ?? 'production');

// ── Identité ─────────────────────────────────────────────────
define('ADVISOR_NAME',  'Eduardo Desul');
define('APP_PHONE',     $_ENV['APP_PHONE']   ?? '');
define('APP_ADDRESS',   'Bordeaux, France');
define('APP_CITY',      'Bordeaux');
define('APP_SIRET',     $_ENV['APP_SIRET']   ?? '');

// ── Chemins absolus ──────────────────────────────────────────
// ROOT_PATH est déjà défini dans public/index.php avant ce fichier
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('CORE_PATH',   ROOT_PATH . '/core');
define('ADMIN_PATH',  ROOT_PATH . '/admin');
define('UPLOAD_PATH', PUBLIC_PATH . '/assets/images/uploads');
define('UPLOAD_URL',  APP_URL . '/assets/images/uploads');

// ── Uploads ──────────────────────────────────────────────────
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMG',   ['jpg', 'jpeg', 'png', 'webp']);

// ── Pagination ───────────────────────────────────────────────
define('BIENS_PER_PAGE',    12);
define('BLOG_PER_PAGE',     10);
define('CONTACTS_PER_PAGE', 25);

// ── Localisation ─────────────────────────────────────────────
date_default_timezone_set('Europe/Paris');
setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fr');

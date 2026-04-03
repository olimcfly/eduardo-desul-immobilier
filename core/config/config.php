<?php
// ============================================================
// CONFIG GLOBALE — Eduardo Desul Immobilier
// ============================================================

define('APP_NAME',      'Eduardo Desul Immobilier');
define('APP_URL',       'https://eduardo-desul-immobilier.fr');
define('APP_EMAIL',     'contact@eduardo-desul-immobilier.fr');
define('APP_PHONE',     ''); // À compléter
define('APP_ADDRESS',   'Bordeaux, France');
define('APP_CITY',      'Bordeaux');
define('APP_SIRET',     ''); // À compléter

// ── Conseiller ───────────────────────────────────────────────
define('ADVISOR_NAME',  'Eduardo Desul');
define('ADVISOR_CARTE', ''); // N° carte pro CCI
define('ADVISOR_RSAC',  ''); // N° RSAC

// ── Chemins ──────────────────────────────────────────────────
define('ROOT_PATH',     '/home/mahe6420/site-immo');
define('PUBLIC_PATH',   ROOT_PATH . '/public');
define('STORAGE_PATH',  ROOT_PATH . '/storage');
define('MODULES_PATH',  ROOT_PATH . '/modules');
define('CORE_PATH',     ROOT_PATH . '/core');

// ── Uploads ──────────────────────────────────────────────────
define('UPLOAD_PATH',   STORAGE_PATH . '/uploads');
define('UPLOAD_URL',    APP_URL . '/storage/uploads');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 Mo
define('ALLOWED_IMG',   ['jpg', 'jpeg', 'png', 'webp']);

// ── Session ──────────────────────────────────────────────────
define('SESSION_NAME',  'edo_immo_sess');
define('SESSION_LIFE',  3600 * 8); // 8h

// ── Environnement ────────────────────────────────────────────
define('APP_ENV',       'production'); // 'dev' ou 'production'
define('APP_DEBUG',     true);

// ── Pagination ───────────────────────────────────────────────
define('BIENS_PER_PAGE',    12);
define('BLOG_PER_PAGE',     10);
define('CONTACTS_PER_PAGE', 25);

// ── Timezone ─────────────────────────────────────────────────
date_default_timezone_set('Europe/Paris');
setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fr');

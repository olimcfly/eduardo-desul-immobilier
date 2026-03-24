<?php
/**
 * Configuration DB - Eduardo De Sul CMS
 * Ce fichier DOIT être autonome (chargé en premier)
 */

// Protection double inclusion
if (defined('DB_LOADED')) return;
define('DB_LOADED', true);

// S'assurer que INCLUDES_PATH existe (au cas où on est chargé en premier)
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
if (!defined('INCLUDES_PATH')) {
    define('INCLUDES_PATH', ROOT_PATH . '/includes');
}

// Charger le helper d'environnement (env/loadEnv) si disponible
$envHelper = ROOT_PATH . '/core/env.php';
if (is_file($envHelper)) {
    require_once $envHelper;
}
if (function_exists('loadEnv')) {
    loadEnv(ROOT_PATH . '/.env');
}

// Charger la classe Database
require_once INCLUDES_PATH . '/classes/Database.php';

// Singleton - une seule connexion
$db = Database::getInstance();
return $db;
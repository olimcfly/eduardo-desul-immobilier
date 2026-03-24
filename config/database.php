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

// Fallback minimal si core/env.php est absent ou vide
if (!function_exists('loadEnv')) {
    function loadEnv(string $path): void {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }

            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));
            $value = trim($value, "\"'");

            if ($key !== '' && getenv($key) === false) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

if (!function_exists('env')) {
    function env(string $key, $default = null) {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }

        $lower = strtolower($value);
        if ($lower === 'true') return true;
        if ($lower === 'false') return false;
        if ($lower === 'null') return null;

        return $value;
    }
}

if (function_exists('loadEnv')) {
    loadEnv(ROOT_PATH . '/.env');
}

// Charger la classe Database
require_once INCLUDES_PATH . '/classes/Database.php';

// Singleton - une seule connexion
$db = Database::getInstance();
return $db;
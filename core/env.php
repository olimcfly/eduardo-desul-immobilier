<?php
/**
 * Loader .env simple - sans dependance Composer
 * core/env.php
 *
 * Usage: require_once __DIR__ . '/../core/env.php';
 *        loadEnv(__DIR__ . '/../.env');
 *
 * Charge les variables du fichier .env dans getenv() / $_ENV / $_SERVER
 */

function loadEnv(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        // Ignorer les commentaires et lignes vides
        if ($line === '' || $line[0] === '#') {
            continue;
        }

        // Format: KEY=VALUE
        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }

        $key = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));

        // Retirer les guillemets entourants
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        // Ne pas ecraser les variables deja definies par le systeme
        if (getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

/**
 * Recupere une variable d'environnement avec valeur par defaut
 */
function env(string $key, $default = null)
{
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }

    // Convertir les booleens
    $lower = strtolower($value);
    if ($lower === 'true') return true;
    if ($lower === 'false') return false;
    if ($lower === 'null') return null;

    return $value;
}

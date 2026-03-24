<?php
/**
 * Diagnostic DB minimal (temporaire)
 * - Vérifie lecture .env
 * - Vérifie extensions PDO
 * - Tente connexion MySQL avec message d'erreur clair
 *
 * ⚠️ À SUPPRIMER APRÈS DIAGNOSTIC
 */

header('Content-Type: text/plain; charset=utf-8');

define('ROOT_PATH', __DIR__);

function loadEnvFile(string $path): array {
    $vars = [];
    if (!is_file($path) || !is_readable($path)) {
        return $vars;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return $vars;
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

        if ($key !== '') {
            $vars[$key] = $value;
            if (getenv($key) === false) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }

    return $vars;
}

function envv(string $key, $default = null) {
    $v = getenv($key);
    return $v === false ? $default : $v;
}

function mask(?string $value, int $visible = 3): string {
    if ($value === null || $value === '') {
        return '(vide)';
    }
    $len = strlen($value);
    if ($len <= $visible * 2) {
        return str_repeat('*', $len);
    }
    return substr($value, 0, $visible) . str_repeat('*', $len - ($visible * 2)) . substr($value, -$visible);
}

$envPath = ROOT_PATH . '/.env';
$loaded = loadEnvFile($envPath);

echo "=== Diagnostic DB ===\n";
echo 'Date UTC: ' . gmdate('Y-m-d H:i:s') . "\n\n";

echo "[1] Fichier .env\n";
echo '- Path: ' . $envPath . "\n";
echo '- Existe: ' . (is_file($envPath) ? 'oui' : 'non') . "\n";
echo '- Lisible: ' . (is_readable($envPath) ? 'oui' : 'non') . "\n";
echo '- Vars chargees: ' . count($loaded) . "\n\n";

echo "[2] Extensions PHP\n";
echo '- PDO: ' . (extension_loaded('pdo') ? 'oui' : 'non') . "\n";
echo '- pdo_mysql: ' . (extension_loaded('pdo_mysql') ? 'oui' : 'non') . "\n\n";

$dbHost = (string) envv('DB_HOST', 'localhost');
$dbPort = (string) envv('DB_PORT', '3306');
$dbName = (string) envv('DB_NAME', '');
$dbUser = (string) envv('DB_USER', '');
$dbPass = (string) envv('DB_PASS', '');
$dbCharset = (string) envv('DB_CHARSET', 'utf8mb4');

echo "[3] Variables DB\n";
echo '- DB_HOST: ' . $dbHost . "\n";
echo '- DB_PORT: ' . $dbPort . "\n";
echo '- DB_NAME: ' . ($dbName !== '' ? $dbName : '(vide)') . "\n";
echo '- DB_USER: ' . ($dbUser !== '' ? $dbUser : '(vide)') . "\n";
echo '- DB_PASS: ' . mask($dbPass) . "\n";
echo '- DB_CHARSET: ' . $dbCharset . "\n\n";

if ($dbName === '' || $dbUser === '' || $dbPass === '') {
    echo "[4] Connexion DB\n";
    echo "- ERREUR: variables DB_NAME/DB_USER/DB_PASS manquantes dans .env\n";
    exit(1);
}

echo "[4] Connexion DB\n";
$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset={$dbCharset}";
echo '- DSN: ' . $dsn . "\n";

try {
    $pdo = new PDO(
        $dsn,
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5,
        ]
    );

    $serverVersion = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
    $stmt = $pdo->query('SELECT NOW() AS now_utc');
    $row = $stmt ? $stmt->fetch() : null;

    echo "- OK: connexion reussie\n";
    echo '- MySQL version: ' . $serverVersion . "\n";
    echo '- SELECT NOW(): ' . ($row['now_utc'] ?? 'n/a') . "\n";
    echo "\nDiagnostic termine avec succes.\n";
} catch (Throwable $e) {
    echo "- ECHEC: " . $e->getMessage() . "\n";
    echo "\nActions conseillees:\n";
    echo "1) Tester DB_HOST=127.0.0.1 puis localhost\n";
    echo "2) Verifier DB_USER/DB_PASS dans cPanel\n";
    echo "3) Verifier que la base existe et est assignee a l'utilisateur\n";
    exit(2);
}

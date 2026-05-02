#!/usr/bin/env php
<?php
/**
 * Applique database/migrations/033_settings_openrouter.sql (template api_openrouter).
 * Usage : php scripts/run-migration-033.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$envFile = $root . '/.env';
$sqlFile = $root . '/database/migrations/033_settings_openrouter.sql';

if (!is_readable($envFile)) {
    fwrite(STDERR, "Fichier .env introuvable : {$envFile}\n");
    exit(1);
}
if (!is_readable($sqlFile)) {
    fwrite(STDERR, "Fichier SQL introuvable : {$sqlFile}\n");
    exit(1);
}

$_ENV = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
        continue;
    }
    [$k, $v] = explode('=', $line, 2);
    $k = trim($k);
    $v = trim($v);
    if ($k === '') {
        continue;
    }
    if (strlen($v) >= 2 && (($v[0] === '"' && $v[strlen($v) - 1] === '"') || ($v[0] === "'" && $v[strlen($v) - 1] === "'"))) {
        $v = substr($v, 1, -1);
    }
    $_ENV[$k] = $v;
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = (int) ($_ENV['DB_PORT'] ?? 3306);
$db = $_ENV['DB_NAME'] ?? $_ENV['DB_DATABASE'] ?? '';
$user = $_ENV['DB_USER'] ?? $_ENV['DB_USERNAME'] ?? '';
$pass = $_ENV['DB_PASS'] ?? $_ENV['DB_PASSWORD'] ?? '';
$charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

if ($db === '' || $user === '') {
    fwrite(STDERR, "DB_NAME et DB_USER requis dans .env\n");
    exit(1);
}

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Throwable $e) {
    fwrite(STDERR, 'Connexion DB : ' . $e->getMessage() . "\n");
    exit(1);
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    fwrite(STDERR, "Lecture SQL impossible\n");
    exit(1);
}

$pdo->exec($sql);
echo "OK — migration 033 appliquée (settings_templates.api_openrouter).\n";

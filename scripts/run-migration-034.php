#!/usr/bin/env php
<?php
/**
 * Applique database/migrations/034_guide_annuaire_commercants.sql
 * Prérequis : 032 (guide_pois) exécuté.
 * Usage : php scripts/run-migration-034.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$envFile = $root . '/.env';
$sqlFile = $root . '/database/migrations/034_guide_annuaire_commercants.sql';

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
    if (strlen($v) >= 2 && (($v[0] === '"' && $v[strlen($v) - 1] === '"') || ($v[0] === "'" && $v[strlen($v) - 1] === "'"))) {
        $v = substr($v, 1, -1);
    }
    $_ENV[$k] = $v;
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = (int) ($_ENV['DB_PORT'] ?? 3306);
$name = $_ENV['DB_NAME'] ?? $_ENV['DB_DATABASE'] ?? '';
$user = $_ENV['DB_USER'] ?? $_ENV['DB_USERNAME'] ?? '';
$pass = $_ENV['DB_PASS'] ?? $_ENV['DB_PASSWORD'] ?? '';
$socket = $_ENV['DB_SOCKET'] ?? '';

if ($name === '' || $user === '') {
    fwrite(STDERR, "DB_NAME et DB_USER requis dans .env\n");
    exit(1);
}

if ($socket !== '') {
    $mysqli = mysqli_connect($host, $user, $pass, $name, $port, $socket);
} else {
    $mysqli = mysqli_connect($host, $user, $pass, $name, $port);
}

if ($mysqli === false) {
    fwrite(STDERR, 'Connexion MySQL impossible : ' . mysqli_connect_error() . "\n");
    exit(1);
}

$mysqli->set_charset('utf8mb4');
$sql = file_get_contents($sqlFile);
if ($sql === false) {
    fwrite(STDERR, "Lecture SQL impossible.\n");
    exit(1);
}

if (!$mysqli->multi_query($sql)) {
    fwrite(STDERR, 'Erreur SQL : ' . $mysqli->error . "\n");
    exit(1);
}
do {
    if ($result = $mysqli->store_result()) {
        $result->free();
    }
} while ($mysqli->more_results() && $mysqli->next_result());

if ($mysqli->errno) {
    fwrite(STDERR, 'Erreur après exécution : ' . $mysqli->error . "\n");
    exit(1);
}

echo "OK — migration 034 appliquée (colonnes annuaire guide_pois, avis, catégories commerçants).\n";
$mysqli->close();
exit(0);

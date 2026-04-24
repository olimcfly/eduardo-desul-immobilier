<?php
declare(strict_types=1);

/**
 * Connexion PDO centrale via variables d'environnement (.env)
 * Compatible avec le pattern existant du projet
 */

if (!function_exists('getPDO')) {
    function getPDO(): PDO
    {
        static $pdo = null;

        if ($pdo !== null) {
            return $pdo;
        }

        $host    = $_ENV['DB_HOST']     ?? $_ENV['DATABASE_HOST']     ?? 'localhost';
        $port    = $_ENV['DB_PORT']     ?? $_ENV['DATABASE_PORT']     ?? '';
        $socket  = $_ENV['DB_SOCKET']   ?? $_ENV['DATABASE_SOCKET']   ?? '';
        $dbName  = $_ENV['DB_NAME']     ?? $_ENV['DB_DATABASE']       ?? $_ENV['DATABASE_NAME'] ?? '';
        $user    = $_ENV['DB_USER']     ?? $_ENV['DB_USERNAME']       ?? $_ENV['DATABASE_USER'] ?? '';
        $pass    = $_ENV['DB_PASS']     ?? $_ENV['DB_PASSWORD']       ?? $_ENV['DATABASE_PASSWORD'] ?? '';
        $charset = $_ENV['DB_CHARSET']  ?? 'utf8mb4';

        if ($dbName === '' || $user === '') {
            throw new RuntimeException('DB_NAME / DB_USER manquants dans le fichier .env');
        }

        $dsn = "mysql:host={$host};dbname={$dbName};charset={$charset}";
        if ($port   !== '') $dsn .= ';port='        . (int) $port;
        if ($socket !== '') $dsn .= ';unix_socket=' . $socket;

        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (Throwable $e) {
            error_log('[DB] Connexion échouée : ' . $e->getMessage());
            throw new RuntimeException('Erreur de connexion base de données.');
        }

        return $pdo;
    }
}

/**
 * Version nullable — pour les contextes où la DB peut être absente
 * (même pattern que blog_articles_pdo())
 */
if (!function_exists('getPDOSafe')) {
    function getPDOSafe(): ?PDO
    {
        try {
            return getPDO();
        } catch (Throwable $e) {
            error_log('[DB] getPDOSafe échoué : ' . $e->getMessage());
            return null;
        }
    }
}

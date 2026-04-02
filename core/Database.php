<?php
// ============================================================
// DATABASE — PDO Singleton
// Lit les credentials depuis les variables d'environnement
// ============================================================

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $host    = $_ENV['DB_HOST']    ?? 'localhost';
            $dbname  = $_ENV['DB_NAME']    ?? '';
            $user    = $_ENV['DB_USER']    ?? '';
            $pass    = $_ENV['DB_PASS']    ?? '';
            $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

            $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]);
            } catch (PDOException $e) {
                error_log('DB Connection failed: ' . $e->getMessage());
                if ($_ENV['APP_DEBUG'] ?? false) {
                    die('DB Error: ' . $e->getMessage());
                }
                http_response_code(500);
                die('Service temporairement indisponible.');
            }
        }

        return self::$instance;
    }

    private function __construct() {}
    private function __clone() {}
}

function db(): PDO
{
    return Database::getInstance();
}

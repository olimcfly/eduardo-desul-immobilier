<?php
// ============================================================
// CONNEXION PDO — Singleton
// ============================================================

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $host    = $_ENV['DB_HOST'] ?? $_ENV['DATABASE_HOST'] ?? 'localhost';
            $port    = $_ENV['DB_PORT'] ?? $_ENV['DATABASE_PORT'] ?? '';
            $socket  = $_ENV['DB_SOCKET'] ?? $_ENV['DATABASE_SOCKET'] ?? '';
            $dbname  = $_ENV['DB_NAME'] ?? $_ENV['DB_DATABASE'] ?? $_ENV['DATABASE_NAME'] ?? '';
            $user    = $_ENV['DB_USER'] ?? $_ENV['DB_USERNAME'] ?? $_ENV['DATABASE_USER'] ?? '';
            $pass    = $_ENV['DB_PASS'] ?? $_ENV['DB_PASSWORD'] ?? $_ENV['DATABASE_PASSWORD'] ?? '';
            $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s%s%s',
                $host,
                $dbname,
                $charset,
                $port !== '' ? ';port=' . (int) $port : '',
                $socket !== '' ? ';unix_socket=' . $socket : ''
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                    ]
                );
            } catch (PDOException $e) {
                if (APP_DEBUG) {
                    die('DB Error: ' . $e->getMessage());
                }
                error_log('DB Connection failed: ' . $e->getMessage());
                http_response_code(500);
                die('Service temporairement indisponible.');
            }
        }

        return self::$instance;
    }

    // Raccourci global
    public static function get(): PDO
    {
        return self::getInstance();
    }

    // Empêche clone et sérialisation
    private function __construct() {}
    private function __clone() {}
}

// ── Helper global ────────────────────────────────────────────
function db(): PDO
{
    return Database::get();
}

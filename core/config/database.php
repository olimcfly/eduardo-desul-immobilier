<?php
// ============================================================
// CONNEXION PDO — Singleton
// ============================================================

class Database
{
    private static ?PDO $instance = null;

    private static array $config = [
        'host'    => 'localhost',
        'dbname'  => 'mahe6420_site_immo',
        'user'    => 'mahe6420_site_immo',
        'pass'    => 'm3okqlr55312ik05',
        'charset' => 'utf8mb4',
    ];

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                self::$config['host'],
                self::$config['dbname'],
                self::$config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    self::$config['user'],
                    self::$config['pass'],
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

<?php
/**
 * DATABASE MIGRATION SCRIPT
 * /database/migrate.php
 *
 * Exécute les migrations SQL dans l'ordre
 * Vérifie les migrations déjà appliquées
 * Gère les erreurs proprement
 *
 * Usage:
 *   php database/migrate.php              # Exécuter les migrations
 *   php database/migrate.php --reset      # Réinitialiser le suivi
 *   php database/migrate.php --status     # Voir le statut
 */

declare(strict_types=1);

// ═══════════════════════════════════════════════════════════
// CONFIGURATION
// ═══════════════════════════════════════════════════════════

define('ROOT_PATH', dirname(__DIR__));
define('MIGRATIONS_PATH', __DIR__ . '/migrations');
define('MIGRATIONS_TABLE', 'migrations');

// Couleurs pour terminal
class Colors {
    const RESET = "\033[0m";
    const RED = "\033[91m";
    const GREEN = "\033[92m";
    const YELLOW = "\033[93m";
    const BLUE = "\033[94m";
    const CYAN = "\033[96m";
}

// ═══════════════════════════════════════════════════════════
// BOOTSTRAP & CONFIGURATION
// ═══════════════════════════════════════════════════════════

// Charger config
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';

// ═══════════════════════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════════════════════

function println($message = '', $color = '') {
    if ($color) {
        echo $color . $message . Colors::RESET . "\n";
    } else {
        echo $message . "\n";
    }
}

function print_header($text) {
    println();
    println(str_repeat('═', 60), Colors::CYAN);
    println('  ' . $text, Colors::CYAN);
    println(str_repeat('═', 60), Colors::CYAN);
    println();
}

function print_success($text) {
    println('✅ ' . $text, Colors::GREEN);
}

function print_error($text) {
    println('❌ ' . $text, Colors::RED);
}

function print_warning($text) {
    println('⚠️  ' . $text, Colors::YELLOW);
}

function print_info($text) {
    println('ℹ️  ' . $text, Colors::BLUE);
}

// ═══════════════════════════════════════════════════════════
// MIGRATION CLASS
// ═══════════════════════════════════════════════════════════

class MigrationManager {
    private $db;
    private $migrations_table = MIGRATIONS_TABLE;
    private $migrations = [];
    private $executed = [];

    /**
     * Constructor
     */
    public function __construct($pdo) {
        $this->db = $pdo;
        $this->initMigrationsTable();
        $this->loadExecutedMigrations();
        $this->discoverMigrations();
    }

    /**
     * Créer la table migrations si elle n'existe pas
     */
    private function initMigrationsTable() {
        try {
            $sql = "
                CREATE TABLE IF NOT EXISTS `{$this->migrations_table}` (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    filename VARCHAR(255) UNIQUE NOT NULL,
                    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
                    error_message LONGTEXT,
                    duration_ms INT,
                    INDEX idx_filename (filename),
                    INDEX idx_status (status),
                    INDEX idx_executed_at (executed_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ";

            $this->db->exec($sql);
            return true;
        } catch (PDOException $e) {
            throw new Exception('Failed to create migrations table: ' . $e->getMessage());
        }
    }

    /**
     * Charger les migrations déjà exécutées
     */
    private function loadExecutedMigrations() {
        try {
            $sql = "SELECT filename FROM `{$this->migrations_table}` WHERE status = 'completed'";
            $stmt = $this->db->query($sql);
            $this->executed = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            print_warning('Could not load executed migrations: ' . $e->getMessage());
            $this->executed = [];
        }
    }

    /**
     * Découvrir les fichiers migrations
     */
    private function discoverMigrations() {
        if (!is_dir(MIGRATIONS_PATH)) {
            throw new Exception('Migrations directory not found: ' . MIGRATIONS_PATH);
        }

        $files = glob(MIGRATIONS_PATH . '/*.sql');
        if (!$files) {
            print_warning('No migration files found');
            return;
        }

        // Trier par nom (qui contient la date)
        sort($files);

        foreach ($files as $file) {
            $filename = basename($file);
            // Ignorer les fichiers qui ne matchent pas le pattern
            if (!preg_match('/^\d{8}_.*\.sql$/', $filename)) {
                continue;
            }

            $this->migrations[$filename] = [
                'path' => $file,
                'filename' => $filename,
                'executed' => in_array($filename, $this->executed)
            ];
        }
    }

    /**
     * Obtenir le statut
     */
    public function getStatus() {
        $status = [
            'total' => count($this->migrations),
            'executed' => 0,
            'pending' => 0,
            'migrations' => []
        ];

        foreach ($this->migrations as $name => $info) {
            $status['migrations'][$name] = [
                'executed' => $info['executed'],
                'status' => $info['executed'] ? 'completed' : 'pending'
            ];

            if ($info['executed']) {
                $status['executed']++;
            } else {
                $status['pending']++;
            }
        }

        return $status;
    }

    /**
     * Exécuter toutes les migrations
     */
    public function migrate($force = false) {
        $results = [];
        $count = 0;

        foreach ($this->migrations as $filename => $info) {
            // Sauter si déjà exécutée (sauf si --force)
            if ($info['executed'] && !$force) {
                $results[$filename] = [
                    'status' => 'skipped',
                    'message' => 'Already executed',
                    'duration' => 0
                ];
                continue;
            }

            // Exécuter la migration
            $result = $this->executeMigration($filename, $info['path']);
            $results[$filename] = $result;

            if ($result['status'] === 'completed') {
                $count++;
            }
        }

        return [
            'total' => count($this->migrations),
            'executed' => $count,
            'results' => $results
        ];
    }

    /**
     * Exécuter une migration
     */
    private function executeMigration($filename, $filepath) {
        $start = microtime(true);

        try {
            // Lire le fichier SQL
            if (!file_exists($filepath)) {
                throw new Exception('Migration file not found: ' . $filepath);
            }

            $sql = file_get_contents($filepath);
            if ($sql === false) {
                throw new Exception('Could not read migration file: ' . $filepath);
            }

            // Diviser en statements
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function($stmt) { return !empty($stmt); }
            );

            // Exécuter chaque statement
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $this->db->exec($statement);
                }
            }

            // Enregistrer comme complétée
            $duration = round((microtime(true) - $start) * 1000);
            $this->recordMigration($filename, 'completed', null, $duration);

            return [
                'status' => 'completed',
                'message' => count($statements) . ' statement(s) executed',
                'duration' => $duration
            ];

        } catch (Exception $e) {
            $duration = round((microtime(true) - $start) * 1000);
            $this->recordMigration($filename, 'failed', $e->getMessage(), $duration);

            return [
                'status' => 'failed',
                'message' => $e->getMessage(),
                'duration' => $duration
            ];
        }
    }

    /**
     * Enregistrer l'exécution d'une migration
     */
    private function recordMigration($filename, $status, $error = null, $duration = 0) {
        try {
            $sql = "
                INSERT INTO `{$this->migrations_table}` (filename, status, error_message, duration_ms)
                VALUES (:filename, :status, :error, :duration)
                ON DUPLICATE KEY UPDATE
                    status = :status,
                    error_message = :error,
                    duration_ms = :duration,
                    executed_at = NOW()
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':filename' => $filename,
                ':status' => $status,
                ':error' => $error,
                ':duration' => $duration
            ]);
        } catch (Exception $e) {
            print_error('Failed to record migration: ' . $e->getMessage());
        }
    }

    /**
     * Réinitialiser le suivi des migrations
     */
    public function reset() {
        try {
            $sql = "DELETE FROM `{$this->migrations_table}`";
            $this->db->exec($sql);
            print_success('Migrations table cleared');
            return true;
        } catch (Exception $e) {
            print_error('Failed to reset migrations: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Afficher les migrations découvertes
     */
    public function listMigrations() {
        if (empty($this->migrations)) {
            print_warning('No migrations found');
            return;
        }

        println('Migrations découvertes:', Colors::CYAN);
        println();

        $num = 1;
        foreach ($this->migrations as $filename => $info) {
            $status = $info['executed'] ? '✅' : '⏳';
            printf("  %d. %s %s\n", $num++, $status, $filename);
        }
        println();
    }

    /**
     * Afficher le statut détaillé
     */
    public function showStatus() {
        $status = $this->getStatus();

        print_header('Migration Status');
        println("Total migrations: " . $status['total'], Colors::CYAN);
        println("Executed: " . $status['executed'], Colors::GREEN);
        println("Pending: " . $status['pending'], Colors::YELLOW);
        println();

        println('Details:', Colors::CYAN);
        foreach ($status['migrations'] as $name => $info) {
            $symbol = $info['status'] === 'completed' ? '✅' : '⏳';
            printf("  %s %s\n", $symbol, $name);
        }
        println();
    }
}

// ═══════════════════════════════════════════════════════════
// MAIN EXECUTION
// ═══════════════════════════════════════════════════════════

try {
    // Récupérer les arguments
    $command = $argv[1] ?? 'migrate';
    $force = in_array('--force', $argv);

    // Connexion DB
    try {
        $db = getDB();
    } catch (Exception $e) {
        print_error('Database connection failed: ' . $e->getMessage());
        exit(1);
    }

    // Créer le manager
    $manager = new MigrationManager($db);

    // ─────────────────────────────────────────────────────────
    // Traiter les commandes
    // ─────────────────────────────────────────────────────────

    if ($command === '--status') {
        // Afficher le statut
        $manager->showStatus();

    } elseif ($command === '--reset') {
        // Réinitialiser
        print_warning('Resetting migrations tracking...');
        $manager->reset();

    } elseif ($command === '--list') {
        // Lister les migrations
        print_header('Discovered Migrations');
        $manager->listMigrations();

    } elseif ($command === 'migrate' || $command === '--migrate') {
        // Exécuter les migrations
        print_header('Running Migrations');

        $manager->listMigrations();

        $result = $manager->migrate($force);

        println('Execution Results:', Colors::CYAN);
        println();

        $success_count = 0;
        $skip_count = 0;
        $error_count = 0;

        foreach ($result['results'] as $filename => $info) {
            if ($info['status'] === 'completed') {
                print_success($filename);
                println('  └─ ' . $info['message'] . ' (' . $info['duration'] . 'ms)', Colors::GREEN);
                $success_count++;
            } elseif ($info['status'] === 'skipped') {
                print_info($filename);
                println('  └─ ' . $info['message'], Colors::BLUE);
                $skip_count++;
            } else {
                print_error($filename);
                println('  └─ ' . $info['message'], Colors::RED);
                $error_count++;
            }
        }

        println();
        println('Summary:', Colors::CYAN);
        printf("  ✅ Executed: %d\n", $success_count);
        printf("  ⏳ Skipped: %d\n", $skip_count);
        printf("  ❌ Failed: %d\n", $error_count);
        println();

        if ($error_count > 0) {
            exit(1);
        }

    } else {
        // Aide
        println('Usage:', Colors::CYAN);
        println();
        println('  php database/migrate.php              # Execute migrations', Colors::YELLOW);
        println('  php database/migrate.php --list       # List migrations', Colors::YELLOW);
        println('  php database/migrate.php --status     # Show status', Colors::YELLOW);
        println('  php database/migrate.php --reset      # Reset tracking', Colors::YELLOW);
        println('  php database/migrate.php --force      # Force re-execute', Colors::YELLOW);
        println();
    }

    exit(0);

} catch (Exception $e) {
    print_error('Error: ' . $e->getMessage());
    if (isset($argv) && in_array('--debug', $argv)) {
        println();
        println($e->getTraceAsString(), Colors::RED);
    }
    exit(1);
}

?>

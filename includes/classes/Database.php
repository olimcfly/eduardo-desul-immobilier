<?php
/**
 * Database Singleton Class
 * /includes/classes/Database.php
 * 
 * Gère la connexion PDO à MySQL
 * Utilisation: $db = Database::getInstance();
 */

class Database {
    private static $instance = null;
    private $connection = null;
    
    /**
     * Database credentials - lues depuis les constantes config.php / .env
     */
    private $host;
    private $port;
    private $dbname;
    private $user;
    private $password;
    private $charset;
    
    /**
     * Private constructor (singleton pattern)
     */
    private function __construct() {
        $this->bootEnvIfNeeded();

        $this->host     = $this->configValue('DB_HOST', 'DB_HOST', 'localhost');
        $this->port     = $this->configValue('DB_PORT', 'DB_PORT', '3306');
        $this->dbname   = $this->configValue('DB_NAME', 'DB_NAME', '');
        $this->user     = $this->configValue('DB_USER', 'DB_USER', '');
        $this->password = $this->configValue('DB_PASS', 'DB_PASS', '');
        $this->charset  = $this->configValue('DB_CHARSET', 'DB_CHARSET', 'utf8mb4');

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset={$this->charset}";
            
            $this->connection = new PDO(
                $dsn,
                $this->user,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
            
            // Set timezone
            $this->connection->exec("SET time_zone = '+00:00'");
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection error: " . $e->getMessage());
        }
    }
    

    /**
     * Charge le .env si le helper env() n'est pas encore disponible.
     */
    private function bootEnvIfNeeded(): void {
        if (function_exists('env')) {
            return;
        }

        $root = dirname(__DIR__, 2);
        $envFile = $root . '/core/env.php';
        if (is_file($envFile)) {
            require_once $envFile;
            if (function_exists('loadEnv')) {
                loadEnv($root . '/.env');
            }
        }
    }

    /**
     * Lit une valeur de config depuis constante -> env() -> getenv() -> défaut.
     */
    private function configValue(string $constant, string $envKey, string $default = ''): string {
        if (defined($constant)) {
            $v = constant($constant);
            return is_string($v) ? $v : (string) $v;
        }

        if (function_exists('env')) {
            $v = env($envKey, $default);
            return is_string($v) ? $v : (string) $v;
        }

        $v = getenv($envKey);
        if ($v === false || $v === '') {
            return $default;
        }

        return (string) $v;
    }

    /**
     * Get the PDO connection instance (PRIMARY METHOD)
     *
     * Returns the singleton PDO connection for database operations.
     * This is the RECOMMENDED method for all database access.
     *
     * @return PDO The database connection
     * @throws Exception If connection fails
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connection;
    }

    /**
     * Get the Database wrapper object (ADVANCED USAGE ONLY)
     *
     * Returns the Database class instance itself.
     * Most code should use getInstance() to get the PDO connection directly.
     * This method is provided for advanced usage that needs access to Database methods.
     *
     * @return Database The Database wrapper object
     */
    public static function getConnection() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Execute a prepared statement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }
    
    /**
     * Fetch all rows
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Fetch single row
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Insert record
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        
        return $this->connection->lastInsertId();
    }
    
    /**
     * Update records
     */
    public function update($table, $data, $where = '', $whereParams = []) {
        $sets = [];
        foreach ($data as $key => $value) {
            $sets[] = "{$key} = ?";
        }
        $setString = implode(', ', $sets);
        
        $whereString = '';
        if (!empty($where)) {
            $whereString = ' WHERE ' . $where;
        }
        
        $sql = "UPDATE {$table} SET {$setString}{$whereString}";
        $params = array_merge(array_values($data), $whereParams);
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Delete records
     */
    public function delete($table, $where = '', $params = []) {
        $whereString = '';
        if (!empty($where)) {
            $whereString = ' WHERE ' . $where;
        }
        
        $sql = "DELETE FROM {$table}{$whereString}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Test connection
     */
    public static function testConnection() {
        try {
            $db = self::getInstance();
            $result = $db->query("SELECT 1");
            return $result->fetch() ? true : false;
        } catch (Exception $e) {
            error_log("Connection test failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserializing
     */
    public function __wakeup() {
        throw new Exception('Cannot unserialize singleton');
    }
}

?>
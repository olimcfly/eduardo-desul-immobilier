<?php
/**
 * BASE CONTROLLER CLASS
 * /includes/classes/BaseController.php
 *
 * Classe de base pour tous les contrôleurs
 * Fournit la gestion d'erreurs et les méthodes communes
 */

class BaseController {

    protected $db;
    protected $errors = [];
    protected $data = [];
    protected $statusCode = 200;

    /**
     * Constructeur
     */
    public function __construct() {
        try {
            if (!class_exists('Database')) {
                require_once dirname(__FILE__) . '/Database.php';
            }
            $this->db = Database::getInstance();
        } catch (Exception $e) {
            ErrorHandler::log($e, 'BaseController::__construct', ['message' => 'Failed to initialize database']);
            $this->db = null;
        }
    }

    /**
     * Exécuter une action de contrôleur avec gestion d'erreurs
     *
     * @param string $action Nom de l'action à exécuter
     * @param array $params Paramètres de l'action
     * @return mixed Résultat de l'action
     */
    protected function executeAction($action, $params = []) {
        try {
            // Vérifier que la méthode existe
            if (!method_exists($this, $action)) {
                throw new Exception("Action '{$action}' not found in " . get_class($this));
            }

            // Vérifier la permission si nécessaire
            if (method_exists($this, 'requirePermission')) {
                $this->requirePermission($action);
            }

            // Exécuter l'action
            return call_user_func_array([$this, $action], $params);

        } catch (Exception $e) {
            return $this->handleError($e, $action);
        }
    }

    /**
     * Gérer une erreur
     *
     * @param Exception $exception L'exception
     * @param string $context Contexte (optional)
     */
    protected function handleError($exception, $context = '') {
        // Logger l'erreur
        ErrorHandler::log($exception, get_class($this) . '::' . $context);

        // Ajouter à la liste des erreurs
        $this->errors[] = [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'context' => $context
        ];

        // Déterminer la réponse selon le contexte
        if ($this->isApiRequest()) {
            return $this->respondJson(
                $exception->getMessage(),
                false,
                $this->statusCode ?: 500,
                [],
                $exception
            );
        }

        return false;
    }

    /**
     * Vérifier si c'est une requête API
     */
    protected function isApiRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' ||
               strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false;
    }

    /**
     * Répondre avec JSON
     */
    protected function respondJson($message = '', $success = true, $statusCode = 200, $data = [], $exception = null) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);

        $response = [
            'success' => $success,
            'message' => $message,
            'status' => $statusCode,
            'timestamp' => date('c')
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        if (DEBUG_MODE && $exception) {
            $response['debug'] = [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ];
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Répondre avec redirection
     */
    protected function redirect($url, $message = '', $messageType = 'info') {
        if (!empty($message)) {
            $_SESSION['message'] = $message;
            $_SESSION['message_type'] = $messageType;
        }
        header('Location: ' . $url);
        exit;
    }

    /**
     * Valider les données
     */
    protected function validate($data, $rules) {
        try {
            foreach ($rules as $field => $fieldRules) {
                if (isset($fieldRules['required']) && $fieldRules['required']) {
                    if (empty($data[$field])) {
                        throw new Exception("Field '$field' is required");
                    }
                }

                if (isset($fieldRules['email']) && $fieldRules['email']) {
                    if (!filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                        throw new Exception("Field '$field' must be a valid email");
                    }
                }

                if (isset($fieldRules['min']) && !empty($data[$field])) {
                    if (strlen($data[$field]) < $fieldRules['min']) {
                        throw new Exception("Field '$field' must be at least {$fieldRules['min']} characters");
                    }
                }

                if (isset($fieldRules['max']) && !empty($data[$field])) {
                    if (strlen($data[$field]) > $fieldRules['max']) {
                        throw new Exception("Field '$field' must be at most {$fieldRules['max']} characters");
                    }
                }
            }

            return true;

        } catch (Exception $e) {
            $this->handleError($e, 'validate');
            return false;
        }
    }

    /**
     * Sanitiser les données
     */
    protected function sanitize($data) {
        try {
            if (is_array($data)) {
                return array_map(function($value) {
                    return is_string($value) ? trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8')) : $value;
                }, $data);
            }
            return is_string($data) ? trim(htmlspecialchars($data, ENT_QUOTES, 'UTF-8')) : $data;

        } catch (Exception $e) {
            ErrorHandler::log($e, 'BaseController::sanitize');
            return $data;
        }
    }

    /**
     * Exécuter une requête DB avec gestion d'erreurs
     */
    protected function query($sql, $params = []) {
        try {
            if (!$this->db) {
                throw new Exception('Database connection not available');
            }

            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $this->db->error);
            }

            if (!$stmt->execute($params)) {
                throw new Exception('Query execution failed: ' . $stmt->error);
            }

            return $stmt;

        } catch (Exception $e) {
            ErrorHandler::log($e, 'BaseController::query', ['sql' => $sql, 'params' => $params]);
            throw $e;
        }
    }

    /**
     * Récupérer les erreurs
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Ajouter un message d'erreur
     */
    protected function addError($message, $code = 0) {
        $this->errors[] = [
            'message' => $message,
            'code' => $code,
            'timestamp' => date('c')
        ];
    }

    /**
     * Vérifier les permissions (à implémenter dans les contrôleurs enfants)
     */
    protected function requirePermission($action) {
        // À surcharger dans les classes enfants si nécessaire
        return true;
    }

    // ════════════════════════════════════════════════════════════
    // RATE LIMITING (P1-6)
    // ════════════════════════════════════════════════════════════

    /**
     * Vérifier le rate limiting par IP
     * Max 100 requêtes par minute par IP
     *
     * @param int $maxRequests Nombre max de requêtes (default: 100)
     * @param int $windowSeconds Fenêtre de temps en secondes (default: 60)
     * @return bool true si OK, false si dépassement
     */
    public function checkRateLimit($maxRequests = 100, $windowSeconds = 60) {
        $clientIp = $this->getClientIp();
        $rateKey = 'ratelimit_' . md5($clientIp);
        $rateFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $rateKey . '.tmp';

        // Lire les données de rate limiting
        $rateLimitData = [];
        if (file_exists($rateFile)) {
            $content = file_get_contents($rateFile);
            $rateLimitData = json_decode($content, true) ?: [];
        }

        $now = time();
        $cutoffTime = $now - $windowSeconds;

        // Filtrer les anciennes requêtes (en dehors de la fenêtre)
        $rateLimitData = array_filter($rateLimitData, function($timestamp) use ($cutoffTime) {
            return $timestamp > $cutoffTime;
        });

        // Vérifier si le limite est dépassé
        if (count($rateLimitData) >= $maxRequests) {
            // Marquer la violation
            ErrorHandler::log(
                new Exception('Rate limit exceeded'),
                'RateLimit::checkRateLimit',
                ['ip' => $clientIp, 'requests' => count($rateLimitData), 'limit' => $maxRequests]
            );
            return false;
        }

        // Enregistrer cette requête
        $rateLimitData[] = $now;
        file_put_contents($rateFile, json_encode($rateLimitData), LOCK_EX);

        // Nettoyer les fichiers de rate limit expirés toutes les 100 requêtes
        if (rand(1, 100) === 1) {
            $this->cleanupRateLimitFiles($windowSeconds);
        }

        return true;
    }

    /**
     * Obtenir l'adresse IP du client
     */
    protected function getClientIp() {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            // Cloudflare
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Proxy/Load Balancer
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED'])) {
            return $_SERVER['HTTP_X_FORWARDED'];
        }
        if (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_FORWARDED_FOR'];
        }
        if (!empty($_SERVER['HTTP_FORWARDED'])) {
            return $_SERVER['HTTP_FORWARDED'];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Nettoyer les fichiers de rate limiting expirés
     */
    private function cleanupRateLimitFiles($windowSeconds) {
        try {
            $tempDir = sys_get_temp_dir();
            $cutoffTime = time() - $windowSeconds;
            $pattern = $tempDir . DIRECTORY_SEPARATOR . 'ratelimit_*.tmp';

            foreach (glob($pattern) as $file) {
                if (is_file($file) && filemtime($file) < $cutoffTime) {
                    @unlink($file);
                }
            }
        } catch (Exception $e) {
            // Silently ignore cleanup errors
        }
    }

    /**
     * Répondre avec erreur rate limit (HTTP 429)
     */
    protected function respondRateLimited() {
        header('Content-Type: application/json; charset=utf-8');
        header('Retry-After: 60');
        http_response_code(429);

        echo json_encode([
            'success' => false,
            'message' => 'Too many requests. Maximum 100 requests per minute allowed.',
            'status' => 429,
            'timestamp' => date('c'),
            'retry_after' => 60
        ]);

        exit;
    }
}

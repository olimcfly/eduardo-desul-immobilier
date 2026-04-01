<?php
/**
 * BaseController - Base class for all controllers with error handling and RBAC
 *
 * Provides:
 * - Centralized error handling with ErrorHandler
 * - Role-based access control (RBAC)
 * - Validation and sanitization helpers
 * - Database query wrapper with error logging
 *
 * @package Eduardo Desul Immobilier
 */

if (!class_exists('Database')) require_once __DIR__ . '/Database.php';
if (!class_exists('ErrorHandler')) require_once __DIR__ . '/ErrorHandler.php';
if (!class_exists('RbacManager')) require_once __DIR__ . '/RbacManager.php';

abstract class BaseController
{
    protected $db;
    protected $errors = [];
    protected $isApi = false;

    /**
     * Constructor - Initialize database connection
     */
    public function __construct()
    {
        try {
            $this->db = Database::getInstance();
            $this->isApi = $this->detectApiContext();
        } catch (Exception $e) {
            ErrorHandler::log($e, 'BaseController.__construct');
            throw $e;
        }
    }

    /**
     * Require specific role to access action
     *
     * @param string $module Module identifier (e.g. 'content_pages')
     * @param string $permission Permission level (view, create, edit, delete, manage)
     *
     * @throws Exception If user doesn't have required permission
     */
    protected function requireRole(string $module, string $permission = RbacManager::PERM_VIEW): void
    {
        // Get user role from session
        $userRole = $_SESSION['auth_admin_role'] ?? RbacManager::ROLE_VIEWER;

        // Check permission
        if (!RbacManager::hasPermission($userRole, $module, $permission)) {
            ErrorHandler::respond(
                'Accès refusé: Vous n\'avez pas les permissions pour accéder à ce module.',
                403,
                ['required_role' => $module, 'required_permission' => $permission],
                null
            );
        }
    }

    /**
     * Require specific roles (multiple roles check)
     *
     * @param array $modules Array of module identifiers to check
     * @param string $permission Permission level
     *
     * @throws Exception If user doesn't have any of the required permissions
     */
    protected function requireAnyRole(array $modules, string $permission = RbacManager::PERM_VIEW): void
    {
        $userRole = $_SESSION['auth_admin_role'] ?? RbacManager::ROLE_VIEWER;
        $hasPermission = false;

        foreach ($modules as $module) {
            if (RbacManager::hasPermission($userRole, $module, $permission)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            ErrorHandler::respond(
                'Accès refusé: Vous n\'avez pas les permissions pour accéder à ce module.',
                403,
                ['required_modules' => $modules, 'required_permission' => $permission],
                null
            );
        }
    }

    /**
     * Execute action with error handling
     *
     * @param string $action Action name
     * @param array $params Action parameters
     *
     * @return mixed Action result
     */
    public function executeAction(string $action, array $params = []): mixed
    {
        try {
            $method = 'action' . ucfirst($action);

            if (!method_exists($this, $method)) {
                throw new Exception("Action '{$action}' non implémentée");
            }

            return $this->$method($params);
        } catch (Exception $e) {
            return $this->handleError($e, $action);
        }
    }

    /**
     * Handle errors consistently
     *
     * @param Exception $exception The exception
     * @param string $context Context information
     *
     * @return array Error response
     */
    protected function handleError(Exception $exception, string $context = ''): array
    {
        ErrorHandler::log($exception, $context);

        if ($this->isApi) {
            return [
                'success' => false,
                'message' => $exception->getMessage(),
                'context' => $context
            ];
        }

        return ['error' => $exception->getMessage()];
    }

    /**
     * Detect if this is an API request
     *
     * @return bool True if API request
     */
    protected function detectApiContext(): bool
    {
        // Check content-type header
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            return true;
        }

        // Check if request is to /api/ endpoint
        if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) {
            return true;
        }

        // Check for X-Requested-With header
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            return true;
        }

        return false;
    }

    /**
     * Respond with JSON (for API)
     *
     * @param string $message Response message
     * @param bool $success Success status
     * @param int $statusCode HTTP status code
     * @param array $data Additional data
     * @param Exception $exception Optional exception for debugging
     *
     * @return array Response array
     */
    protected function respondJson(string $message, bool $success = true, int $statusCode = 200, array $data = [], ?Exception $exception = null): array
    {
        $response = [
            'success' => $success,
            'message' => $message,
            'status' => $statusCode,
            'timestamp' => date('c'),
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        if (defined('DEBUG_MODE') && DEBUG_MODE && $exception) {
            $response['debug'] = [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        return $response;
    }

    /**
     * Redirect with message
     *
     * @param string $url Target URL
     * @param string $message Flash message
     * @param string $messageType Message type (success, error, warning, info)
     *
     * @return never
     */
    protected function redirect(string $url, string $message = '', string $messageType = 'success'): never
    {
        if ($message) {
            $_SESSION['auth_flash_message'] = $message;
            $_SESSION['auth_flash_type'] = $messageType;
        }

        header("Location: " . htmlspecialchars($url, ENT_QUOTES, 'UTF-8'));
        exit;
    }

    /**
     * Validate data against rules
     *
     * @param array $data Data to validate
     * @param array $rules Validation rules
     *
     * @return bool True if validation passes
     */
    protected function validate(array $data, array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? '';

            // Check required
            if (isset($fieldRules['required']) && $fieldRules['required'] && empty($value)) {
                $this->errors[$field] = "Le champ '{$field}' est requis";
                continue;
            }

            if (empty($value)) {
                continue;
            }

            // Check email
            if (isset($fieldRules['email']) && $fieldRules['email']) {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field] = "Le champ '{$field}' doit être un email valide";
                }
            }

            // Check min length
            if (isset($fieldRules['min'])) {
                if (strlen($value) < $fieldRules['min']) {
                    $this->errors[$field] = "Le champ '{$field}' doit contenir au moins {$fieldRules['min']} caractères";
                }
            }

            // Check max length
            if (isset($fieldRules['max'])) {
                if (strlen($value) > $fieldRules['max']) {
                    $this->errors[$field] = "Le champ '{$field}' doit contenir au maximum {$fieldRules['max']} caractères";
                }
            }

            // Check pattern
            if (isset($fieldRules['pattern'])) {
                if (!preg_match($fieldRules['pattern'], $value)) {
                    $this->errors[$field] = "Le champ '{$field}' n'a pas le format valide";
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Sanitize data
     *
     * @param array $data Data to sanitize
     * @param array $fields Fields to sanitize (null = all)
     *
     * @return array Sanitized data
     */
    protected function sanitize(array $data, ?array $fields = null): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if ($fields && !in_array($key, $fields)) {
                $sanitized[$key] = $value;
                continue;
            }

            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Execute database query with error handling
     *
     * @param string $sql SQL query
     * @param array $params Bind parameters
     *
     * @return PDOStatement Statement object
     *
     * @throws Exception If query fails
     */
    protected function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            ErrorHandler::log($e, 'BaseController.query', ['sql' => $sql]);
            throw new Exception("Erreur de base de données");
        }
    }

    /**
     * Get errors
     *
     * @return array List of errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Add error
     *
     * @param string $field Field name
     * @param string $message Error message
     */
    public function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    /**
     * Check if there are errors
     *
     * @return bool True if there are errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Clear errors
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }
}

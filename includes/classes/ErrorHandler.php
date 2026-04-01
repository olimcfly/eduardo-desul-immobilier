<?php
/**
 * ERROR HANDLER CLASS
 * /includes/classes/ErrorHandler.php
 *
 * Gère les erreurs et exceptions de manière uniforme dans toute l'application
 * Fournit des méthodes pour logger, afficher et répondre aux erreurs
 */

class ErrorHandler {

    /**
     * Types d'erreur
     */
    const ERROR = 'error';
    const WARNING = 'warning';
    const INFO = 'info';
    const SUCCESS = 'success';

    /**
     * Contextes d'erreur
     */
    const CONTEXT_API = 'api';
    const CONTEXT_WEB = 'web';
    const CONTEXT_CLI = 'cli';

    /**
     * HTTP Status codes
     */
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_CONFLICT = 409;
    const HTTP_UNPROCESSABLE = 422;
    const HTTP_INTERNAL_ERROR = 500;
    const HTTP_SERVICE_UNAVAILABLE = 503;

    /**
     * Store des erreurs
     */
    private static $errors = [];
    private static $context = self::CONTEXT_WEB;

    /**
     * Déterminer le contexte automatiquement
     */
    public static function detectContext() {
        if (php_sapi_name() === 'cli') {
            self::$context = self::CONTEXT_CLI;
        } elseif (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            self::$context = self::CONTEXT_API;
        } elseif (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) {
            self::$context = self::CONTEXT_API;
        } else {
            self::$context = self::CONTEXT_WEB;
        }
        return self::$context;
    }

    /**
     * Logger une erreur ou exception
     *
     * @param Exception|Throwable $exception L'exception à logger
     * @param string $context Contexte de l'erreur (optional)
     * @param array $extra Données supplémentaires (optional)
     */
    public static function log($exception, $context = null, $extra = []) {
        if (!($exception instanceof Throwable) && !($exception instanceof Exception)) {
            $exception = new Exception((string)$exception);
        }

        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
            'context' => $context ?? self::detectContext(),
            'trace' => DEBUG_MODE ? $exception->getTraceAsString() : null,
            'extra' => $extra
        ];

        // Enregistrer dans le fichier de log
        self::writeLog($logEntry);

        // Stocker en mémoire
        self::$errors[] = $logEntry;

        return $logEntry;
    }

    /**
     * Écrire dans le fichier de log
     */
    private static function writeLog($logEntry) {
        if (!defined('ROOT_PATH')) {
            return;
        }

        $logDir = ROOT_PATH . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/errors.log';
        $logContent = json_encode($logEntry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        file_put_contents($logFile, $logContent . "\n\n", FILE_APPEND);
    }

    /**
     * Répondre avec une erreur (API ou Web)
     *
     * @param string $message Message d'erreur
     * @param int $httpStatus Code HTTP
     * @param array $data Données additionnelles (optionnel)
     * @param Exception $exception Exception (optionnel)
     */
    public static function respond($message, $httpStatus = 500, $data = [], $exception = null) {
        // Logger l'erreur
        if ($exception) {
            self::log($exception, 'error_response', ['message' => $message, 'status' => $httpStatus]);
        }

        // Détecter le contexte
        $context = self::detectContext();

        // Répondre selon le contexte
        if ($context === self::CONTEXT_API) {
            self::respondJson($message, false, $httpStatus, $data, $exception);
        } else {
            self::respondHtml($message, $httpStatus, $data, $exception);
        }
    }

    /**
     * Répondre avec JSON (API)
     */
    private static function respondJson($message, $success = false, $httpStatus = 500, $data = [], $exception = null) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($httpStatus);

        $response = [
            'success' => $success,
            'message' => $message,
            'status' => $httpStatus,
            'timestamp' => date('c')
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        if (DEBUG_MODE && $exception) {
            $response['debug'] = [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Répondre avec HTML (Web)
     */
    private static function respondHtml($message, $httpStatus = 500, $data = [], $exception = null) {
        http_response_code($httpStatus);

        $title = self::getHttpStatusText($httpStatus);
        $cssClass = $httpStatus >= 500 ? 'error' : ($httpStatus >= 400 ? 'warning' : 'info');
        $debugInfo = '';

        if (DEBUG_MODE && $exception) {
            $debugInfo = "
                <h3>Debug Information</h3>
                <p><strong>Exception:</strong> " . htmlspecialchars(get_class($exception)) . "</p>
                <p><strong>File:</strong> " . htmlspecialchars($exception->getFile()) . ":" . $exception->getLine() . "</p>
                <p><strong>Trace:</strong></p>
                <pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>
            ";
        }

        echo "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>$title</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
                .container { max-width: 600px; margin: 50px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .error { border-left: 4px solid #dc2626; }
                .warning { border-left: 4px solid #f59e0b; }
                .info { border-left: 4px solid #3b82f6; }
                h1 { margin-top: 0; color: #1f2937; }
                .error h1 { color: #dc2626; }
                .warning h1 { color: #f59e0b; }
                .info h1 { color: #3b82f6; }
                p { color: #6b7280; line-height: 1.6; }
                pre { background: #f3f4f6; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
                a { color: #3b82f6; text-decoration: none; }
                a:hover { text-decoration: underline; }
            </style>
        </head>
        <body>
            <div class='container $cssClass'>
                <h1>$title</h1>
                <p>" . htmlspecialchars($message) . "</p>
                $debugInfo
                <p><a href='javascript:history.back()'>← Go back</a></p>
            </div>
        </body>
        </html>
        ";
        exit;
    }

    /**
     * Obtenir le texte du code HTTP
     */
    private static function getHttpStatusText($code) {
        $statuses = [
            200 => 'OK',
            201 => 'Created',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            409 => 'Conflict',
            422 => 'Unprocessable Entity',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable'
        ];
        return $statuses[$code] ?? 'Error';
    }

    /**
     * Récupérer toutes les erreurs enregistrées
     */
    public static function getErrors() {
        return self::$errors;
    }

    /**
     * Effacer les erreurs
     */
    public static function clearErrors() {
        self::$errors = [];
    }

    /**
     * Vérifier s'il y a des erreurs
     */
    public static function hasErrors() {
        return !empty(self::$errors);
    }

    /**
     * Compter les erreurs
     */
    public static function countErrors() {
        return count(self::$errors);
    }
}
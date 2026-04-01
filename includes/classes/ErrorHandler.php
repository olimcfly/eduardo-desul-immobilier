<?php
/**
 * ErrorHandler - Centralized error handling and logging
 *
 * Provides unified error logging, response formatting, and context-aware error handling.
 *
 * @package Eduardo Desul Immobilier
 */

class ErrorHandler
{
    // Error types
    const ERROR = 'error';
    const WARNING = 'warning';
    const INFO = 'info';
    const SUCCESS = 'success';

    // HTTP Status Codes
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

    private static $errors = [];

    /**
     * Log exception to file
     *
     * @param Exception $exception The exception
     * @param string $context Context information
     * @param array $extra Extra data to log
     */
    public static function log(Exception $exception, string $context = '', array $extra = []): void
    {
        $logsDir = defined('LOGS_PATH') ? LOGS_PATH : ROOT_PATH . '/logs';

        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0755, true);
        }

        $logFile = $logsDir . '/errors.log';
        $timestamp = date('Y-m-d H:i:s');

        $message = "[$timestamp] [{$context}] " . $exception->getMessage();

        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            $message .= "\n  File: " . $exception->getFile() . ':' . $exception->getLine();
            $message .= "\n  Trace: " . str_replace("\n", "\n  ", $exception->getTraceAsString());
        }

        if (!empty($extra)) {
            $message .= "\n  Extra: " . json_encode($extra);
        }

        $message .= "\n";

        error_log($message, 3, $logFile);
    }

    /**
     * Unified error response based on context
     *
     * @param string $message Error message
     * @param int $httpStatus HTTP status code
     * @param array $data Additional data
     * @param Exception $exception Optional exception
     *
     * @return never
     */
    public static function respond(string $message, int $httpStatus = 500, array $data = [], ?Exception $exception = null): never
    {
        http_response_code($httpStatus);

        $context = self::detectContext();

        if ($context === 'api') {
            self::respondJson($message, false, $httpStatus, $data, $exception);
        } elseif ($context === 'cli') {
            self::respondCli($message, $httpStatus, $exception);
        } else {
            self::respondHtml($message, $httpStatus, $data, $exception);
        }
    }

    /**
     * Detect request context
     *
     * @return string 'api', 'web', or 'cli'
     */
    public static function detectContext(): string
    {
        // Check if CLI
        if (php_sapi_name() === 'cli') {
            return 'cli';
        }

        // Check if API request
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            return 'api';
        }

        if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) {
            return 'api';
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            return 'api';
        }

        // Default to web
        return 'web';
    }

    /**
     * JSON error response for API
     *
     * @param string $message Error message
     * @param bool $success Success status
     * @param int $httpStatus HTTP status code
     * @param array $data Additional data
     * @param Exception $exception Optional exception
     *
     * @return never
     */
    public static function respondJson(string $message, bool $success = false, int $httpStatus = 500, array $data = [], ?Exception $exception = null): never
    {
        $response = [
            'success' => $success,
            'message' => $message,
            'status' => $httpStatus,
            'timestamp' => date('c'),
        ];

        if (!empty($data)) {
            $response = array_merge($response, $data);
        }

        // Include debug info in development
        if (defined('DEBUG_MODE') && DEBUG_MODE && $exception) {
            $response['debug'] = [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => explode("\n", $exception->getTraceAsString()),
            ];
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * HTML error response for web
     *
     * @param string $message Error message
     * @param int $httpStatus HTTP status code
     * @param array $data Additional data
     * @param Exception $exception Optional exception
     *
     * @return never
     */
    public static function respondHtml(string $message, int $httpStatus = 500, array $data = [], ?Exception $exception = null): never
    {
        $title = "Erreur $httpStatus";
        $descriptions = [
            400 => 'Requête invalide',
            401 => 'Non authentifié',
            403 => 'Accès refusé',
            404 => 'Non trouvé',
            409 => 'Conflit',
            422 => 'Entité non traitable',
            500 => 'Erreur serveur',
            503 => 'Service indisponible',
        ];

        $description = $descriptions[$httpStatus] ?? 'Une erreur est survenue';

        header('Content-Type: text/html; charset=utf-8');

        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= $title ?></title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                .error-container {
                    background: white;
                    border-radius: 12px;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                    max-width: 500px;
                    padding: 40px;
                    text-align: center;
                }
                .error-code {
                    font-size: 72px;
                    font-weight: 700;
                    color: #667eea;
                    margin-bottom: 10px;
                }
                .error-title {
                    font-size: 24px;
                    font-weight: 600;
                    color: #1f2937;
                    margin-bottom: 10px;
                }
                .error-description {
                    font-size: 16px;
                    color: #6b7280;
                    margin-bottom: 20px;
                }
                .error-message {
                    background: #f3f4f6;
                    border-left: 4px solid #667eea;
                    padding: 15px;
                    border-radius: 6px;
                    margin-bottom: 20px;
                    text-align: left;
                    font-size: 14px;
                    color: #374151;
                    word-break: break-word;
                }
                .error-debug {
                    background: #fef2f2;
                    border: 1px solid #fecaca;
                    border-radius: 6px;
                    padding: 15px;
                    margin-bottom: 20px;
                    text-align: left;
                    font-family: monospace;
                    font-size: 12px;
                    color: #991b1b;
                    max-height: 200px;
                    overflow-y: auto;
                }
                .error-debug strong {
                    display: block;
                    margin-top: 10px;
                    margin-bottom: 5px;
                }
                .error-actions {
                    display: flex;
                    gap: 10px;
                    flex-direction: column;
                }
                .btn {
                    padding: 10px 20px;
                    border-radius: 6px;
                    text-decoration: none;
                    font-weight: 600;
                    display: inline-block;
                    cursor: pointer;
                    border: none;
                    font-size: 14px;
                    transition: all 0.2s ease;
                }
                .btn-primary {
                    background: #667eea;
                    color: white;
                }
                .btn-primary:hover {
                    background: #5a67d8;
                }
                .btn-secondary {
                    background: #e5e7eb;
                    color: #374151;
                }
                .btn-secondary:hover {
                    background: #d1d5db;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-code"><?= $httpStatus ?></div>
                <div class="error-title"><?= htmlspecialchars($title) ?></div>
                <div class="error-description"><?= htmlspecialchars($description) ?></div>
                <div class="error-message"><?= htmlspecialchars($message) ?></div>

                <?php if (defined('DEBUG_MODE') && DEBUG_MODE && $exception): ?>
                    <div class="error-debug">
                        <strong>Debug Information:</strong>
                        <div><strong>Exception:</strong> <?= htmlspecialchars(get_class($exception)) ?></div>
                        <div><strong>File:</strong> <?= htmlspecialchars($exception->getFile()) ?></div>
                        <div><strong>Line:</strong> <?= $exception->getLine() ?></div>
                        <div><strong>Trace:</strong></div>
                        <pre><?= htmlspecialchars($exception->getTraceAsString()) ?></pre>
                    </div>
                <?php endif; ?>

                <div class="error-actions">
                    <button class="btn btn-primary" onclick="history.back()">← Retour</button>
                    <a href="/" class="btn btn-secondary">Accueil</a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    /**
     * CLI error response
     *
     * @param string $message Error message
     * @param int $httpStatus HTTP status code
     * @param Exception $exception Optional exception
     *
     * @return never
     */
    public static function respondCli(string $message, int $httpStatus = 500, ?Exception $exception = null): never
    {
        echo "\n\n❌ ERROR [$httpStatus]: " . $message . "\n";

        if ($exception && (defined('DEBUG_MODE') && DEBUG_MODE)) {
            echo "\nStack Trace:\n";
            echo $exception->getTraceAsString() . "\n";
        }

        echo "\n";
        exit(1);
    }

    /**
     * Get all collected errors
     *
     * @return array List of errors
     */
    public static function getErrors(): array
    {
        return self::$errors;
    }

    /**
     * Check if there are errors
     *
     * @return bool True if there are errors
     */
    public static function hasErrors(): bool
    {
        return !empty(self::$errors);
    }

    /**
     * Count errors
     *
     * @return int Number of errors
     */
    public static function countErrors(): int
    {
        return count(self::$errors);
    }

    /**
     * Clear all errors
     */
    public static function clearErrors(): void
    {
        self::$errors = [];
    }

    /**
     * Add an error message
     *
     * @param string $message Error message
     * @param string $type Error type
     */
    public static function addError(string $message, string $type = self::ERROR): void
    {
        self::$errors[] = [
            'message' => $message,
            'type' => $type,
            'timestamp' => date('c'),
        ];
    }
}

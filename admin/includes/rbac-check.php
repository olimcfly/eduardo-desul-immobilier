<?php
/**
 * RBAC Security Check for Admin Modules
 *
 * Include this file at the beginning of each admin module to enforce role-based access control.
 *
 * Usage in admin modules:
 *   <?php
 *   require_once __DIR__ . '/rbac-check.php';
 *   checkModuleAccess('content_pages', RbacManager::PERM_VIEW);
 *   ?>
 *
 * @package Eduardo Desul Immobilier
 */

if (!class_exists('RbacManager')) {
    require_once dirname(__DIR__, 2) . '/includes/classes/RbacManager.php';
}

/**
 * Check if current user has access to module
 *
 * @param string $module Module identifier
 * @param string $permission Required permission
 * @param string $page Current page name (optional, auto-detected if not provided)
 *
 * @return void Dies if access denied
 */
function checkModuleAccess(string $module, string $permission = RbacManager::PERM_VIEW, ?string $page = null): void
{
    // Get user role from session
    $userRole = $_SESSION['auth_admin_role'] ?? RbacManager::ROLE_VIEWER;
    $userId = $_SESSION['auth_admin_id'] ?? 0;

    // Check if user is authenticated
    if (empty($userId) || empty($userRole)) {
        http_response_code(401);
        die('<div style="background:#fee2e2;color:#991b1b;padding:20px;border-radius:10px;margin:20px">
            ⚠️ Accès refusé: Vous n\'êtes pas authentifié.
            </div>');
    }

    // Check permission
    if (!RbacManager::hasPermission($userRole, $module, $permission)) {
        http_response_code(403);
        die('<div style="background:#fee2e2;color:#991b1b;padding:20px;border-radius:10px;margin:20px">
            ⚠️ Accès refusé: Vous n\'avez pas les permissions pour accéder à ce module.<br>
            <small>Module: ' . htmlspecialchars($module) . ' | Permission: ' . htmlspecialchars($permission) . ' | Role: ' . htmlspecialchars($userRole) . '</small>
            </div>');
    }

    // Log access
    logModuleAccess($userId, $module, $permission, true);
}

/**
 * Check access to specific action
 *
 * @param string $module Module identifier
 * @param string $action Action (view, create, edit, delete, manage)
 *
 * @return bool True if allowed, false otherwise
 */
function hasModuleAccess(string $module, string $action = RbacManager::PERM_VIEW): bool
{
    $userRole = $_SESSION['auth_admin_role'] ?? RbacManager::ROLE_VIEWER;
    return RbacManager::hasPermission($userRole, $module, $action);
}

/**
 * Get current user role
 *
 * @return string User role
 */
function getCurrentUserRole(): string
{
    return $_SESSION['auth_admin_role'] ?? RbacManager::ROLE_VIEWER;
}

/**
 * Get current user ID
 *
 * @return int|null User ID or null if not authenticated
 */
function getCurrentUserId(): ?int
{
    return $_SESSION['auth_admin_id'] ?? null;
}

/**
 * Log module access
 *
 * @param int $userId User ID
 * @param string $module Module identifier
 * @param string $permission Permission
 * @param bool $allowed Access allowed
 *
 * @return void
 */
function logModuleAccess(int $userId, string $module, string $permission, bool $allowed): void
{
    $timestamp = date('Y-m-d H:i:s');
    $userRole = getCurrentUserRole();
    $status = $allowed ? 'ALLOWED' : 'DENIED';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    $logMessage = "[$timestamp] [$status] User=$userId | Role=$userRole | Module=$module | Permission=$permission | IP=$ip\n";

    $logsDir = defined('LOGS_PATH') ? LOGS_PATH : dirname(__DIR__, 2) . '/logs';
    if (!is_dir($logsDir)) {
        mkdir($logsDir, 0755, true);
    }

    error_log($logMessage, 3, $logsDir . '/rbac-access.log');
}

/**
 * Display permission denied error
 *
 * @param string $module Module name
 * @param string $action Action name
 * @param string $message Custom message (optional)
 *
 * @return void
 */
function showAccessDenied(string $module = '', string $action = '', string $message = ''): void
{
    $userRole = getCurrentUserRole();

    if (empty($message)) {
        $message = "Vous n'avez pas les permissions pour accéder à ce module.";
    }

    $html = '
    <div style="
        background: #fee2e2;
        color: #991b1b;
        border-left: 4px solid #dc2626;
        padding: 16px;
        border-radius: 8px;
        margin: 20px;
        max-width: 600px;
    ">
        <h3 style="margin: 0 0 8px 0; font-size: 18px;">⚠️ Accès refusé</h3>
        <p style="margin: 8px 0; font-size: 14px;">' . htmlspecialchars($message) . '</p>';

    if (!empty($module) || !empty($action)) {
        $html .= '<p style="margin: 8px 0; font-size: 12px; opacity: 0.8;">';
        if (!empty($module)) {
            $html .= 'Module: <strong>' . htmlspecialchars($module) . '</strong> | ';
        }
        if (!empty($action)) {
            $html .= 'Action: <strong>' . htmlspecialchars($action) . '</strong>';
        }
        $html .= '</p>';
    }

    $html .= '
        <p style="margin: 12px 0 0 0; font-size: 12px; opacity: 0.8;">
            Votre rôle: <strong>' . htmlspecialchars(RbacManager::getRoleLabel($userRole)) . '</strong>
        </p>
    </div>';

    echo $html;
}

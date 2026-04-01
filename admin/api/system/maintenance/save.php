<?php
/**
 * API Maintenance
 * /admin/api/system/maintenance/save.php
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Charger config.php EN PREMIER pour qu'il démarre la session avec le bon nom
// (ECOSYSTEM_EDUARDO-BORDEAUX) — sinon session_start() utilise PHPSESSID
// et ne retrouve pas admin_id
require_once dirname(__FILE__, 4) . '/config/config.php';

// Vérifier l'authentification admin (JSON au lieu d'un redirect HTML)
$isAuthenticated = !empty($_SESSION['auth_admin_id'])
    || !empty($_SESSION['auth_admin_logged_in'])
    || !empty($_SESSION['auth_user_id']);

if (!$isAuthenticated) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}
if (!class_exists('Database')) require_once ROOT_PATH . '/includes/classes/Database.php';

    if (!class_exists('Database')) require_once ROOT_PATH . '/includes/classes/Database.php';
$pdo = Database::getInstance();

$rawInput = file_get_contents('php://input');
$jsonInput = json_decode($rawInput ?: '', true);
if (!is_array($jsonInput)) {
    $jsonInput = [];
}

$input = array_merge($jsonInput, $_POST);
$action = trim((string)($input['action'] ?? ''));

/**
 * Nettoyage SQL minimal pour les noms de table/colonne.
 */
function qident(string $name): string
{
    return '`' . str_replace('`', '``', $name) . '`';
}

/**
 * Retourne la liste des tables de la base courante.
 *
 * @return string[]
 */
function listTables(PDO $pdo): array
{
    $rows = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_NUM) ?: [];
    $tables = [];
    foreach ($rows as $r) {
        if (!empty($r[0]) && is_string($r[0])) {
            $tables[] = $r[0];
        }
    }
    return $tables;
}

/**
 * Vérifie la présence d'une colonne.
 */
function hasColumn(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare("SHOW COLUMNS FROM " . qident($table) . " LIKE ?");
    $stmt->execute([$column]);
    return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
}

try {
    // Créer la table si absente
    $pdo->exec("CREATE TABLE IF NOT EXISTS maintenance (
        id          INT PRIMARY KEY AUTO_INCREMENT,
        is_active   TINYINT(1) NOT NULL DEFAULT 0,
        message     TEXT,
        allowed_ips TEXT,
        end_date    DATETIME NULL,
        updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Ligne par défaut si vide
    $count = (int)$pdo->query("SELECT COUNT(*) FROM maintenance")->fetchColumn();
    if ($count === 0) {
        $pdo->exec("INSERT INTO maintenance (id, is_active, message, allowed_ips) VALUES (1, 0, '', '127.0.0.1')");
    }

    switch ($action) {
        case 'toggle':
            $val = (int)($input['is_active'] ?? 0);
            $val = $val === 1 ? 1 : 0;
            $pdo->prepare("UPDATE maintenance SET is_active = ? WHERE id = 1")->execute([$val]);
            echo json_encode(['success' => true, 'is_active' => $val]);
            break;

        case 'save_message':
            $msg = trim((string)($input['message'] ?? ''));
            $pdo->prepare("UPDATE maintenance SET message = ? WHERE id = 1")->execute([$msg]);
            echo json_encode(['success' => true]);
            break;

        case 'save_whitelist':
            $ips = trim((string)($input['allowed_ips'] ?? ''));
            $pdo->prepare("UPDATE maintenance SET allowed_ips = ? WHERE id = 1")->execute([$ips]);
            echo json_encode(['success' => true]);
            break;

        // Compatibilité: certaines implémentations front envoient action=save
        case 'save':
            $msg = trim((string)($input['message'] ?? ''));
            $ips = trim((string)($input['allowed_ips'] ?? ''));
            $endDate = trim((string)($input['end_date'] ?? ''));

            $parsedEndDate = null;
            if ($endDate !== '') {
                $timestamp = strtotime($endDate);
                if ($timestamp !== false) {
                    $parsedEndDate = date('Y-m-d H:i:s', $timestamp);
                }
            }

            $pdo->prepare("UPDATE maintenance SET message = ?, allowed_ips = ?, end_date = ? WHERE id = 1")
                ->execute([$msg, $ips, $parsedEndDate]);

            if (array_key_exists('is_active', $input)) {
                $isActive = (int)$input['is_active'] === 1 ? 1 : 0;
                $pdo->prepare("UPDATE maintenance SET is_active = ? WHERE id = 1")->execute([$isActive]);
            }

            echo json_encode(['success' => true]);
            break;

        case 'danger_reset':
            $scope = trim((string)($input['scope'] ?? ''));
            $confirmation = trim((string)($input['confirmation'] ?? ''));
            $doubleConfirmation = trim((string)($input['double_confirmation'] ?? ''));

            if ($confirmation !== 'SUPPRIMER DEFINITIVEMENT') {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'Confirmation manuelle invalide.']);
                break;
            }

            if ($scope === 'all' && $doubleConfirmation !== 'EFFACER TOUTES LES DONNÉES MÉTIER') {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'Double confirmation invalide (données métier).']);
                break;
            }
            if ($scope === 'stats' && $doubleConfirmation !== 'EFFACER TOUTES LES STATISTIQUES') {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'Double confirmation invalide (statistiques).']);
                break;
            }

            if (!in_array($scope, ['all', 'stats'], true)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Scope invalide.']);
                break;
            }

            $tables = listTables($pdo);
            $excluded = [
                'admins', 'admin_logs', 'admin_sessions', 'admin_tenant_memberships',
                'tenants', 'tenant_context', 'tenant_settings',
                'settings', 'setting', 'module_settings', 'modules',
                'migrations', 'maintenance', 'licenses', 'license_keys',
                'api_keys', 'roles', 'role_permissions', 'permissions'
            ];

            $statsDeleteTables = [
                'analytics', 'analytics_events', 'page_views', 'visits', 'visitor_logs',
                'statistics', 'stats', 'tracking_events', 'event_logs', 'lead_events'
            ];
            $statsResetColumns = ['vues', 'views', 'impressions', 'conversions', 'clicks', 'visits_count', 'score'];

            $pdo->beginTransaction();
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

            $affected = [];
            if ($scope === 'all') {
                foreach ($tables as $table) {
                    if (in_array($table, $excluded, true)) {
                        continue;
                    }
                    $pdo->exec("TRUNCATE TABLE " . qident($table));
                    $affected[] = $table;
                }
            } else {
                foreach ($tables as $table) {
                    if (in_array($table, $excluded, true)) {
                        continue;
                    }

                    if (in_array($table, $statsDeleteTables, true)) {
                        $pdo->exec("TRUNCATE TABLE " . qident($table));
                        $affected[] = $table . ' (truncate)';
                        continue;
                    }

                    $setParts = [];
                    foreach ($statsResetColumns as $col) {
                        if (hasColumn($pdo, $table, $col)) {
                            // Validate column name is in whitelist to prevent SQL injection
                            if (in_array($col, $statsResetColumns, true)) {
                                $setParts[] = qident($col) . " = 0";
                            }
                        }
                    }
                    if ($setParts) {
                        // Build and execute UPDATE with validated table and column names
                        $updateSql = "UPDATE " . qident($table) . " SET " . implode(', ', $setParts);
                        $pdo->exec($updateSql);
                        $affected[] = $table . ' (reset compteurs)';
                    }
                }
            }

            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => $scope === 'all'
                    ? 'Toutes les données métier ont été effacées.'
                    : 'Les statistiques ont été réinitialisées.',
                'affected_count' => count($affected)
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Action inconnue : ' . $action]);
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
        try { $pdo->exec("SET FOREIGN_KEY_CHECKS = 1"); } catch (Exception $ignored) {}
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

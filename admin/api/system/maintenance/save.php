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
if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$pdo = getDB();

$rawInput = file_get_contents('php://input');
$jsonInput = json_decode($rawInput ?: '', true);
if (!is_array($jsonInput)) {
    $jsonInput = [];
}

$input = array_merge($jsonInput, $_POST);
$action = trim((string)($input['action'] ?? ''));

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

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Action inconnue : ' . $action]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

<?php
/**
 * API Maintenance
 */

require_once dirname(__FILE__, 4) . '/includes/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$action = trim($_POST['action'] ?? '');

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
    $count = $pdo->query("SELECT COUNT(*) FROM maintenance")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO maintenance (id, is_active, message, allowed_ips) VALUES (1, 0, '', '127.0.0.1')");
    }

    switch ($action) {

        case 'toggle':
            $val = (int)($_POST['is_active'] ?? 0);
            $pdo->prepare("UPDATE maintenance SET is_active = ? WHERE id = 1")->execute([$val]);
            echo json_encode(['success' => true, 'is_active' => $val]);
            break;

        case 'save_message':
            $msg = trim($_POST['message'] ?? '');
            $pdo->prepare("UPDATE maintenance SET message = ? WHERE id = 1")->execute([$msg]);
            echo json_encode(['success' => true]);
            break;

        case 'save_whitelist':
            $ips = trim($_POST['allowed_ips'] ?? '');
            $pdo->prepare("UPDATE maintenance SET allowed_ips = ? WHERE id = 1")->execute([$ips]);
            echo json_encode(['success' => true]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Action inconnue : ' . $action]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
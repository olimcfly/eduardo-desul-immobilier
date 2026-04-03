<?php
require_once __DIR__ . '/../../core/services/ModuleService.php';

header('Content-Type: application/json; charset=utf-8');

$db = Database::getInstance();
$db->exec(
    'CREATE TABLE IF NOT EXISTS admin_page_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        superadmin_id INT NOT NULL,
        user_id INT NOT NULL,
        page_url VARCHAR(255),
        status ENUM("pending", "allowed", "denied") DEFAULT "pending",
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        responded_at TIMESTAMP NULL DEFAULT NULL,
        INDEX idx_user_status (user_id, status),
        INDEX idx_superadmin_created (superadmin_id, created_at),
        CONSTRAINT fk_apr_superadmin FOREIGN KEY (superadmin_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_apr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);

$authUser = Auth::user();
if (!$authUser) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Non authentifié.']);
    exit;
}

$action = isset($_GET['action']) ? preg_replace('/[^a-z_-]/', '', (string) $_GET['action']) : 'page_request';

if ($action === 'page_request') {
    if (($authUser['role'] ?? '') !== 'superadmin') {
        http_response_code(403);
        echo json_encode(['ok' => false, 'message' => 'Accès réservé au superadmin.']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'message' => 'Méthode non autorisée.']);
        exit;
    }

    $userId = (int) ($_POST['user_id'] ?? 0);
    $pageUrl = trim((string) ($_POST['page_url'] ?? ''));

    if ($userId <= 0) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'message' => 'Utilisateur invalide.']);
        exit;
    }

    $stmt = $db->prepare(
        'INSERT INTO admin_page_requests (superadmin_id, user_id, page_url, status)
         VALUES (:superadmin_id, :user_id, :page_url, "pending")'
    );
    $stmt->execute([
        'superadmin_id' => (int) $authUser['id'],
        'user_id' => $userId,
        'page_url' => mb_substr($pageUrl, 0, 255),
    ]);

    echo json_encode(['ok' => true, 'request_id' => (int) $db->lastInsertId()]);
    exit;
}

if ($action === 'poll_request') {
    if (($authUser['role'] ?? '') === 'user') {
        ModuleService::trackUserPagePresence((int) $authUser['id'], $_SERVER['REQUEST_URI'] ?? '/');

        $stmt = $db->prepare(
            'SELECT id, superadmin_id, user_id, page_url, status, created_at
             FROM admin_page_requests
             WHERE user_id = :user_id AND status = "pending"
             ORDER BY id DESC
             LIMIT 1'
        );
        $stmt->execute(['user_id' => (int) $authUser['id']]);
        $req = $stmt->fetch();

        echo json_encode(['ok' => true, 'request' => $req ?: null]);
        exit;
    }

    if (($authUser['role'] ?? '') === 'superadmin') {
        $requestId = (int) ($_GET['request_id'] ?? 0);
        $stmt = $db->prepare(
            'SELECT id, status, created_at, responded_at
             FROM admin_page_requests
             WHERE id = :id AND superadmin_id = :superadmin_id
             LIMIT 1'
        );
        $stmt->execute([
            'id' => $requestId,
            'superadmin_id' => (int) $authUser['id'],
        ]);
        echo json_encode(['ok' => true, 'request' => $stmt->fetch() ?: null]);
        exit;
    }

    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Rôle non autorisé.']);
    exit;
}

if ($action === 'respond_request') {
    if (($authUser['role'] ?? '') !== 'user') {
        http_response_code(403);
        echo json_encode(['ok' => false, 'message' => 'Accès réservé aux utilisateurs.']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'message' => 'Méthode non autorisée.']);
        exit;
    }

    $requestId = (int) ($_POST['request_id'] ?? 0);
    $decision = (string) ($_POST['decision'] ?? 'denied');
    $status = $decision === 'allowed' ? 'allowed' : 'denied';

    $stmt = $db->prepare(
        'UPDATE admin_page_requests
         SET status = :status, responded_at = NOW()
         WHERE id = :id AND user_id = :user_id AND status = "pending"'
    );
    $stmt->execute([
        'status' => $status,
        'id' => $requestId,
        'user_id' => (int) $authUser['id'],
    ]);

    echo json_encode(['ok' => true, 'status' => $status, 'updated' => $stmt->rowCount() > 0]);
    exit;
}

http_response_code(400);
echo json_encode(['ok' => false, 'message' => 'Action inconnue.']);
exit;

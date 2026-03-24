<?php
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/config/config.php';

if (!class_exists('Database')) {
    require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/classes/Database.php';
}

$token = $_GET['token'] ?? '';
if (!$token) {
    http_response_code(400);
    echo 'Lien invalide';
    exit;
}

try {
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare("SELECT title, html FROM social_image_designs WHERE share_token = ? LIMIT 1");
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        http_response_code(404);
        echo 'Image introuvable';
        exit;
    }

    echo $row['html'];
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Erreur serveur';
}

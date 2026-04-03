<?php
declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/../core/bootstrap.php';
Auth::requireAuth();
require_once ROOT_PATH . '/core/helpers/settings.php';

header('Content-Type: application/json; charset=UTF-8');

$payload = json_decode((string)file_get_contents('php://input'), true);
$action = preg_replace('/[^a-z_]/', '', (string)($payload['action'] ?? ''));
$userId = (int)(Auth::user()['id'] ?? 0);

if ($userId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Utilisateur introuvable.']);
    exit;
}

try {
    switch ($action) {
        case 'clear_cache':
            setting_flush($userId);
            echo json_encode(['success' => true, 'message' => 'Cache paramètres vidé.']);
            break;

        case 'reset_settings':
            db()->prepare('DELETE FROM settings WHERE user_id = ?')->execute([$userId]);
            setting_flush($userId);
            echo json_encode(['success' => true, 'message' => 'Paramètres réinitialisés.']);
            break;

        case 'delete_all':
            db()->prepare('DELETE FROM settings WHERE user_id = ?')->execute([$userId]);
            db()->prepare('DELETE FROM settings_history WHERE user_id = ?')->execute([$userId]);
            setting_flush($userId);
            echo json_encode(['success' => true, 'message' => 'Toutes les données de paramètres ont été supprimées.']);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Action invalide.']);
            break;
    }
} catch (Throwable $e) {
    error_log('settings danger error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erreur lors du traitement de l\'action.']);
}

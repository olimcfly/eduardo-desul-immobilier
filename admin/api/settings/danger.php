<?php
// ============================================================
// API — Zone de danger
// ============================================================
require_once $_SERVER['DOCUMENT_ROOT'] . '/../core/bootstrap.php';
Auth::requireAuth();

header('Content-Type: application/json');

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$action = preg_replace('/[^a-z_]/', '', (string)($input['action'] ?? ''));

if (!$action) {
    echo json_encode(['success' => false, 'error' => 'Action manquante.']);
    exit;
}

$user   = Auth::user();
$userId = (int)($user['id'] ?? 0);
$maintenanceFlag = STORAGE_PATH . '/cache/maintenance.flag';

if ($userId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Utilisateur introuvable.']);
    exit;
}

switch ($action) {

    // ── Vider le cache ────────────────────────────────────────
    case 'clear_cache':
        clearSettingCache($userId);
        echo json_encode(['success' => true, 'message' => 'Cache vidé avec succès.']);
        break;

    // ── Activer le mode maintenance ──────────────────────────
    case 'maintenance_on':
        try {
            $dir = dirname($maintenanceFlag);
            if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
                throw new RuntimeException('Impossible de créer le dossier de maintenance.');
            }

            $payload = json_encode([
                'enabled_by' => $userId,
                'enabled_at' => gmdate('c'),
            ], JSON_UNESCAPED_SLASHES);

            if (file_put_contents($maintenanceFlag, $payload === false ? '' : $payload, LOCK_EX) === false) {
                throw new RuntimeException('Impossible d\'écrire le fichier de maintenance.');
            }

            echo json_encode(['success' => true, 'message' => 'Mode maintenance activé.']);
        } catch (Throwable $e) {
            error_log('maintenance_on error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Impossible d\'activer la maintenance.']);
        }
        break;

    // ── Désactiver le mode maintenance ───────────────────────
    case 'maintenance_off':
        try {
            if (is_file($maintenanceFlag) && !unlink($maintenanceFlag)) {
                throw new RuntimeException('Impossible de supprimer le fichier de maintenance.');
            }
            echo json_encode(['success' => true, 'message' => 'Mode maintenance désactivé.']);
        } catch (Throwable $e) {
            error_log('maintenance_off error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Impossible de désactiver la maintenance.']);
        }
        break;

    // ── Réinitialiser les paramètres ─────────────────────────
    case 'reset_settings':
        try {
            $pdo = db();
            $pdo->prepare('DELETE FROM settings WHERE user_id = ?')->execute([$userId]);
            clearSettingCache($userId);
            initUserSettings($pdo, $userId);
            echo json_encode(['success' => true, 'message' => 'Paramètres réinitialisés.']);
        } catch (Throwable $e) {
            error_log('reset_settings error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la réinitialisation.']);
        }
        break;

    // ── Supprimer toutes les données ─────────────────────────
    case 'delete_all':
        // Seuls admin et superadmin peuvent supprimer toutes les données
        if (!Auth::isAdmin()) {
            echo json_encode(['success' => false, 'error' => 'Permission refusée.']);
            exit;
        }
        try {
            $pdo = db();
            // Suppression des données liées à l'utilisateur
            foreach (['settings', 'settings_history', 'contacts', 'biens', 'crm_leads'] as $table) {
                try {
                    $pdo->prepare("DELETE FROM `{$table}` WHERE user_id = ?")->execute([$userId]);
                } catch (Throwable) {
                    // Table optionnelle — ignorer si elle n'existe pas
                }
            }
            clearSettingCache($userId);
            Auth::logout();
            echo json_encode(['success' => true, 'message' => 'Toutes les données ont été supprimées.']);
        } catch (Throwable $e) {
            error_log('delete_all error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la suppression.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Action inconnue.']);
        break;
}

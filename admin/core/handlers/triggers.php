<?php
/**
 * API Handler: triggers
 * /admin/api/router.php?module=triggers&action=...
 */

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = CURRENT_ACTION;

require_once dirname(__DIR__, 3) . '/includes/classes/SequenceTriggerService.php';
$triggerService = new SequenceTriggerService($pdo);

switch ($action) {
    case 'list':
        try {
            $stmt = $pdo->query("SELECT t.*, s.name AS sequence_name FROM crm_triggers t LEFT JOIN crm_sequences s ON s.id = t.sequence_id ORDER BY t.priority DESC, t.id DESC");
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'create':
        try {
            $stmt = $pdo->prepare("INSERT INTO crm_triggers (name, event_key, sequence_id, conditions_json, priority, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                trim((string)($input['name'] ?? 'Nouveau trigger')),
                trim((string)($input['event_key'] ?? 'capture_submitted')),
                (int)($input['sequence_id'] ?? 0),
                json_encode($input['conditions_json'] ?? [], JSON_UNESCAPED_UNICODE),
                (int)($input['priority'] ?? 100),
                (int)($input['is_active'] ?? 1),
            ]);
            echo json_encode(['success' => true, 'id' => (int)$pdo->lastInsertId()]);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'update':
        try {
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'id requis']);
                break;
            }
            $stmt = $pdo->prepare("UPDATE crm_triggers SET name=?, event_key=?, sequence_id=?, conditions_json=?, priority=?, is_active=? WHERE id=?");
            $stmt->execute([
                trim((string)($input['name'] ?? 'Trigger')),
                trim((string)($input['event_key'] ?? 'capture_submitted')),
                (int)($input['sequence_id'] ?? 0),
                json_encode($input['conditions_json'] ?? [], JSON_UNESCAPED_UNICODE),
                (int)($input['priority'] ?? 100),
                (int)($input['is_active'] ?? 1),
                $id,
            ]);
            echo json_encode(['success' => true]);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'delete':
        try {
            $id = (int)($input['id'] ?? 0);
            $pdo->prepare("DELETE FROM crm_triggers WHERE id=?")->execute([$id]);
            echo json_encode(['success' => true]);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'process_capture':
        try {
            $leadId = (int)($input['lead_id'] ?? 0);
            if ($leadId <= 0) {
                echo json_encode(['success' => false, 'message' => 'lead_id requis']);
                break;
            }
            $result = $triggerService->processLeadEvent('capture_submitted', $leadId, [
                'capture_slug' => (string)($input['capture_slug'] ?? ''),
                'capture_page_id' => (int)($input['capture_page_id'] ?? 0),
                'source' => (string)($input['source'] ?? ''),
            ]);
            echo json_encode(['success' => true, 'data' => $result]);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'logs':
        try {
            $limit = max(1, min(200, (int)($input['limit'] ?? $_GET['limit'] ?? 50)));
            $stmt = $pdo->prepare("SELECT l.*, t.name AS trigger_name FROM crm_trigger_logs l LEFT JOIN crm_triggers t ON t.id = l.trigger_id ORDER BY l.id DESC LIMIT ?");
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => "Action '{$action}' non supportee"]);
}

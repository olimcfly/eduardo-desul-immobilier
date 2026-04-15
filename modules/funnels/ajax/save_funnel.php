<?php
// modules/funnels/ajax/save_funnel.php
// Dispatché par /public/admin/api/funnels/ajax.php

require_once ROOT_PATH . '/core/bootstrap.php';
require_once MODULES_PATH . '/funnels/services/FunnelService.php';
require_once MODULES_PATH . '/funnels/services/SequenceCrmService.php';

header('Content-Type: application/json');

// Auth vérification
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

$raw    = file_get_contents('php://input');
$input  = json_decode($raw, true) ?? $_POST;
$action = $input['action'] ?? '';

$db      = \Database::getInstance();
$service = new FunnelService($db);

try {
    switch ($action) {
        case 'create':
            $result = $service->create($input);
            echo json_encode($result);
            break;

        case 'update':
            $id = (int) ($input['id'] ?? 0);
            if (!$id) throw new \Exception('ID manquant');
            $result = $service->update($id, $input);
            echo json_encode($result);
            break;

        case 'publish':
            $id = (int) ($input['id'] ?? 0);
            if (!$id) throw new \Exception('ID manquant');
            $ok = $service->publish($id);
            echo json_encode(['success' => $ok]);
            break;

        case 'unpublish':
            $id = (int) ($input['id'] ?? 0);
            $ok = $service->unpublish($id);
            echo json_encode(['success' => $ok]);
            break;

        case 'duplicate':
            $id  = (int) ($input['id'] ?? 0);
            $newId = $service->duplicate($id);
            echo json_encode(['success' => (bool) $newId, 'id' => $newId]);
            break;

        case 'delete':
            $id = (int) ($input['id'] ?? 0);
            $ok = $service->delete($id);
            echo json_encode(['success' => $ok]);
            break;

        case 'get_templates':
            $canal = $input['canal'] ?? '';
            $templates = $service->getTemplatesForCanal($canal);
            echo json_encode(['success' => true, 'templates' => $templates]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Action inconnue']);
    }
} catch (\Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

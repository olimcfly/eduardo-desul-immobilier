<?php
/**
 * API Handler: estimateur
 * Called via: /admin/api/router.php?module=estimateur&action=...
 */

require_once dirname(__DIR__, 3) . '/app/Models/EstimatorConfigRepository.php';
require_once dirname(__DIR__, 3) . '/app/Models/EstimationRequestRepository.php';
require_once dirname(__DIR__, 3) . '/app/Controllers/Admin/EstimatorAdminController.php';

use App\Controllers\Admin\EstimatorAdminController;
use App\Models\EstimationRequestRepository;
use App\Models\EstimatorConfigRepository;

$controller = new EstimatorAdminController(new EstimatorConfigRepository($pdo), new EstimationRequestRepository($pdo));
$action = CURRENT_ACTION;

switch ($action) {
    case 'dashboard':
        echo json_encode(['success' => true, 'data' => $controller->dashboard((string) ($_GET['city_slug'] ?? ''))]);
        break;
    case 'requests':
        $data = $controller->dashboard((string) ($_GET['city_slug'] ?? ''));
        echo json_encode(['success' => true, 'data' => $data['requests'] ?? []]);
        break;
    case 'request_detail':
        $id = (int) ($_GET['id'] ?? 0);
        echo json_encode(['success' => true, 'data' => $controller->requestDetail($id)]);
        break;
    default:
        echo json_encode(['success' => false, 'message' => "Action '{$action}' non supportée"]);
}

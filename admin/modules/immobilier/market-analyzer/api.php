<?php
/**
 * API AJAX — Market Analyzer
 * /admin/modules/immobilier/market-analyzer/api.php
 */

require_once __DIR__ . '/../../../includes/init.php';
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/MarketAnalyzer.php';
require_once __DIR__ . '/services/KeywordOpportunityScoringService.php';
require_once __DIR__ . '/services/ClusterPlannerService.php';
require_once __DIR__ . '/services/ArticleBridgeService.php';
require_once __DIR__ . '/services/SiloLinkingService.php';
require_once __DIR__ . '/services/ClusterJobOrchestratorService.php';

$userId = (int)($_SESSION['auth_admin_id'] ?? 0);
$analyzer = new MarketAnalyzer($pdo, $userId);
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? [];

$scoringService = new KeywordOpportunityScoringService();
$plannerService = new ClusterPlannerService($pdo, $userId, $scoringService);
$articleBridgeService = new ArticleBridgeService($pdo, $userId);
$linkingService = new SiloLinkingService($pdo, $userId);
$orchestratorService = new ClusterJobOrchestratorService($pdo, $userId, $articleBridgeService);

function requireMutableCsrf(array $input): void {
    if (empty($_SESSION['auth_csrf_token'])) {
        return;
    }

    $token = $input['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['auth_csrf_token'], $token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
        exit;
    }
}

try {
    switch ($action) {
        case 'analyze':
            $city = trim($input['city'] ?? $_GET['city'] ?? '');
            if ($city === '') {
                echo json_encode(['success' => false, 'error' => 'Ville requise']);
                exit;
            }
            $city = htmlspecialchars_decode(strip_tags($city));
            $city = preg_replace('/[^a-zA-ZÀ-ÿ\s\-\']/u', '', $city);
            $city = trim($city);
            if (mb_strlen($city) < 2 || mb_strlen($city) > 100) {
                echo json_encode(['success' => false, 'error' => 'Nom de ville invalide']);
                exit;
            }
            echo json_encode($analyzer->runAnalysis($city), JSON_UNESCAPED_UNICODE);
            break;

        case 'add-city':
            requireMutableCsrf($input);
            $city = trim($input['city'] ?? '');
            $dept = trim($input['department'] ?? '');
            if ($city === '') {
                echo json_encode(['success' => false, 'error' => 'Ville requise']);
                exit;
            }
            $ok = $analyzer->addCity($city, $dept ?: null);
            echo json_encode(['success' => $ok, 'cities' => $analyzer->getUserCities()]);
            break;

        case 'remove-city':
            requireMutableCsrf($input);
            $city = trim($input['city'] ?? '');
            if ($city === '') {
                echo json_encode(['success' => false, 'error' => 'Ville requise']);
                exit;
            }
            $analyzer->removeCity($city);
            echo json_encode(['success' => true, 'cities' => $analyzer->getUserCities()]);
            break;

        case 'history':
            echo json_encode(['success' => true, 'analyses' => $analyzer->getAllAnalyses()]);
            break;

        case 'cities':
            echo json_encode(['success' => true, 'cities' => $analyzer->getUserCities()]);
            break;

        case 'cluster.plan':
            requireMutableCsrf($input);
            $city = trim((string)($input['city'] ?? ''));
            if ($city === '') {
                echo json_encode(['success' => false, 'error' => 'Ville requise']);
                exit;
            }
            $result = $plannerService->planForCity($city, $input['options'] ?? []);
            echo json_encode(['success' => true] + $result, JSON_UNESCAPED_UNICODE);
            break;

        case 'cluster.get':
            $planId = (int)($input['plan_id'] ?? $_GET['plan_id'] ?? 0);
            if ($planId <= 0) {
                echo json_encode(['success' => false, 'error' => 'plan_id requis']);
                exit;
            }
            echo json_encode(['success' => true] + $plannerService->getPlan($planId), JSON_UNESCAPED_UNICODE);
            break;

        case 'cluster.generate_articles':
            requireMutableCsrf($input);
            $planId = (int)($input['plan_id'] ?? 0);
            if ($planId <= 0) {
                echo json_encode(['success' => false, 'error' => 'plan_id requis']);
                exit;
            }
            echo json_encode(['success' => true] + $orchestratorService->enqueuePlanGeneration($planId));
            break;

        case 'cluster.generate_item':
            requireMutableCsrf($input);
            $itemId = (int)($input['item_id'] ?? 0);
            if ($itemId <= 0) {
                echo json_encode(['success' => false, 'error' => 'item_id requis']);
                exit;
            }
            echo json_encode(['success' => true] + $orchestratorService->enqueueItemGeneration($itemId));
            break;

        case 'cluster.retry_failed':
            requireMutableCsrf($input);
            $planId = (int)($input['plan_id'] ?? 0);
            $itemId = (int)($input['item_id'] ?? 0);
            echo json_encode(['success' => true] + $orchestratorService->retryFailed($planId ?: null, $itemId ?: null));
            break;

        case 'cluster.link_plan':
            requireMutableCsrf($input);
            $planId = (int)($input['plan_id'] ?? 0);
            if ($planId <= 0) {
                echo json_encode(['success' => false, 'error' => 'plan_id requis']);
                exit;
            }
            echo json_encode(['success' => true] + $linkingService->buildLinkPlan($planId), JSON_UNESCAPED_UNICODE);
            break;

        case 'cluster.link_plan.get':
            $planId = (int)($input['plan_id'] ?? $_GET['plan_id'] ?? 0);
            if ($planId <= 0) {
                echo json_encode(['success' => false, 'error' => 'plan_id requis']);
                exit;
            }
            echo json_encode(['success' => true] + $linkingService->getLinkPlan($planId), JSON_UNESCAPED_UNICODE);
            break;

        case 'jobs.process_queue':
            requireMutableCsrf($input);
            $limit = (int)($input['limit'] ?? 5);
            echo json_encode(['success' => true] + $orchestratorService->processQueue($limit));
            break;

        case 'jobs.status':
            $planId = (int)($input['plan_id'] ?? $_GET['plan_id'] ?? 0);
            echo json_encode(['success' => true] + $orchestratorService->getStatus($planId ?: null));
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Action inconnue: ' . $action]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

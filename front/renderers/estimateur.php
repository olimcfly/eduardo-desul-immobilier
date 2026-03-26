<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Models/EstimatorConfigRepository.php';
require_once dirname(__DIR__, 2) . '/app/Models/EstimationRequestRepository.php';
require_once dirname(__DIR__, 2) . '/app/Services/EstimatorConfigService.php';
require_once dirname(__DIR__, 2) . '/app/Services/EstimationEngine.php';
require_once dirname(__DIR__, 2) . '/app/Services/LeadQualificationService.php';
require_once dirname(__DIR__, 2) . '/app/Services/EstimatorService.php';
require_once dirname(__DIR__, 2) . '/app/Controllers/EstimatorController.php';

use App\Controllers\EstimatorController;
use App\Models\EstimationRequestRepository;
use App\Models\EstimatorConfigRepository;
use App\Services\EstimationEngine;
use App\Services\EstimatorConfigService;
use App\Services\EstimatorService;
use App\Services\LeadQualificationService;

$db = getDB();

// Auto-migration : crée les tables si elles n'existent pas encore
$migrationFile = dirname(__DIR__, 2) . '/database/migrations/20260325_estimateur_module.sql';
try {
    $tableCheck = $db->query("SHOW TABLES LIKE 'estimator_configs'")->fetchColumn();
    if (!$tableCheck && file_exists($migrationFile)) {
        $sql = file_get_contents($migrationFile);
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
            if ($statement !== '') {
                $db->exec($statement);
            }
        }
    }
} catch (\PDOException $e) {
    // Migration failed silently — page will render without config
}
$citySlug = $_GET['city_slug'] ?? '';

$configRepository = new EstimatorConfigRepository($db);
$requestRepository = new EstimationRequestRepository($db);
$configService = new EstimatorConfigService($configRepository);
$service = new EstimatorService($configRepository, $requestRepository, new EstimationEngine(), new LeadQualificationService());
$controller = new EstimatorController($configService, $service);

$context = $controller->show($citySlug);
$resultPayload = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission = $controller->submit($_POST);
    if (!($submission['success'] ?? false)) {
        $errors[] = $submission['error'] ?? 'Erreur de validation.';
    } else {
        $resultPayload = $submission['data'];
    }
}

$pageTitle = $context['config']['seo_title'] ?? 'Estimation immobilière';
$pageDescription = $context['config']['seo_description'] ?? 'Estimez votre bien en ligne';

include __DIR__ . '/../templates/estimateur/index.php';

<?php
/**
 * API AJAX — Market Analyzer
 * /admin/modules/immobilier/market-analyzer/api.php
 *
 * Actions: analyze, add-city, remove-city, history
 */

require_once __DIR__ . '/../../../includes/init.php';

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/MarketAnalyzer.php';

$analyzer = new MarketAnalyzer($pdo, (int) $_SESSION['admin_id']);
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($action) {

    // ── Lancer une analyse ────────────────────────
    case 'analyze':
        $city = trim($input['city'] ?? $_GET['city'] ?? '');
        if (empty($city)) {
            echo json_encode(['success' => false, 'error' => 'Ville requise']);
            exit;
        }
        // Sanitize
        $city = htmlspecialchars_decode(strip_tags($city));
        $city = preg_replace('/[^a-zA-ZÀ-ÿ\s\-\']/u', '', $city);
        $city = trim($city);

        if (mb_strlen($city) < 2 || mb_strlen($city) > 100) {
            echo json_encode(['success' => false, 'error' => 'Nom de ville invalide']);
            exit;
        }

        $result = $analyzer->runAnalysis($city);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;

    // ── Ajouter une ville ─────────────────────────
    case 'add-city':
        $city = trim($input['city'] ?? '');
        $dept = trim($input['department'] ?? '');
        if (empty($city)) {
            echo json_encode(['success' => false, 'error' => 'Ville requise']);
            exit;
        }
        $ok = $analyzer->addCity($city, $dept ?: null);
        echo json_encode(['success' => $ok, 'cities' => $analyzer->getUserCities()]);
        break;

    // ── Supprimer une ville ───────────────────────
    case 'remove-city':
        $city = trim($input['city'] ?? '');
        if (empty($city)) {
            echo json_encode(['success' => false, 'error' => 'Ville requise']);
            exit;
        }
        $analyzer->removeCity($city);
        echo json_encode(['success' => true, 'cities' => $analyzer->getUserCities()]);
        break;

    // ── Historique des analyses ────────────────────
    case 'history':
        echo json_encode(['success' => true, 'analyses' => $analyzer->getAllAnalyses()]);
        break;

    // ── Liste villes ──────────────────────────────
    case 'cities':
        echo json_encode(['success' => true, 'cities' => $analyzer->getUserCities()]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Action inconnue: ' . $action]);
}

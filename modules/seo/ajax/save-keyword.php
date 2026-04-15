<?php
/** @deprecated SEO legacy freeze: no new feature here. Use /modules/seo/mots-cles/api.php */
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../includes/KeywordTracker.php';
require_once __DIR__ . '/../_legacy_guard.php';
seoLegacyGuard('modules/seo/ajax/save-keyword.php', '/modules/seo/mots-cles/api.php');

header('Content-Type: application/json');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

try {
    $tracker = new KeywordTracker(db(), (int)$_SESSION['user_id']);
    $id = $tracker->saveKeyword($_POST);
    echo json_encode(['success' => true, 'id' => $id]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

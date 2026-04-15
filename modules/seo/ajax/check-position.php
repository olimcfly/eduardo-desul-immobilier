<?php
/** @deprecated SEO legacy freeze: no new feature here. Use /modules/seo/mots-cles/api.php */
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../includes/KeywordTracker.php';
require_once __DIR__ . '/../_legacy_guard.php';
seoLegacyGuard('modules/seo/ajax/check-position.php', '/modules/seo/mots-cles/api.php');

header('Content-Type: application/json');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

try {
    $keywordId = (int)($_POST['keyword_id'] ?? 0);
    if ($keywordId <= 0) {
        throw new InvalidArgumentException('keyword_id invalide');
    }

    $tracker = new KeywordTracker(db(), (int)$_SESSION['user_id']);
    $result = $tracker->mockCheckPosition($keywordId);

    echo json_encode(['success' => true, 'data' => $result]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

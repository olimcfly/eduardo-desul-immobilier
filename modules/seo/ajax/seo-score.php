<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/core/bootstrap.php';

Auth::requireAuth('/admin/login');
header('Content-Type: application/json; charset=utf-8');

try {
    verifyCsrf();
    $title = trim((string)($_POST['seo_title'] ?? ''));
    $meta = trim((string)($_POST['meta_description'] ?? ''));
    $content = trim((string)($_POST['content'] ?? ''));

    $score = 0;
    $score += strlen($title) >= 30 && strlen($title) <= 60 ? 35 : 10;
    $score += strlen($meta) >= 80 && strlen($meta) <= 160 ? 30 : 10;
    $score += strlen(strip_tags($content)) >= 400 ? 35 : 10;

    echo json_encode(['success' => true, 'score' => min(100, $score)]);
} catch (Throwable $e) {
    error_log('[' . date('Y-m-d H:i:s') . '] seo-score: ' . $e->getMessage() . PHP_EOL, 3, dirname(__DIR__, 3) . '/logs/seo.log');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

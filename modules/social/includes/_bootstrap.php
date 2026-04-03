<?php
require_once __DIR__ . '/../../../core/bootstrap.php';
Auth::requireAuth('/admin/login');

require_once __DIR__ . '/FacebookClient.php';
require_once __DIR__ . '/InstagramClient.php';
require_once __DIR__ . '/LinkedinClient.php';
require_once __DIR__ . '/ContentGenerator.php';
require_once __DIR__ . '/SocialService.php';

if (!function_exists('socialUserId')) {
    function socialUserId(): int
    {
        return (int) ($_SESSION['user_id'] ?? 0);
    }
}

if (!function_exists('socialJsonResponse')) {
    function socialJsonResponse(array $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

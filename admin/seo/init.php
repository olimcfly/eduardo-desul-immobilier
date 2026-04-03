<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/services/BlogService.php';
require_once __DIR__ . '/services/SeoService.php';

$dbConfig = require __DIR__ . '/../../config/database.php';

$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    $dbConfig['host'] ?? 'localhost',
    $dbConfig['dbname'] ?? '',
    $dbConfig['charset'] ?? 'utf8mb4'
);

$pdo = new PDO(
    $dsn,
    (string)($dbConfig['user'] ?? ''),
    (string)($dbConfig['pass'] ?? ''),
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);

$blogService = new BlogService($pdo);
$seoService = new SeoService($pdo);

if (empty($_SESSION['seo_csrf_token'])) {
    $_SESSION['seo_csrf_token'] = bin2hex(random_bytes(32));
}

function seo_h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function seo_verify_csrf(string $token): bool
{
    return hash_equals($_SESSION['seo_csrf_token'] ?? '', $token);
}

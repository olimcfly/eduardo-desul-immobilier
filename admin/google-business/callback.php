<?php

declare(strict_types=1);

require_once __DIR__ . '/../session-helper.php';
startAdminSession();

if (!isAdminLoggedIn()) {
    header('Location: /admin/auth/login.php', true, 302);
    exit;
}

if (!function_exists('db')) {
    require_once __DIR__ . '/../../core/bootstrap.php';
}

require_once __DIR__ . '/../../modules/gmb/ajax/oauth-callback.php';

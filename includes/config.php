<?php

declare(strict_types=1);

if (!defined('ADMIN_EMAIL')) {
    define('ADMIN_EMAIL', 'admin@example.com');
}

if (!defined('ADMIN_PASSWORD_HASH')) {
    define('ADMIN_PASSWORD_HASH', '$2y$10$replace.with.generated.hash');
}

if (!defined('ADMIN_PASSWORD_RESET_REQUIRED')) {
    define('ADMIN_PASSWORD_RESET_REQUIRED', true);
}

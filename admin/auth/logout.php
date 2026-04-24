<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_functions.php';

logout();
header('Location: /admin/login.php?logged_out=1');
exit;

<?php

declare(strict_types=1);

require_once __DIR__ . '/Service/GmbService.php';
require_once __DIR__ . '/controller.php';

$user = Auth::user();
$userId = (int)($user['id'] ?? 0);

gmbAssertAuthorizedUser($userId);

$gmbService = new GmbService(db());
$stats = $gmbService->getHubStats($userId);
$action = gmbResolveAction($_GET['action'] ?? null);

require __DIR__ . '/view.php';

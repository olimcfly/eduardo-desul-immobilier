<?php

require_once __DIR__ . '/../../../../app/Models/Prompt.php';
require_once __DIR__ . '/../../../../app/Services/PromptBuilderService.php';
require_once __DIR__ . '/../../../../app/Controllers/PromptController.php';

if (!isset($pdo) || !$pdo instanceof PDO) {
    $pdo = Database::getInstance();
}

$controller = new PromptController($pdo);
$controller->handle();

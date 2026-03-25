<?php

require_once ROOT_PATH . '/app/Models/ClientInstance.php';
require_once ROOT_PATH . '/app/Services/SecretVaultService.php';
require_once ROOT_PATH . '/app/Services/PlaceholderReplacementService.php';
require_once ROOT_PATH . '/app/Services/ZipExportService.php';
require_once ROOT_PATH . '/app/Services/InstanceGeneratorService.php';
require_once ROOT_PATH . '/app/Services/InstanceIntakeParserService.php';
require_once ROOT_PATH . '/app/Controllers/Admin/ClientInstanceController.php';

$controller = new ClientInstanceController($pdo);
$controller->handle();

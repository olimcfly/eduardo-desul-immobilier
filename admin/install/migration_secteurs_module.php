<?php
/**
 * Migration MVP module Secteurs.
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/classes/SecteurSeoService.php';
require_once dirname(__DIR__, 2) . '/includes/classes/SecteurPublishService.php';
require_once dirname(__DIR__, 2) . '/includes/classes/SecteurService.php';
if (!class_exists('Database')) require_once ROOT_PATH . '/includes/classes/Database.php';

$pdo = Database::getInstance();
$service = new SecteurService($pdo);
$service->ensureSchema();

echo "✅ Migration Secteurs MVP exécutée\n";

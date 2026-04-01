<?php
/**
 * Migration MVP module Secteurs.
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/classes/SecteurSeoService.php';
require_once dirname(__DIR__, 2) . '/includes/classes/SecteurPublishService.php';
require_once dirname(__DIR__, 2) . '/includes/classes/SecteurService.php';

$pdo = Database::getInstance();
$service = new SecteurService($pdo);
$service->ensureSchema();

echo "✅ Migration Secteurs MVP exécutée\n";

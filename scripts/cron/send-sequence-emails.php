<?php
/**
 * Cron: envoi des emails de séquence.
 * Exemple crontab (toutes les 5 minutes):
 * php /workspace/eduardo-desul-immobilier/scripts/cron/send-sequence-emails.php
 */

$rootPath = dirname(__DIR__, 2);
require_once $rootPath . '/config/config.php';
require_once $rootPath . '/includes/classes/Database.php';
require_once $rootPath . '/includes/classes/EmailService.php';
require_once $rootPath . '/includes/classes/SequenceEngineService.php';

$dryRun = in_array('--dry-run', $argv ?? [], true);
$limit = 100;
foreach (($argv ?? []) as $arg) {
    if (strpos($arg, '--limit=') === 0) {
        $limit = max(1, (int)substr($arg, 8));
    }
}

try {
    $pdo = Database::getInstance();
    $engine = new SequenceEngineService($pdo);
    $result = $engine->run($limit, $dryRun);

    echo json_encode([
        'success' => true,
        'dry_run' => $dryRun,
        'limit' => $limit,
        'result' => $result,
        'executed_at' => date('c'),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit(0);
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'executed_at' => date('c'),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit(1);
}

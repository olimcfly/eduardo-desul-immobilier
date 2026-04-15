#!/usr/bin/env php
<?php
/**
 * script/process_sequences.php
 * Cron : traite les emails de séquences CRM dus
 *
 * Planification recommandée :
 *   */1 * * * * php /home/cool1019/site/script/process_sequences.php >> /var/log/sequences_crm.log 2>&1
 *
 * Usage manuel :
 *   php script/process_sequences.php [--dry-run]
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/core/bootstrap.php';
require_once MODULES_PATH . '/funnels/services/SequenceCrmService.php';

$isDryRun = in_array('--dry-run', $argv ?? []);
$startTime = microtime(true);

$db      = \Database::getInstance();
$service = new SequenceCrmService($db);

echo '[' . date('Y-m-d H:i:s') . '] Démarrage process_sequences.php' . ($isDryRun ? ' [DRY-RUN]' : '') . PHP_EOL;

if ($isDryRun) {
    // Compter les emails dus sans envoyer
    $stmt = $db->prepare('
        SELECT COUNT(*) FROM crm_sequence_enrollments
        WHERE status = "active" AND next_send_at <= NOW()
    ');
    $stmt->execute();
    $count = $stmt->fetchColumn();
    echo "[DRY-RUN] {$count} email(s) seraient envoyés." . PHP_EOL;
} else {
    $sent = $service->processDue();
    echo "Emails envoyés : $sent" . PHP_EOL;
}

$elapsed = round((microtime(true) - $startTime) * 1000);
echo '[' . date('Y-m-d H:i:s') . "] Terminé en {$elapsed}ms" . PHP_EOL;

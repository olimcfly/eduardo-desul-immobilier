<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../modules/optimiser/services/MonthlyReportService.php';

$pdo = db();
$service = new MonthlyReportService($pdo);

$users = $pdo->query('SELECT id, email FROM users ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC) ?: [];
$sentCount = 0;
$skippedCount = 0;

foreach ($users as $user) {
    $userId = (int) ($user['id'] ?? 0);
    if ($userId <= 0) {
        continue;
    }

    $email = trim((string) setting('advisor_email', (string) ($user['email'] ?? APP_EMAIL), $userId));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $skippedCount++;
        continue;
    }

    $result = $service->sendCurrentMonthIfDue($userId, $email);
    if (is_array($result) && !empty($result['email_sent'])) {
        $sentCount++;
    } else {
        $skippedCount++;
    }
}

$line = sprintf("[%s] Monthly report cron done. sent=%d skipped=%d\n", date('Y-m-d H:i:s'), $sentCount, $skippedCount);
file_put_contents(__DIR__ . '/../logs/monthly-report-cron.log', $line, FILE_APPEND);
echo $line;

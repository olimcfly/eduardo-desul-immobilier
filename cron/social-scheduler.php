<?php
require_once __DIR__ . '/../modules/social/includes/_bootstrap.php';
$service = new SocialService();
$count = $service->processScheduled();
$line = sprintf("[%s] Published: %d\n", date('Y-m-d H:i:s'), $count);
file_put_contents(__DIR__ . '/../logs/social-cron.log', $line, FILE_APPEND);
echo $line;

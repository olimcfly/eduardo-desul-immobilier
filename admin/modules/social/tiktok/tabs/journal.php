<?php
/**
 * tabs — Journal éditorial
 * Canal : TikTok
 */
if (!defined('ADMIN_ROUTER')) { http_response_code(403); exit; }

$journal_channel      = 'tiktok';
$journal_module_label = 'TikTok';

require_once __DIR__ . '/../../../ai/journal/journal-widget.php';

<?php
/**
 * tabs — Journal éditorial
 * Canal : LinkedIn
 */
if (!defined('ADMIN_ROUTER')) { http_response_code(403); exit; }

$journal_channel      = 'linkedin';
$journal_module_label = 'LinkedIn';

require_once __DIR__ . '/../../../ai/journal/journal-widget.php';

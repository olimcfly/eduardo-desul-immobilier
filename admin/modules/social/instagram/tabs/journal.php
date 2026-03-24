<?php
/**
 * tabs — Journal éditorial
 * Canal : Instagram
 */
if (!defined('ADMIN_ROUTER')) { http_response_code(403); exit; }

$journal_channel      = 'instagram';
$journal_module_label = 'Instagram';

require_once __DIR__ . '/../../../ai/journal/journal-widget.php';

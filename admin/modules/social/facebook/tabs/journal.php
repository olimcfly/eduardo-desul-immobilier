<?php
/**
 * tabs — Journal éditorial
 * Canal : Facebook
 */
if (!defined('ADMIN_ROUTER')) { http_response_code(403); exit; }

$journal_channel      = 'facebook';
$journal_module_label = 'Facebook';

require_once __DIR__ . '/../../../ai/journal/journal-widget.php';

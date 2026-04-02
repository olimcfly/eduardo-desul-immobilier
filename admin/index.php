<?php
// admin/index.php
session_start();
if (!isset($_SESSION['agent_id'])) {
    header('Location: /admin/login.php');
    exit;
}

$page_title = 'Mon espace – CRM Immo';

ob_start();
include __DIR__ . '/views/dashboard/index.php';
$content = ob_get_clean();

include __DIR__ . '/views/layout.php';

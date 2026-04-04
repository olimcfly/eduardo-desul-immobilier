<?php
require_once __DIR__ . '/../../config/database.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

switch ($action) {
    case 'new':    include __DIR__ . '/views/form.php'; break;
    case 'edit':   include __DIR__ . '/views/form.php'; break;
    case 'delete': include __DIR__ . '/controllers/delete.php'; break;
    case 'view':   include __DIR__ . '/views/show.php'; break;
    default:       include __DIR__ . '/views/list.php'; break;
}

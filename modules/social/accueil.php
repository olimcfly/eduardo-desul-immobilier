<?php
$action = preg_replace('/[^a-z]/', '', (string) ($_GET['action'] ?? 'index'));
$allowed = ['index', 'facebook', 'instagram', 'linkedin', 'calendrier'];
if (!in_array($action, $allowed, true)) {
    $action = 'index';
}
require __DIR__ . '/' . $action . '.php';

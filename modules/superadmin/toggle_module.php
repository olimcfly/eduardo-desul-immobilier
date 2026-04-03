<?php
require_once __DIR__ . '/../../core/services/ModuleService.php';

header('Content-Type: application/json; charset=utf-8');

$user = Auth::user();
if (!$user || ($user['role'] ?? '') !== 'superadmin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Accès refusé.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

$module = preg_replace('/[^a-z0-9_-]/', '', mb_strtolower((string) ($_POST['module_name'] ?? '')));
if ($module === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Module invalide.']);
    exit;
}

$enabledForUsers = (int) ($_POST['enabled_for_users'] ?? 0) === 1;
$enabledForAdmins = (int) ($_POST['enabled_for_admins'] ?? 0) === 1;

$ok = ModuleService::setModuleState($module, $enabledForUsers, $enabledForAdmins);
echo json_encode([
    'ok' => $ok,
    'module_name' => $module,
    'enabled_for_users' => $enabledForUsers,
    'enabled_for_admins' => $enabledForAdmins,
]);
exit;

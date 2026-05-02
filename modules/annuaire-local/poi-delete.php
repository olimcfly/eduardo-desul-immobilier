<?php
declare(strict_types=1);

if (!function_exists('csrfToken')) {
    header('Location: /admin?module=annuaire-local&error=' . rawurlencode('Session indisponible'));
    exit;
}

$token = (string) ($_POST['csrf_token'] ?? '');
if (!hash_equals(csrfToken(), $token)) {
    header('Location: /admin?module=annuaire-local&error=' . rawurlencode('Jeton CSRF invalide'));
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: /admin?module=annuaire-local&error=' . rawurlencode('POI invalide'));
    exit;
}

try {
    $pdo = db();
    $pdo->prepare('DELETE FROM guide_pois WHERE id = ? LIMIT 1')->execute([$id]);
} catch (Throwable $e) {
    error_log('[annuaire-local poi-delete] ' . $e->getMessage());
    header('Location: /admin?module=annuaire-local&error=' . rawurlencode('Suppression impossible.'));
    exit;
}

header('Location: /admin?module=annuaire-local&deleted=1');
exit;

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

$slug = trim((string) ($_POST['ville_slug'] ?? ''));
$slug = preg_replace('/[^a-z0-9-]/', '', $slug) ?? '';
$description = trim((string) ($_POST['description'] ?? ''));
$imageUrl    = trim((string) ($_POST['image_url'] ?? ''));
$codePostal  = trim((string) ($_POST['code_postal'] ?? ''));
$codePostal  = preg_replace('/\D/', '', $codePostal);
$codePostal  = $codePostal !== '' ? substr($codePostal, 0, 5) : null;

if ($slug === '') {
    header('Location: /admin?module=annuaire-local&action=edit-ville&error=' . rawurlencode('Slug manquant.'));
    exit;
}

try {
    $pdo = db();
    $st  = $pdo->prepare('UPDATE villes SET description = ?, image_url = ?, code_postal = COALESCE(?, code_postal), updated_at = CURRENT_TIMESTAMP WHERE slug = ? AND actif = 1');
    $st->execute([
        $description !== '' ? $description : null,
        $imageUrl !== '' ? $imageUrl : null,
        $codePostal,
        $slug,
    ]);
    if ($st->rowCount() === 0) {
        header('Location: /admin?module=annuaire-local&action=edit-ville&slug=' . rawurlencode($slug) . '&error=' . rawurlencode('Aucune ligne mise à jour.'));
        exit;
    }
} catch (Throwable $e) {
    error_log('[ville-presentation-save] ' . $e->getMessage());
    header('Location: /admin?module=annuaire-local&action=edit-ville&slug=' . rawurlencode($slug) . '&error=' . rawurlencode('Erreur enregistrement.'));
    exit;
}

header('Location: /admin?module=annuaire-local&action=edit-ville&slug=' . rawurlencode($slug) . '&ville_saved=1');
exit;

<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../services/CityPageService.php';

Auth::requireAuth('/admin/login');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Méthode non autorisée');
}

verifyCsrf();

$userId = (int)(Auth::user()['id'] ?? 0);
$service = new CityPageService(db());
$mode = (string)($_POST['mode'] ?? 'save');

try {
    if ($mode === 'toggle-publication') {
        $service->togglePublication((int)($_POST['id'] ?? 0), $userId);
        flash('success', 'Statut de publication mis à jour.');
        redirect('/admin?module=seo&action=villes');
    }

    $id = (int)($_POST['id'] ?? 0);
    $savedId = $service->save($userId, $_POST, $id > 0 ? $id : null);

    flash('success', 'Fiche ville enregistrée avec succès.');
    redirect('/admin?module=seo&action=ville-edit&id=' . $savedId);
} catch (Throwable $e) {
    flash('error', 'Impossible d\'enregistrer la fiche : ' . $e->getMessage());
    $backId = (int)($_POST['id'] ?? 0);
    $backUrl = '/admin?module=seo&action=ville-edit';
    if ($backId > 0) {
        $backUrl .= '&id=' . $backId;
    }
    redirect($backUrl);
}

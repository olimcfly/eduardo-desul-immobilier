<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/SeoTechnicalPerformanceService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('error', 'Méthode invalide pour lancer un audit.');
    redirect('/admin?module=seo&action=performance');
}

verifyCsrf();

$userId = (int)(Auth::user()['id'] ?? 0);
$service = new SeoTechnicalPerformanceService(db(), $userId);

try {
    $results = $service->runBatchAudit();
    flash('success', count($results) . ' page(s) auditées avec succès.');
} catch (Throwable $e) {
    flash('error', 'Audit interrompu : ' . $e->getMessage());
}

redirect('/admin?module=seo&action=performance');

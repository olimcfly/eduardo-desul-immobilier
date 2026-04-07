<?php
$pageTitle = 'Estimation DVF';
$pageDescription = 'Import DVF, demandes d’estimation, statistiques et carte.';

$importFeedback = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['dvf_csv'])) {
    if (!empty($_POST['csrf_token']) && hash_equals(csrfToken(), (string) $_POST['csrf_token'])) {
        $importFeedback = DvfImportService::importCsv($_FILES['dvf_csv']);
    } else {
        $importFeedback = ['ok' => false, 'message' => 'Token CSRF invalide.'];
    }
}

$filters = [
    'city' => trim((string) ($_GET['city'] ?? '')),
    'property_type' => trim((string) ($_GET['property_type'] ?? '')),
    'status' => trim((string) ($_GET['status'] ?? '')),
];

$requests = DvfEstimatorService::recentRequests($filters);
$importStats = DvfEstimatorService::importStats();

function renderContent() {
    global $importFeedback, $requests, $importStats, $filters;
    ?>
    <div class="page-header">
        <h1><i class="fas fa-chart-area page-icon"></i> Estimation <span class="page-title-accent">DVF</span></h1>
        <p>Importez DVF, suivez les demandes et pilotez les estimations.</p>
    </div>

    <?php if ($importFeedback): ?>
        <div class="card" style="padding:1rem;border-left:4px solid <?= $importFeedback['ok'] ? '#10b981' : '#ef4444' ?>;margin-bottom:1rem;">
            <?= e((string) $importFeedback['message']) ?>
        </div>
    <?php endif; ?>

    <div class="cards-container" style="grid-template-columns:repeat(3,minmax(0,1fr));margin-bottom:1.25rem;">
        <div class="card"><h3>Données DVF</h3><p><strong><?= number_format((int) $importStats['total_rows'], 0, ',', ' ') ?></strong> lignes exploitées</p></div>
        <div class="card"><h3>Demandes</h3><p><strong><?= number_format(count($requests), 0, ',', ' ') ?></strong> demandes filtrées</p></div>
        <div class="card"><h3>Imports récents</h3><p><strong><?= number_format(count($importStats['runs']), 0, ',', ' ') ?></strong> runs</p></div>
    </div>

    <div class="card" style="padding:1rem;margin-bottom:1rem;">
        <h3>Import DVF (CSV)</h3>
        <form method="POST" enctype="multipart/form-data" style="display:flex;gap:.5rem;align-items:center;">
            <?= csrfField() ?>
            <input type="file" name="dvf_csv" accept=".csv" required>
            <button type="submit" class="btn btn--primary">Lancer import</button>
        </form>
    </div>

    <div class="card" style="padding:1rem;margin-bottom:1rem;">
        <h3>Filtres demandes</h3>
        <form method="GET" action="/admin/index.php" style="display:grid;gap:.75rem;grid-template-columns:repeat(5,minmax(0,1fr));">
            <input type="hidden" name="module" value="estimation_dvf">
            <input type="text" name="city" class="form-control" placeholder="Ville" value="<?= e($filters['city']) ?>">
            <select name="property_type" class="form-control">
                <option value="">Type</option>
                <option value="appartement" <?= $filters['property_type']==='appartement'?'selected':'' ?>>Appartement</option>
                <option value="maison" <?= $filters['property_type']==='maison'?'selected':'' ?>>Maison</option>
                <option value="local" <?= $filters['property_type']==='local'?'selected':'' ?>>Local</option>
                <option value="terrain" <?= $filters['property_type']==='terrain'?'selected':'' ?>>Terrain</option>
            </select>
            <select name="status" class="form-control">
                <option value="">Statut</option>
                <option value="new" <?= $filters['status']==='new'?'selected':'' ?>>Nouveau</option>
                <option value="contacted" <?= $filters['status']==='contacted'?'selected':'' ?>>Contacté</option>
                <option value="qualified" <?= $filters['status']==='qualified'?'selected':'' ?>>Qualifié</option>
            </select>
            <button class="btn btn--primary" type="submit">Filtrer</button>
        </form>
    </div>

    <div class="card" style="padding:1rem;margin-bottom:1rem;overflow:auto;">
        <h3>Demandes d’estimation</h3>
        <table style="width:100%;border-collapse:collapse;font-size:.9rem;">
            <thead>
            <tr>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Date</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Type</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Ville</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Surface</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Comparables</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Fiabilité</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Statut</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($requests as $r): ?>
                <tr>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;"><?= e((string) $r['created_at']) ?></td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;"><?= e((string) $r['property_type']) ?></td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;"><?= e((string) $r['city']) ?></td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;"><?= e((string) $r['surface']) ?> m²</td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;"><?= e((string) $r['comparables_count']) ?></td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;"><?= e((string) $r['confidence_level']) ?></td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;"><?= e((string) $r['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card" style="padding:1rem;">
        <h3>Carte des demandes (Google Maps)</h3>
        <p style="margin-bottom:.75rem;color:#6b7280">Connectez votre clé Google Maps pour afficher la carte interactive des demandes.</p>
        <div style="height:280px;border:1px dashed #d1d5db;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#6b7280;">
            Carte des demandes (placeholder)
        </div>
    </div>

    <div class="card" style="padding:1rem;margin-top:1rem;overflow:auto;">
        <h3>Historique imports</h3>
        <table style="width:100%;border-collapse:collapse;font-size:.9rem;">
            <thead>
            <tr>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Date</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Fichier</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Statut</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Lues</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Insérées</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">MAJ</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Rejetées</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($importStats['runs'] as $run): ?>
                <tr>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;"><?= e((string) $run['started_at']) ?></td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;"><?= e((string) $run['source_file']) ?></td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;"><?= e((string) $run['status']) ?></td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;"><?= e((string) $run['rows_read']) ?></td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;"><?= e((string) $run['rows_inserted']) ?></td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;"><?= e((string) $run['rows_updated']) ?></td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;"><?= e((string) $run['rows_rejected']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

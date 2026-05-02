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
    <style>
        .start-hero { background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%); border-radius: 16px; padding: 36px 40px; color: #fff; margin-bottom: 32px; box-shadow: 0 4px 20px rgba(15,34,55,.18); }
        .start-hero-badge { display: inline-block; background: rgba(201,168,76,.2); color: #c9a84c; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; padding: 4px 12px; border-radius: 20px; margin-bottom: 14px; border: 1px solid rgba(201,168,76,.35); }
        .start-hero h1 { font-size: 28px; font-weight: 700; color: #fff; margin: 0 0 12px; line-height: 1.25; }
        .start-hero p { font-size: 15px; color: rgba(255,255,255,.7); line-height: 1.65; max-width: 680px; margin: 0; }
        .start-steps-title { font-size: 12px; font-weight: 700; color: #8a95a3; text-transform: uppercase; letter-spacing: .07em; margin: 0 0 16px; }
        .start-steps { display: flex; flex-direction: column; gap: 14px; margin-bottom: 24px; }
        .start-step { display: flex; align-items: flex-start; gap: 18px; background: #fff; border-radius: 12px; padding: 20px 22px; box-shadow: 0 1px 6px rgba(0,0,0,.07); text-decoration: none; color: inherit; border-left: 4px solid #e8ecf0; }
        .start-step-num { flex-shrink: 0; width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; color: #64748b; }
        .start-step-body { flex: 1; }
        .start-step-label { font-size: 15px; font-weight: 600; color: #1e293b; margin-bottom: 3px; }
        .start-step-desc { font-size: 13px; color: #64748b; line-height: 1.5; }
        .start-step-arrow { flex-shrink: 0; color: #c9a84c; font-size: 16px; margin-top: 8px; }
        .start-cta { background: #fff; border-radius: 12px; padding: 24px 26px; box-shadow: 0 1px 6px rgba(0,0,0,.07); display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; margin-top: 16px; }
        .start-cta-text strong { display: block; font-size: 15px; font-weight: 600; color: #1e293b; margin-bottom: 4px; }
        .start-cta-text span { font-size: 13px; color: #64748b; }
        .start-cta-btn { display: inline-flex; align-items: center; gap: 8px; padding: 11px 22px; background: #c9a84c; color: #0f2237; border-radius: 8px; font-size: 14px; font-weight: 700; text-decoration: none; white-space: nowrap; }
        @media (max-width: 600px) { .start-hero { padding: 24px 20px; } .start-step { flex-wrap: wrap; } }
    </style>

    <div class="start-hero">
        <div class="start-hero-badge">Données DVF</div>
        <h1>Estimation DVF</h1>
        <p>Importez DVF, suivez les demandes et pilotez les estimations depuis un espace unique.</p>
    </div>

    <div class="start-steps-title">Flux de traitement</div>
    <div class="start-steps">
        <a href="/admin/?module=estimation_dvf" class="start-step">
            <div class="start-step-num">1</div>
            <div class="start-step-body">
                <div class="start-step-label">Importer les données DVF</div>
                <div class="start-step-desc">Chargez le CSV, contrôlez les erreurs et gardez un historique des imports.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>
        <a href="/admin/?module=estimation_dvf" class="start-step">
            <div class="start-step-num">2</div>
            <div class="start-step-body">
                <div class="start-step-label">Filtrer les demandes</div>
                <div class="start-step-desc">Retrouvez les demandes par ville, type de bien et statut.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>
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
        <form method="GET" action="/admin/" style="display:grid;gap:.75rem;grid-template-columns:repeat(5,minmax(0,1fr));">
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
    <div class="start-cta">
        <div class="start-cta-text">
            <strong>Maintenir les données à jour</strong>
            <span>Relancez un import dès qu’un nouveau fichier DVF est disponible.</span>
        </div>
        <a href="/admin/?module=estimation_dvf" class="start-cta-btn"><i class="fas fa-upload"></i> Revenir à l’import</a>
    </div>
    <?php
}

<?php
$pageTitle = 'Demandes de financement';
$pageDescription = 'Leads reçus depuis la page Financement';

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $rows = LeadService::list(['pipeline' => LeadService::SOURCE_FINANCEMENT]) ?? [];

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="demandes-financement-' . date('Ymd-His') . '.csv"');

    $out = fopen('php://output', 'wb');
    fputcsv($out, ['Date', 'Statut', 'Prénom', 'Nom', 'Email', 'Téléphone', 'Type de projet', 'Secteur', 'Budget', 'Apport', 'Situation pro', 'Délai', 'Message']);

    foreach ($rows as $lead) {
        $meta = is_array($lead['metadata'] ?? null) ? $lead['metadata'] : [];

        fputcsv($out, [
            (string)($lead['created_at'] ?? ''),
            LeadService::stageLabel((string)($lead['stage'] ?? 'nouveau')),
            (string)($lead['first_name'] ?? ''),
            (string)($lead['last_name'] ?? ''),
            (string)($lead['email'] ?? ''),
            (string)($lead['phone'] ?? ''),
            (string)($meta['type_projet'] ?? ''),
            (string)($meta['secteur_recherche'] ?? ''),
            (string)($meta['budget_estime'] ?? ''),
            (string)($meta['apport_personnel'] ?? ''),
            (string)($meta['situation_professionnelle'] ?? ''),
            (string)($meta['delai_projet'] ?? ''),
            (string)($lead['notes'] ?? ''),
        ]);
    }

    fclose($out);
    exit;
}

$leads = LeadService::list(['pipeline' => LeadService::SOURCE_FINANCEMENT]) ?? [];
$stats = [
    'total' => count($leads),
    'nouveau' => 0,
    'en_cours' => 0,
    'traite' => 0,
];

foreach ($leads as $lead) {
    $stage = (string)($lead['stage'] ?? 'nouveau');
    if (isset($stats[$stage])) {
        $stats[$stage]++;
    }
}

function renderContent(): void
{
    global $leads, $stats;

    if (isset($_GET['view']) && $_GET['view'] === 'liste') {
        ?>
        <style>
            .finance-toolbar{display:flex;justify-content:space-between;gap:1rem;align-items:center;flex-wrap:wrap;margin:0 0 1rem}
            .finance-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:.85rem;margin-bottom:1.2rem}
            .finance-stat{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:.9rem 1rem}
            .finance-grid{background:#fff;border:1px solid #e5e7eb;border-radius:14px;overflow:auto}
            .finance-grid table{width:100%;border-collapse:collapse;min-width:1100px}
            .finance-grid th,.finance-grid td{padding:.75rem .8rem;border-bottom:1px solid #f1f5f9;text-align:left;font-size:.9rem;vertical-align:top}
            .finance-meta{font-size:.78rem;color:#64748b}
            .finance-stage{display:inline-block;padding:.2rem .5rem;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-size:.76rem;font-weight:700}
            .start-hero{background:linear-gradient(135deg,#0f2237 0%,#1a3a5c 100%);border-radius:16px;padding:36px 40px;color:#fff;margin-bottom:32px;box-shadow:0 4px 20px rgba(15,34,55,.18)}
            .start-hero-badge{display:inline-block;background:rgba(201,168,76,.2);color:#c9a84c;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:4px 12px;border-radius:20px;margin-bottom:14px;border:1px solid rgba(201,168,76,.35)}
            .start-hero h1{font-size:28px;font-weight:700;color:#fff;margin:0 0 12px;line-height:1.25}
            .start-hero p{font-size:15px;color:rgba(255,255,255,.7);line-height:1.65;max-width:680px;margin:0}
        </style>

        <div class="start-hero">
            <div class="start-hero-badge">Suivi financement</div>
            <h1>Demandes de financement</h1>
            <p>Rubrique dédiée aux demandes reçues depuis la page Financement du site.</p>
        </div>

        <div class="finance-toolbar">
            <p style="margin:0;color:#64748b">Toutes les demandes sont listées avec leur date et leur statut de traitement.</p>
            <a class="btn btn-primary" href="/admin/?module=financement&export=csv">Exporter CSV</a>
        </div>

        <div class="finance-stats">
            <div class="finance-stat"><strong><?= $stats['total'] ?? 0 ?></strong><div>Total demandes</div></div>
            <div class="finance-stat"><strong><?= $stats['nouveau'] ?? 0 ?></strong><div>Nouveau</div></div>
            <div class="finance-stat"><strong><?= $stats['en_cours'] ?? 0 ?></strong><div>En cours</div></div>
            <div class="finance-stat"><strong><?= $stats['traite'] ?? 0 ?></strong><div>Traité</div></div>
        </div>

        <div class="finance-grid">
            <table>
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Contact</th>
                    <th>Projet</th>
                    <th>Secteur</th>
                    <th>Budget / Apport</th>
                    <th>Situation / Délai</th>
                    <th>Message</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$leads): ?>
                    <tr><td colspan="8">Aucune demande de financement pour le moment.</td></tr>
                <?php endif; ?>
                <?php foreach ($leads as $lead):
                    $meta = is_array($lead['metadata'] ?? null) ? $lead['metadata'] : [];
                    ?>
                    <tr>
                        <td><?= e(date('d/m/Y H:i', strtotime((string)$lead['created_at']))) ?></td>
                        <td><span class="finance-stage"><?= e(LeadService::stageLabel((string)($lead['stage'] ?? 'nouveau'))) ?></span></td>
                        <td>
                            <strong><?= e(trim((string)($lead['first_name'] ?? '') . ' ' . (string)($lead['last_name'] ?? ''))) ?></strong>
                            <div class="finance-meta"><?= e((string)($lead['email'] ?? '')) ?></div>
                            <div class="finance-meta"><?= e((string)($lead['phone'] ?? '')) ?></div>
                        </td>
                        <td><?= e((string)($meta['type_projet'] ?? ($lead['intent'] ?? '—'))) ?></td>
                        <td><?= e((string)($meta['secteur_recherche'] ?? '—')) ?></td>
                        <td>
                            <div>Budget : <?= e((string)($meta['budget_estime'] ?? '—')) ?></div>
                            <div class="finance-meta">Apport : <?= e((string)($meta['apport_personnel'] ?? '—')) ?></div>
                        </td>
                        <td>
                            <div><?= e((string)($meta['situation_professionnelle'] ?? '—')) ?></div>
                            <div class="finance-meta">Délai : <?= e((string)($meta['delai_projet'] ?? '—')) ?></div>
                        </td>
                        <td><?= e((string)($lead['notes'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        return;
    }

    // Hub page
    ?>
    <style>
        .finance-page { display: grid; gap: 22px; }
        .start-hero { background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%); border-radius: 16px; padding: 36px 40px; color: #fff; box-shadow: 0 4px 20px rgba(15, 34, 55, .18); }
        .start-hero-badge { display: inline-block; background: rgba(201,168,76,.2); color: #c9a84c; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; padding: 4px 12px; border-radius: 20px; margin-bottom: 14px; border: 1px solid rgba(201,168,76,.35); }
        .start-hero h1 { margin: 0 0 12px; font-size: 28px; font-weight: 700; line-height: 1.25; color: #fff; }
        .start-hero p { margin: 0; color: rgba(255,255,255,.7); font-size: 15px; line-height: 1.65; max-width: 680px; }
        .finance-modules { display: grid; grid-template-columns: 1fr; gap: 12px; }
        @media (min-width: 768px) { .finance-modules { grid-template-columns: repeat(2, 1fr); } }
        .finance-card { background: #fff; border-radius: 16px; padding: 18px; box-shadow: 0 1px 8px rgba(15,23,42,.08); border: 1px solid #e2e8f0; transition: transform .18s ease, box-shadow .18s ease; text-decoration: none; color: inherit; }
        .finance-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(15,23,42,.10); }
        .finance-card-head { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
        .finance-card-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
        .finance-card h3 { margin: 0; font-size: 16px; color: #0f172a; font-weight: 600; }
        .finance-card p { margin: 0; font-size: 14px; color: #475569; line-height: 1.6; }
        .finance-action { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: #2563eb; margin-top: 8px; }
        .start-cta { background: #fff; border-radius: 12px; padding: 24px 26px; box-shadow: 0 1px 6px rgba(0,0,0,.07); display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
        .start-cta-text strong { display: block; font-size: 15px; font-weight: 600; color: #1e293b; margin-bottom: 4px; }
        .start-cta-text span { font-size: 13px; color: #64748b; }
        .start-cta-btn { display: inline-flex; align-items: center; gap: 8px; padding: 11px 22px; background: #c9a84c; color: #0f2237; border-radius: 8px; font-size: 14px; font-weight: 700; text-decoration: none; white-space: nowrap; }
        @media (max-width: 600px) { .start-hero { padding: 24px 20px; } }
    </style>

    <section class="finance-page">
        <header class="start-hero">
            <div class="start-hero-badge">Financement</div>
            <h1>Gérez vos demandes de financement</h1>
            <p>Centralisez tous les contacts intéressés par vos services de financement immobilier.</p>
        </header>

        <div class="finance-modules">
            <a href="/admin/?module=financement&view=liste" class="finance-card">
                <div class="finance-card-head">
                    <div class="finance-card-icon" style="background:#eafaf1;color:#16a34a;"><i class="fas fa-list"></i></div>
                    <h3>Voir toutes les demandes</h3>
                </div>
                <p>Consultez la liste complète des demandes de financement reçues.</p>
                <span class="finance-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
            </a>

            <a href="/admin/?module=financement&view=liste" class="finance-card">
                <div class="finance-card-head">
                    <div class="finance-card-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-download"></i></div>
                    <h3>Exporter en CSV</h3>
                </div>
                <p>Téléchargez toutes les demandes pour analyse externe.</p>
                <span class="finance-action"><i class="fas fa-arrow-right"></i> Exporter</span>
            </a>

            <a href="/admin/?module=financement&view=stats" class="finance-card">
                <div class="finance-card-head">
                    <div class="finance-card-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-chart-bar"></i></div>
                    <h3>Statistiques</h3>
                </div>
                <p>Analysez vos demandes par statut et origine.</p>
                <span class="finance-action"><i class="fas fa-arrow-right"></i> Voir</span>
            </a>

            <div class="finance-card" style="opacity: 0.7; pointer-events: none;">
                <div class="finance-card-head">
                    <div class="finance-card-icon" style="background:#fdedec;color:#dc2626;"><i class="fas fa-robot"></i></div>
                    <h3>Scoring IA</h3>
                </div>
                <p>Analyse automatique du potentiel de chaque demande.</p>
                <span style="display:inline-flex;align-items:center;gap:.3rem;font-size:.7rem;font-weight:700;background:#fef3c7;color:#92400e;padding:.22rem .56rem;border-radius:999px;margin-top:.5rem;width:max-content;"><i class="fas fa-clock"></i> Bientôt</span>
            </div>
        </div>

        <section class="start-cta">
            <div class="start-cta-text">
                <strong>Gérer les demandes</strong>
                <span>Consultez les demandes reçues et maintenez un suivi organisé.</span>
            </div>
            <a href="/admin/?module=financement&view=liste" class="start-cta-btn"><i class="fas fa-rocket"></i> Démarrer</a>
        </section>
    </section>
    <?php
}

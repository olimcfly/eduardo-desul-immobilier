<?php
$pageTitle = 'Demandes de financement';
$pageDescription = 'Leads reçus depuis la page Financement';

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $rows = LeadService::list(['pipeline' => LeadService::SOURCE_FINANCEMENT]);

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

$leads = LeadService::list(['pipeline' => LeadService::SOURCE_FINANCEMENT]);
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
    </style>

    <div class="page-header">
        <h1><i class="fas fa-hand-holding-dollar page-icon"></i> Demandes de <span class="page-title-accent">financement</span></h1>
        <p>Rubrique dédiée aux demandes reçues depuis la page Financement du site.</p>
    </div>

    <div class="finance-toolbar">
        <p style="margin:0;color:#64748b">Toutes les demandes sont listées avec leur date et leur statut de traitement.</p>
        <a class="btn btn-primary" href="?module=financement&export=csv">Exporter CSV</a>
    </div>

    <div class="finance-stats">
        <div class="finance-stat"><strong><?= $stats['total'] ?></strong><div>Total demandes</div></div>
        <div class="finance-stat"><strong><?= $stats['nouveau'] ?></strong><div>Nouveau</div></div>
        <div class="finance-stat"><strong><?= $stats['en_cours'] ?></strong><div>En cours</div></div>
        <div class="finance-stat"><strong><?= $stats['traite'] ?></strong><div>Traité</div></div>
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
}

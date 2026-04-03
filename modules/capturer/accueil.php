<?php
$pageTitle = 'CRM Leads';
$pageDescription = 'Centralisez et pilotez les leads issus du front-end';

$view = isset($_GET['view']) ? strtolower((string)$_GET['view']) : 'grid';
$view = in_array($view, ['grid', 'kanban'], true) ? $view : 'grid';

$pipeline = isset($_GET['pipeline']) ? strtolower((string)$_GET['pipeline']) : '';
$pipelines = LeadService::stageMatrix();
if ($pipeline !== '' && !isset($pipelines[$pipeline])) {
    $pipeline = '';
}

$leads = LeadService::list($pipeline ? ['pipeline' => $pipeline] : []);

$stats = [
    'total' => count($leads),
    'estimation' => 0,
    'telechargement' => 0,
    'contact' => 0,
    'rdv' => 0,
];

foreach ($leads as $lead) {
    $source = (string)($lead['source_type'] ?? 'autre');
    if (isset($stats[$source])) {
        $stats[$source]++;
    }
    if (str_contains((string)($lead['stage'] ?? ''), 'rdv')) {
        $stats['rdv']++;
    }
}

function renderContent(): void
{
    global $view, $pipeline, $pipelines, $leads, $stats;

    $kanbanStages = $pipeline && isset($pipelines[$pipeline])
        ? [$pipeline => $pipelines[$pipeline]]
        : $pipelines;
    ?>
    <style>
        .crm-stats {display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin:1rem 0 1.5rem;}
        .crm-stat {background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:1rem;box-shadow:0 10px 30px rgba(15,23,42,.04);}
        .crm-toolbar{display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;}
        .crm-pill{display:inline-flex;gap:.5rem;padding:.5rem .85rem;border-radius:999px;border:1px solid #d1d5db;text-decoration:none;color:#334155;font-weight:600;}
        .crm-pill.active{background:#0f172a;color:#fff;border-color:#0f172a;}
        .crm-grid{background:#fff;border:1px solid #e5e7eb;border-radius:14px;overflow:auto;}
        .crm-grid table{width:100%;border-collapse:collapse;min-width:860px;}
        .crm-grid th,.crm-grid td{padding:.75rem .8rem;border-bottom:1px solid #f1f5f9;text-align:left;font-size:.92rem;}
        .crm-badge{display:inline-block;padding:.2rem .55rem;border-radius:999px;background:#eef2ff;color:#3730a3;font-size:.78rem;font-weight:700;}
        .crm-kanban{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:.9rem;align-items:start;}
        .crm-col{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:.7rem;}
        .crm-card{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:.65rem;margin-top:.6rem;}
        .crm-meta{font-size:.78rem;color:#64748b;}
        .crm-pipelines{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1rem;margin-top:1.5rem;}
        .crm-pipeline{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1rem;}
    </style>

    <div class="page-header">
        <h1><i class="fas fa-table-columns page-icon"></i> CRM <span class="page-title-accent">Leads</span></h1>
        <p>Version 1 admin : capture centralisée des leads front-end (estimation, contact, téléchargement, etc.).</p>
    </div>

    <div class="crm-stats">
        <div class="crm-stat"><strong><?= $stats['total'] ?></strong><div>Total leads</div></div>
        <div class="crm-stat"><strong><?= $stats['estimation'] ?></strong><div>Estimations</div></div>
        <div class="crm-stat"><strong><?= $stats['telechargement'] ?></strong><div>Téléchargements</div></div>
        <div class="crm-stat"><strong><?= $stats['contact'] ?></strong><div>Contacts généraux</div></div>
        <div class="crm-stat"><strong><?= $stats['rdv'] ?></strong><div>Demandes RDV</div></div>
    </div>

    <div class="crm-toolbar">
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <a class="crm-pill <?= $view === 'grid' ? 'active' : '' ?>" href="?module=capturer&view=grid<?= $pipeline ? '&pipeline=' . urlencode($pipeline) : '' ?>">Vue grille</a>
            <a class="crm-pill <?= $view === 'kanban' ? 'active' : '' ?>" href="?module=capturer&view=kanban<?= $pipeline ? '&pipeline=' . urlencode($pipeline) : '' ?>">Vue Kanban</a>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <a class="crm-pill <?= $pipeline === '' ? 'active' : '' ?>" href="?module=capturer&view=<?= urlencode($view) ?>">Tous les pipelines</a>
            <?php foreach (array_keys($pipelines) as $pipe): ?>
                <a class="crm-pill <?= $pipeline === $pipe ? 'active' : '' ?>" href="?module=capturer&view=<?= urlencode($view) ?>&pipeline=<?= urlencode($pipe) ?>"><?= e(LeadService::sourceLabel($pipe)) ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($view === 'grid'): ?>
        <div class="crm-grid">
            <table>
                <thead>
                <tr>
                    <th>Lead</th>
                    <th>Source</th>
                    <th>Pipeline</th>
                    <th>Étape</th>
                    <th>Intention</th>
                    <th>Contact</th>
                    <th>Créé le</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$leads): ?>
                    <tr><td colspan="7">Aucun lead capturé pour l'instant.</td></tr>
                <?php endif; ?>
                <?php foreach ($leads as $lead): ?>
                    <tr>
                        <td><strong><?= e(trim(($lead['first_name'] ?? '') . ' ' . ($lead['last_name'] ?? ''))) ?: 'Lead sans nom' ?></strong></td>
                        <td><span class="crm-badge"><?= e(LeadService::sourceLabel((string)($lead['source_type'] ?? 'autre'))) ?></span></td>
                        <td><?= e(LeadService::sourceLabel((string)($lead['pipeline'] ?? 'autre'))) ?></td>
                        <td><?= e(LeadService::stageLabel((string)($lead['stage'] ?? 'nouveau'))) ?></td>
                        <td><?= e((string)($lead['intent'] ?? '—')) ?></td>
                        <td>
                            <div><?= e((string)($lead['email'] ?? '')) ?></div>
                            <div class="crm-meta"><?= e((string)($lead['phone'] ?? '')) ?></div>
                        </td>
                        <td><?= e(date('d/m/Y H:i', strtotime((string)$lead['created_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <?php foreach ($kanbanStages as $source => $stages): ?>
            <h3 style="margin:1.25rem 0 .5rem;">Pipeline : <?= e(LeadService::sourceLabel($source)) ?></h3>
            <div class="crm-kanban">
                <?php foreach ($stages as $stage): ?>
                    <div class="crm-col">
                        <strong><?= e(LeadService::stageLabel($stage)) ?></strong>
                        <?php
                        $cards = array_filter($leads, static fn(array $lead): bool =>
                            ($lead['pipeline'] ?? '') === $source && ($lead['stage'] ?? '') === $stage
                        );
                        ?>
                        <?php if (!$cards): ?>
                            <div class="crm-meta" style="margin-top:.6rem;">Aucun lead</div>
                        <?php endif; ?>
                        <?php foreach ($cards as $lead): ?>
                            <article class="crm-card">
                                <strong><?= e(trim(($lead['first_name'] ?? '') . ' ' . ($lead['last_name'] ?? ''))) ?: 'Lead' ?></strong>
                                <div><?= e((string)($lead['intent'] ?? '—')) ?></div>
                                <div class="crm-meta"><?= e((string)($lead['email'] ?? '')) ?></div>
                                <div class="crm-meta"><?= e(date('d/m H:i', strtotime((string)$lead['created_at']))) ?></div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="crm-pipelines">
        <?php foreach ($pipelines as $source => $stages): ?>
            <div class="crm-pipeline">
                <h4 style="margin-bottom:.65rem;">Pipeline configurable : <?= e(LeadService::sourceLabel($source)) ?></h4>
                <p class="crm-meta" style="margin-bottom:.65rem;">Étapes recommandées par type de source (V1).</p>
                <ul style="margin:0;padding-left:1rem;display:grid;gap:.2rem;">
                    <?php foreach ($stages as $stage): ?>
                        <li><?= e(LeadService::stageLabel($stage)) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}

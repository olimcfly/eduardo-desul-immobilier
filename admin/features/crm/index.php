<?php
/**
 * Module CRM - Gestion des leads
 */

require_once __DIR__ . '/../../session-helper.php';
startAdminSession();

if (!isAdminLoggedIn()) {
    redirectAdmin('/admin/login');
}

// Filtre par source
$filter = $_GET['source'] ?? '';
$validSources = ['estimation', 'contact', 'telechargement', 'financement', 'avis_valeur', 'autre'];

$filters = [];
if (!empty($filter) && in_array($filter, $validSources, true)) {
    $filters['source_type'] = $filter;
}

// Récupérer les leads
$leads = LeadService::list($filters);
$totalLeads = count($leads);

// Statistiques
$statsEstimation = count(LeadService::list(['source_type' => 'estimation']));
$statsContact = count(LeadService::list(['source_type' => 'contact']));
$statsTelechargement = count(LeadService::list(['source_type' => 'telechargement']));
$statsAll = count(LeadService::list());

$pageTitle = 'CRM - Leads';
$currentModule = 'crm';
?>

<div class="admin-page">
    <div class="page-header">
        <h1>📊 CRM - Gestion des Leads</h1>
        <p>Tableau de bord des leads enregistrées depuis vos formulaires.</p>
    </div>

    <!-- Statistiques -->
    <div class="stats-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:1.5rem;margin-bottom:2rem">
        <div class="stat-card" style="background:#f8f9fa;border:1px solid #dee2e6;border-radius:8px;padding:1.5rem;text-align:center">
            <div style="font-size:2rem;font-weight:700;color:#1f3a5e"><?= $statsAll ?></div>
            <div style="font-size:0.85rem;color:#6c757d;margin-top:0.5rem">Leads totales</div>
        </div>
        <div class="stat-card" style="background:#e7f3ff;border:1px solid #b3e5fc;border-radius:8px;padding:1.5rem;text-align:center">
            <div style="font-size:2rem;font-weight:700;color:#0277bd"><?= $statsEstimation ?></div>
            <div style="font-size:0.85rem;color:#0277bd;margin-top:0.5rem">Estimations</div>
        </div>
        <div class="stat-card" style="background:#f3e5f5;border:1px solid #ce93d8;border-radius:8px;padding:1.5rem;text-align:center">
            <div style="font-size:2rem;font-weight:700;color:#6a1b9a"><?= $statsContact ?></div>
            <div style="font-size:0.85rem;color:#6a1b9a;margin-top:0.5rem">Contacts</div>
        </div>
        <div class="stat-card" style="background:#fff3e0;border:1px solid #ffe0b2;border-radius:8px;padding:1.5rem;text-align:center">
            <div style="font-size:2rem;font-weight:700;color:#e65100"><?= $statsTelechargement ?></div>
            <div style="font-size:0.85rem;color:#e65100;margin-top:0.5rem">Téléchargements</div>
        </div>
    </div>

    <!-- Filtres -->
    <div style="margin-bottom:2rem;padding:1rem;background:#f8f9fa;border-radius:8px;border:1px solid #dee2e6">
        <div style="display:flex;gap:1rem;flex-wrap:wrap">
            <a href="/admin?module=crm" class="btn <?= empty($filter) ? 'btn--accent' : 'btn--outline' ?>" style="padding:0.5rem 1rem;font-size:0.9rem">
                Tous (<?= $statsAll ?>)
            </a>
            <a href="/admin?module=crm&source=estimation" class="btn <?= $filter === 'estimation' ? 'btn--accent' : 'btn--outline' ?>" style="padding:0.5rem 1rem;font-size:0.9rem">
                Estimations (<?= $statsEstimation ?>)
            </a>
            <a href="/admin?module=crm&source=contact" class="btn <?= $filter === 'contact' ? 'btn--accent' : 'btn--outline' ?>" style="padding:0.5rem 1rem;font-size:0.9rem">
                Contacts (<?= $statsContact ?>)
            </a>
            <a href="/admin?module=crm&source=telechargement" class="btn <?= $filter === 'telechargement' ? 'btn--accent' : 'btn--outline' ?>" style="padding:0.5rem 1rem;font-size:0.9rem">
                Téléchargements (<?= $statsTelechargement ?>)
            </a>
        </div>
    </div>

    <!-- Tableau des leads -->
    <div style="overflow-x:auto;background:white;border-radius:8px;border:1px solid #dee2e6">
        <?php if (empty($leads)): ?>
            <div style="padding:2rem;text-align:center;color:#6c757d">
                <p style="font-size:1.1rem;margin-bottom:0.5rem">Aucune lead pour le moment.</p>
                <p style="font-size:0.9rem">Les formulaires remplis s'afficheront ici.</p>
            </div>
        <?php else: ?>
            <table style="width:100%;border-collapse:collapse">
                <thead style="background:#f8f9fa;border-bottom:2px solid #dee2e6">
                    <tr>
                        <th style="padding:1rem;text-align:left;font-weight:600;color:#333">ID</th>
                        <th style="padding:1rem;text-align:left;font-weight:600;color:#333">Prénom</th>
                        <th style="padding:1rem;text-align:left;font-weight:600;color:#333">Email</th>
                        <th style="padding:1rem;text-align:left;font-weight:600;color:#333">Téléphone</th>
                        <th style="padding:1rem;text-align:left;font-weight:600;color:#333">Type</th>
                        <th style="padding:1rem;text-align:left;font-weight:600;color:#333">Source</th>
                        <th style="padding:1rem;text-align:left;font-weight:600;color:#333">Stage</th>
                        <th style="padding:1rem;text-align:left;font-weight:600;color:#333">Date</th>
                        <th style="padding:1rem;text-align:center;font-weight:600;color:#333">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leads as $lead): ?>
                    <tr style="border-bottom:1px solid #dee2e6">
                        <td style="padding:1rem;color:#666"><?= htmlspecialchars($lead['id']) ?></td>
                        <td style="padding:1rem;color:#333;font-weight:500">
                            <?= htmlspecialchars($lead['first_name'] . ' ' . ($lead['last_name'] ?? '')) ?>
                        </td>
                        <td style="padding:1rem;color:#0277bd">
                            <a href="mailto:<?= htmlspecialchars($lead['email']) ?>" style="text-decoration:none;color:#0277bd">
                                <?= htmlspecialchars($lead['email']) ?>
                            </a>
                        </td>
                        <td style="padding:1rem;color:#666">
                            <?php if ($lead['phone']): ?>
                                <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" style="text-decoration:none;color:#666">
                                    <?= htmlspecialchars($lead['phone']) ?>
                                </a>
                            <?php else: ?>
                                <span style="color:#ccc">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:1rem;color:#666">
                            <span style="background:#f0f4f8;padding:0.25rem 0.75rem;border-radius:4px;font-size:0.85rem">
                                <?= htmlspecialchars($lead['property_type'] ?? '—') ?>
                            </span>
                        </td>
                        <td style="padding:1rem">
                            <span style="background:#e7f3ff;color:#0277bd;padding:0.25rem 0.75rem;border-radius:4px;font-size:0.85rem">
                                <?= htmlspecialchars($lead['source_type']) ?>
                            </span>
                        </td>
                        <td style="padding:1rem;color:#666">
                            <span style="background:#fce4ec;color:#c2185b;padding:0.25rem 0.75rem;border-radius:4px;font-size:0.85rem">
                                <?= htmlspecialchars($lead['stage'] ?? 'non défini') ?>
                            </span>
                        </td>
                        <td style="padding:1rem;color:#999;font-size:0.9rem">
                            <?= date('d/m/Y H:i', strtotime($lead['created_at'])) ?>
                        </td>
                        <td style="padding:1rem;text-align:center">
                            <a href="/admin?module=crm&action=view&id=<?= $lead['id'] ?>"
                               style="color:#0277bd;text-decoration:none;font-size:0.9rem">
                                Détails
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Note -->
    <div style="margin-top:2rem;padding:1rem;background:#e8f5e9;border-left:4px solid #4caf50;border-radius:4px">
        <strong>ℹ️ Info:</strong> Les leads sont enregistrées automatiquement depuis les formulaires d'estimation, contact et autres conversions.
    </div>
</div>

<style>
.btn {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    text-decoration: none;
    border: 1px solid #dee2e6;
    background: white;
    color: #333;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.9rem;
}
.btn:hover {
    border-color: #0277bd;
    color: #0277bd;
}
.btn--accent {
    background: #0277bd !important;
    color: white !important;
    border-color: #0277bd !important;
}
.btn--outline {
    background: white;
    color: #333;
    border-color: #dee2e6;
}
</style>

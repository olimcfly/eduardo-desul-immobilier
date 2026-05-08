<?php
/**
 * Module CRM - Gestion des leads
 */

require_once __DIR__ . '/../../session-helper.php';
startAdminSession();

if (!isAdminLoggedIn()) {
    redirectAdmin('/admin/auth/login.php');
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

<div class="crm-page admin-page admin-container">
    <header class="crm-page-header">
        <div>
            <div class="crm-page-kicker"><i class="fas fa-chart-line"></i> CRM</div>
            <h1 class="crm-page-title">CRM - Gestion des Leads</h1>
            <p class="crm-page-subtitle">Tableau de bord des leads enregistrées depuis vos formulaires.</p>
        </div>
    </header>

    <section class="crm-card crm-card--padded">
        <div class="crm-filters__chips">
            <a href="/admin?module=crm" class="crm-btn <?= empty($filter) ? 'crm-btn-primary' : 'crm-btn-secondary' ?>">
                <span class="crm-badge crm-badge--neutral"><?= (int) $statsAll ?></span>
                Tous
            </a>
            <a href="/admin?module=crm&source=estimation" class="crm-btn <?= $filter === 'estimation' ? 'crm-btn-primary' : 'crm-btn-secondary' ?>">
                <span class="crm-badge crm-badge--primary"><?= (int) $statsEstimation ?></span>
                Estimations
            </a>
            <a href="/admin?module=crm&source=contact" class="crm-btn <?= $filter === 'contact' ? 'crm-btn-primary' : 'crm-btn-secondary' ?>">
                <span class="crm-badge crm-badge--neutral"><?= (int) $statsContact ?></span>
                Contacts
            </a>
            <a href="/admin?module=crm&source=telechargement" class="crm-btn <?= $filter === 'telechargement' ? 'crm-btn-primary' : 'crm-btn-secondary' ?>">
                <span class="crm-badge crm-badge--success"><?= (int) $statsTelechargement ?></span>
                Téléchargements
            </a>
        </div>
    </section>

    <?php if (empty($leads)): ?>
        <div class="crm-card crm-card--padded" style="text-align:center;color:var(--ui-muted);">
            <p style="font-size:1.05rem;font-weight:800;color:var(--ui-text);margin-bottom:6px;">Aucune lead pour le moment.</p>
            <p>Les formulaires remplis s'afficheront ici.</p>
        </div>
    <?php else: ?>
        <div class="crm-card crm-table-wrapper">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Type</th>
                        <th>Source</th>
                        <th>Stage</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leads as $lead): ?>
                    <tr>
                        <td><?= htmlspecialchars($lead['id']) ?></td>
                        <td><?= htmlspecialchars($lead['first_name'] . ' ' . ($lead['last_name'] ?? '')) ?></td>
                        <td><a href="mailto:<?= htmlspecialchars($lead['email']) ?>"><?= htmlspecialchars($lead['email']) ?></a></td>
                        <td>
                            <?php if ($lead['phone']): ?>
                                <a href="tel:<?= htmlspecialchars($lead['phone']) ?>"><?= htmlspecialchars($lead['phone']) ?></a>
                            <?php else: ?>
                                <span style="color:var(--ui-muted);">—</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="crm-badge crm-badge--neutral"><?= htmlspecialchars($lead['property_type'] ?? '—') ?></span></td>
                        <td><span class="crm-badge crm-badge--primary"><?= htmlspecialchars($lead['source_type']) ?></span></td>
                        <td><span class="crm-badge crm-badge--warning"><?= htmlspecialchars($lead['stage'] ?? 'non défini') ?></span></td>
                        <td><?= date('d/m/Y H:i', strtotime($lead['created_at'])) ?></td>
                        <td><a href="/admin?module=crm&action=view&id=<?= $lead['id'] ?>" class="crm-btn crm-btn-primary">Détails</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="crm-mobile-list">
            <?php foreach ($leads as $lead): ?>
                <article class="crm-card crm-mobile-card">
                    <div class="crm-mobile-card__top">
                        <div>
                            <div class="crm-mobile-card__title"><?= htmlspecialchars($lead['first_name'] . ' ' . ($lead['last_name'] ?? '')) ?></div>
                            <div class="crm-mobile-card__meta"><?= htmlspecialchars($lead['email']) ?></div>
                        </div>
                        <span class="crm-badge crm-badge--warning"><?= htmlspecialchars($lead['stage'] ?? 'non défini') ?></span>
                    </div>
                    <div class="crm-mobile-card__meta">
                        <div><strong>ID :</strong> <?= htmlspecialchars($lead['id']) ?></div>
                        <div><strong>Téléphone :</strong> <?= !empty($lead['phone']) ? htmlspecialchars($lead['phone']) : '—' ?></div>
                        <div><strong>Type :</strong> <?= htmlspecialchars($lead['property_type'] ?? '—') ?></div>
                        <div><strong>Source :</strong> <?= htmlspecialchars($lead['source_type']) ?></div>
                        <div><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($lead['created_at'])) ?></div>
                    </div>
                    <div class="crm-mobile-card__actions">
                        <a href="/admin?module=crm&action=view&id=<?= $lead['id'] ?>" class="crm-btn crm-btn-primary">Détails</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="crm-card crm-card--padded" style="border-left:4px solid var(--ui-success);background:var(--ui-success-weak);color:#14532d;">
        <strong>Info</strong> Les leads sont enregistrées automatiquement depuis les formulaires d'estimation, contact et autres conversions.
    </div>
</div>

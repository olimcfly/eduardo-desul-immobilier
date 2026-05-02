<?php

declare(strict_types=1);

$pageTitle = 'Suivi des conversions';
$pageDescription = 'Tableau de bord des conversions et tracking';

function renderContent(): void {
    $conversionType = $_GET['type'] ?? null;
    $validTypes = [
        'estimation_gratuite_simple',
        'rapport_telechargement',
        'rdv_demande',
        'contact_formulaire',
        'guide_gratuit_telechargement',
        'guide_payant_telechargement',
    ];

    if (!empty($conversionType) && !in_array($conversionType, $validTypes, true)) {
        $conversionType = null;
    }

    $conversionLabels = [
        'estimation_gratuite_simple' => ['label' => 'Estimations Simples', 'icon' => '📊', 'color' => '#3b82f6', 'desc' => 'Demandes d\'estimation sans contact immédiat'],
        'rapport_telechargement' => ['label' => 'Téléchargements de Rapport', 'icon' => '📄', 'color' => '#10b981', 'desc' => 'Rapports téléchargés par les visiteurs'],
        'rdv_demande' => ['label' => 'Demandes de RDV', 'icon' => '📅', 'color' => '#f59e0b', 'desc' => 'Demandes de rendez-vous avec conseiller'],
        'contact_formulaire' => ['label' => 'Contacts via Formulaire', 'icon' => '💬', 'color' => '#8b5cf6', 'desc' => 'Messages de contact reçus'],
        'guide_gratuit_telechargement' => ['label' => 'Guides Gratuits', 'icon' => '📚', 'color' => '#ec4899', 'desc' => 'Guides gratuits téléchargés'],
        'guide_payant_telechargement' => ['label' => 'Guides Payants (7€)', 'icon' => '💳', 'color' => '#ef4444', 'desc' => 'Guides payants téléchargés'],
    ];

    // Récupérer les stats
    $stats = ConversionTrackingService::getTotalsByType();
    $statsMap = [];
    foreach ($stats as $stat) {
        $statsMap[$stat['conversion_type']] = $stat;
    }

    // Récupérer les conversions récentes
    $recentConversions = ConversionTrackingService::getRecent($conversionType, 100);
    ?>

    <style>
        .conversion-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
            margin-bottom: 32px;
        }
        .conversion-card {
            background: white;
            border-radius: 12px;
            padding: 16px;
            border-left: 4px solid;
            box-shadow: 0 1px 6px rgba(0,0,0,.07);
            cursor: pointer;
            transition: transform .2s, box-shadow .2s;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .conversion-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,.12);
        }
        .conversion-card.active {
            background: #f0f4f8;
            box-shadow: 0 2px 8px rgba(0,0,0,.15);
        }
        .conversion-card-icon {
            font-size: 32px;
            margin-bottom: 8px;
        }
        .conversion-card-count {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .conversion-card-label {
            font-size: 12px;
            color: #64748b;
        }
        .table-container {
            background: white;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .conversion-table {
            width: 100%;
            border-collapse: collapse;
        }
        .conversion-table thead {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }
        .conversion-table th {
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            color: #334155;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        .conversion-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
        }
        .conversion-table tr:hover {
            background: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-green { background: #dcfce7; color: #166534; }
    </style>

    <div style="margin-bottom: 24px;">
        <h2 style="font-size: 24px; font-weight: 700; margin-bottom: 4px; color: #1e293b;">
            📊 Suivi des conversions
        </h2>
        <p style="color: #64748b; font-size: 14px;">
            Trackez tous les types de conversions et interactions depuis votre site
        </p>
    </div>

    <!-- Grille de sélection -->
    <div class="conversion-grid">
        <a href="/admin/?module=crm-hub&action=conversions" class="conversion-card <?= empty($conversionType) ? 'active' : '' ?>" style="border-left-color: #6b7280;">
            <div class="conversion-card-icon">📈</div>
            <div class="conversion-card-count">
                <?= array_sum(array_column($stats, 'total_count')) ?>
            </div>
            <div class="conversion-card-label">Toutes conversions</div>
        </a>

        <?php foreach ($conversionLabels as $type => $config): ?>
        <a href="/admin/?module=crm-hub&action=conversions&type=<?= urlencode($type) ?>"
           class="conversion-card <?= $conversionType === $type ? 'active' : '' ?>"
           style="border-left-color: <?= $config['color'] ?>; color: inherit;">
            <div class="conversion-card-icon"><?= $config['icon'] ?></div>
            <div class="conversion-card-count">
                <?= $statsMap[$type]['total_count'] ?? 0 ?>
            </div>
            <div class="conversion-card-label"><?= $config['label'] ?></div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Détails du type sélectionné -->
    <?php if ($conversionType && isset($conversionLabels[$conversionType])): ?>
    <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 24px; border-left: 4px solid <?= $conversionLabels[$conversionType]['color'] ?>;">
        <h3 style="font-size: 18px; font-weight: 700; margin: 0 0 8px; color: #1e293b;">
            <?= $conversionLabels[$conversionType]['icon'] ?> <?= $conversionLabels[$conversionType]['label'] ?>
        </h3>
        <p style="color: #64748b; font-size: 13px; margin: 0;">
            <?= $conversionLabels[$conversionType]['desc'] ?>
        </p>
        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e2e8f0; display: flex; gap: 20px;">
            <div>
                <div style="font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Total</div>
                <div style="font-size: 24px; font-weight: 700; color: <?= $conversionLabels[$conversionType]['color'] ?>;">
                    <?= $statsMap[$conversionType]['total_count'] ?? 0 ?>
                </div>
            </div>
            <div>
                <div style="font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Avec email</div>
                <div style="font-size: 24px; font-weight: 700; color: #10b981;">
                    <?= $statsMap[$conversionType]['with_email_count'] ?? 0 ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tableau des conversions récentes -->
    <div style="margin-bottom: 24px;">
        <div style="margin-bottom: 16px;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: #1e293b;">
                Conversions récentes
                <?php if ($conversionType): ?>
                    (<?= $conversionLabels[$conversionType]['label'] ?>)
                <?php endif; ?>
            </h3>
        </div>

        <?php if (empty($recentConversions)): ?>
        <div style="background: white; border-radius: 12px; padding: 40px; text-align: center; color: #64748b;">
            <div style="font-size: 14px;">Aucune conversion pour le moment</div>
        </div>
        <?php else: ?>
        <div class="table-container">
            <div class="table-responsive">
                <table class="conversion-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Source</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentConversions as $conversion): ?>
                        <tr>
                            <td>
                                <?php if (isset($conversionLabels[$conversion['conversion_type']])): ?>
                                    <span class="badge badge-blue">
                                        <?= $conversionLabels[$conversion['conversion_type']]['icon'] ?>
                                        <?= substr($conversionLabels[$conversion['conversion_type']]['label'], 0, 12) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-blue"><?= $conversion['conversion_type'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($conversion['first_name'] ?? '—') ?></td>
                            <td>
                                <?php if ($conversion['email']): ?>
                                    <a href="mailto:<?= htmlspecialchars($conversion['email']) ?>" style="color: #0284c7; text-decoration: none;">
                                        <?= htmlspecialchars(substr($conversion['email'], 0, 30)) ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: #cbd5e1;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($conversion['phone']): ?>
                                    <a href="tel:<?= htmlspecialchars($conversion['phone']) ?>" style="color: #0284c7; text-decoration: none;">
                                        <?= htmlspecialchars($conversion['phone']) ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: #cbd5e1;">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 12px; color: #94a3b8;">
                                <?= basename($conversion['source_page'] ?? '/') ?: '/' ?>
                            </td>
                            <td style="color: #94a3b8;">
                                <?= date('d/m/Y H:i', strtotime($conversion['created_at'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php
}

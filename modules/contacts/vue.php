<?php

declare(strict_types=1);

$leadId = (int)($_GET['id'] ?? 0);

if ($leadId <= 0) {
    $pageTitle = 'Contact introuvable';
    function renderContent(): void {
        ?>
        <div style="padding: 40px; text-align: center; color: #9ca3af;">
            <h2 style="color: #374151; margin-bottom: 16px;">Contact non trouvé</h2>
            <p>Le contact que vous recherchez n'existe pas ou a été supprimé.</p>
            <a href="/admin?module=contacts" style="display: inline-block; margin-top: 16px; padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 8px;">Retour aux contacts</a>
        </div>
        <?php
    }
    return;
}

try {
    $stmt = db()->prepare('SELECT * FROM crm_leads WHERE id = ?');
    $stmt->execute([$leadId]);
    $lead = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lead) {
        $pageTitle = 'Contact introuvable';
        function renderContent(): void {
            ?>
            <div style="padding: 40px; text-align: center; color: #9ca3af;">
                <h2 style="color: #374151; margin-bottom: 16px;">Contact non trouvé</h2>
                <p>Le contact que vous recherchez n'existe pas ou a été supprimé.</p>
                <a href="/admin?module=contacts" style="display: inline-block; margin-top: 16px; padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 8px;">Retour aux contacts</a>
            </div>
            <?php
        }
        return;
    }
} catch (Throwable $e) {
    error_log('Lead fetch error: ' . $e->getMessage());
    $pageTitle = 'Erreur';
    function renderContent(): void {
        ?>
        <div style="padding: 40px; text-align: center; color: #9ca3af;">
            <h2 style="color: #374151; margin-bottom: 16px;">Erreur</h2>
            <p>Une erreur est survenue lors du chargement du contact.</p>
            <a href="/admin?module=contacts" style="display: inline-block; margin-top: 16px; padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 8px;">Retour aux contacts</a>
        </div>
        <?php
    }
    return;
}

$pageTitle = htmlspecialchars($lead['first_name'] . ' ' . $lead['last_name']);

$sourceLabels = [
    'estimation' => ['📊 Estimation gratuite', '#3b82f6'],
    'contact' => ['💬 Contact formulaire', '#8b5cf6'],
    'telechargement' => ['📚 Téléchargement ressource', '#ec4899'],
    'financement' => ['💰 Demande financement', '#f59e0b'],
    'avis_valeur' => ['📋 Avis de valeur', '#0e7490'],
    'autre' => ['• Autre source', '#6b7280']
];

$sourceType = $lead['source_type'] ?? 'autre';
[$sourceLabel, $sourceColor] = $sourceLabels[$sourceType] ?? $sourceLabels['autre'];

$metadata = !empty($lead['metadata_json']) ? json_decode($lead['metadata_json'], true) : [];

function renderContent(): void {
    global $lead, $sourceLabel, $sourceColor, $metadata;
    ?>
    <style>
        .lead-header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }

        .lead-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .lead-source {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 8px;
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 16px;
        }

        .lead-section {
            margin-bottom: 24px;
        }

        .section-title {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .info-row {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 16px;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #6b7280;
        }

        .info-value {
            color: #1f2937;
        }

        .info-value a {
            color: #3b82f6;
            text-decoration: none;
        }

        .info-value a:hover {
            text-decoration: underline;
        }

        .lead-contact {
            background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
            color: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
        }

        .lead-contact h2 {
            font-size: 18px;
            margin-bottom: 16px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            font-size: 15px;
        }

        .contact-item:last-child {
            margin-bottom: 0;
        }

        .contact-icon {
            font-size: 20px;
            flex-shrink: 0;
        }

        .stage-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            background: #f0f0f0;
            color: #374151;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .metadata-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 13px;
        }

        .metadata-table th {
            background: #f9fafb;
            padding: 10px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
        }

        .metadata-table td {
            padding: 10px;
            border-bottom: 1px solid #f3f4f6;
        }

        .metadata-table tr:last-child td {
            border-bottom: none;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            margin-bottom: 24px;
            background: #f3f4f6;
            color: #374151;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.2s;
        }

        .back-link:hover {
            background: #e5e7eb;
        }

        .priority-high { color: #dc2626; font-weight: 700; }
        .priority-normal { color: #f59e0b; }
        .priority-low { color: #10b981; }

        .notes-box {
            background: #f9fafb;
            border-left: 4px solid #c9a84c;
            padding: 16px;
            border-radius: 8px;
            white-space: pre-wrap;
            word-break: break-word;
            color: #374151;
            font-size: 13px;
            line-height: 1.6;
        }

        .map-container {
            width: 100%;
            height: 400px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .map-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>

    <a href="/admin?module=contacts" class="back-link">
        <i class="fas fa-arrow-left"></i> Retour aux contacts
    </a>

    <div class="lead-card lead-contact">
        <h2>📞 Coordonnées</h2>
        <?php if ($lead['first_name'] || $lead['last_name']): ?>
            <div class="contact-item">
                <span class="contact-icon">👤</span>
                <strong><?= htmlspecialchars($lead['first_name'] . ' ' . $lead['last_name']) ?></strong>
            </div>
        <?php endif; ?>
        <?php if ($lead['email']): ?>
            <div class="contact-item">
                <span class="contact-icon">✉️</span>
                <a href="mailto:<?= htmlspecialchars($lead['email']) ?>"><?= htmlspecialchars($lead['email']) ?></a>
            </div>
        <?php endif; ?>
        <?php if ($lead['phone']): ?>
            <div class="contact-item">
                <span class="contact-icon">📱</span>
                <a href="tel:<?= htmlspecialchars($lead['phone']) ?>"><?= htmlspecialchars($lead['phone']) ?></a>
            </div>
        <?php endif; ?>
    </div>

    <div class="lead-header">
        <div class="lead-card">
            <div class="lead-source"><?= $sourceLabel ?></div>

            <div class="lead-section">
                <div class="section-title">Statut</div>
                <span class="stage-badge"><?= htmlspecialchars($lead['stage'] ?? 'N/A') ?></span>
                <div style="margin-top: 12px; font-size: 13px;">
                    <strong>Priorité:</strong>
                    <span class="priority-<?= htmlspecialchars($lead['priority'] ?? 'normal') ?>">
                        <?= ucfirst($lead['priority'] ?? 'normal') ?>
                    </span>
                </div>
            </div>

            <div class="lead-section">
                <div class="section-title">Information</div>
                <?php if ($lead['first_name'] || $lead['last_name']): ?>
                    <div class="info-row">
                        <div class="info-label">Nom</div>
                        <div class="info-value"><?= htmlspecialchars($lead['first_name'] . ' ' . $lead['last_name']) ?></div>
                    </div>
                <?php endif; ?>
                <?php if ($lead['email']): ?>
                    <div class="info-row">
                        <div class="info-label">Email</div>
                        <div class="info-value"><a href="mailto:<?= htmlspecialchars($lead['email']) ?>"><?= htmlspecialchars($lead['email']) ?></a></div>
                    </div>
                <?php endif; ?>
                <?php if ($lead['phone']): ?>
                    <div class="info-row">
                        <div class="info-label">Téléphone</div>
                        <div class="info-value"><a href="tel:<?= htmlspecialchars($lead['phone']) ?>"><?= htmlspecialchars($lead['phone']) ?></a></div>
                    </div>
                <?php endif; ?>
                <div class="info-row">
                    <div class="info-label">Créé</div>
                    <div class="info-value"><?= date('d/m/Y H:i', strtotime($lead['created_at'])) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Modifié</div>
                    <div class="info-value"><?= date('d/m/Y H:i', strtotime($lead['updated_at'])) ?></div>
                </div>
            </div>
        </div>

        <div class="lead-card">
            <div class="lead-section">
                <div class="section-title">Propriété</div>
                <?php if ($lead['property_type']): ?>
                    <div class="info-row">
                        <div class="info-label">Type</div>
                        <div class="info-value"><?= htmlspecialchars($lead['property_type']) ?></div>
                    </div>
                <?php endif; ?>
                <?php if ($lead['property_address']): ?>
                    <div class="info-row">
                        <div class="info-label">Adresse</div>
                        <div class="info-value"><?= htmlspecialchars($lead['property_address']) ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="lead-section">
                <div class="section-title">Pipeline</div>
                <div class="info-row">
                    <div class="info-label">Pipeline</div>
                    <div class="info-value"><?= htmlspecialchars($lead['pipeline'] ?? 'N/A') ?></div>
                </div>
            </div>

            <div class="lead-section">
                <div class="section-title">Intent</div>
                <?php if ($lead['intent']): ?>
                    <div style="color: #374151; font-size: 14px; line-height: 1.6;">
                        <?= htmlspecialchars($lead['intent']) ?>
                    </div>
                <?php else: ?>
                    <div style="color: #9ca3af; font-size: 14px;">Aucun intent spécifié</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($lead['notes']): ?>
    <div class="lead-card">
        <div class="lead-section">
            <div class="section-title">Notes</div>
            <div class="notes-box"><?= htmlspecialchars($lead['notes']) ?></div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($lead['property_address']): ?>
    <div class="lead-card">
        <div class="lead-section">
            <div class="section-title">📍 Localisation</div>
            <div class="map-container">
                <?php
                $googleMapsKey = setting('api_google_maps', $_ENV['GOOGLE_MAPS_API_KEY'] ?? '');
                if ($googleMapsKey):
                ?>
                <iframe
                    src="https://www.google.com/maps/embed/v1/place?key=<?= htmlspecialchars($googleMapsKey) ?>&q=<?= urlencode($lead['property_address']) ?>&zoom=15"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
                <?php else: ?>
                <div style="padding: 40px; text-align: center; background: #f9fafb; border-radius: 8px; color: #6b7280;">
                    <p>🗺️ Clé Google Maps non configurée</p>
                    <p style="font-size: 12px;">Configurez la clé dans <a href="/admin?module=api-keys" style="color: #3b82f6;">Gestion des clés API</a></p>
                </div>
                <?php endif; ?>
            </div>
            <p style="margin-top: 12px; font-size: 13px; color: #666;">
                <strong>Adresse:</strong> <?= htmlspecialchars($lead['property_address']) ?>
            </p>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($metadata)): ?>
    <div class="lead-card">
        <div class="lead-section">
            <div class="section-title">Données additionnelles</div>
            <table class="metadata-table">
                <thead>
                    <tr>
                        <th>Clé</th>
                        <th>Valeur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($metadata as $key => $value): ?>
                    <tr>
                        <td style="font-weight: 600;"><?= htmlspecialchars($key) ?></td>
                        <td>
                            <?php if (is_array($value)): ?>
                                <?= htmlspecialchars(json_encode($value, JSON_UNESCAPED_UNICODE)) ?>
                            <?php else: ?>
                                <?= htmlspecialchars((string)$value) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php
}

<?php

declare(strict_types=1);

$sequenceId = (int)($_GET['id'] ?? 0);

if ($sequenceId <= 0) {
    $pageTitle = 'Séquence non trouvée';
    function renderContent(): void {
        ?>
        <div style="padding: 40px; text-align: center; color: #9ca3af;">
            <h2 style="color: #374151; margin-bottom: 16px;">Séquence non trouvée</h2>
            <p>La séquence que vous recherchez n'existe pas.</p>
            <a href="/admin?module=email-sequences" style="display: inline-block; margin-top: 16px; padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 8px;">Retour aux séquences</a>
        </div>
        <?php
    }
    return;
}

$sequence = EmailSequenceService::getSequence($sequenceId);

if (!$sequence) {
    $pageTitle = 'Séquence non trouvée';
    function renderContent(): void {
        ?>
        <div style="padding: 40px; text-align: center; color: #9ca3af;">
            <h2 style="color: #374151; margin-bottom: 16px;">Séquence non trouvée</h2>
            <p>La séquence que vous recherchez n'existe pas.</p>
            <a href="/admin?module=email-sequences" style="display: inline-block; margin-top: 16px; padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 8px;">Retour aux séquences</a>
        </div>
        <?php
    }
    return;
}

$emails = EmailSequenceService::getSequenceEmails($sequenceId);
$stats = EmailSequenceService::getSequenceStats($sequenceId);

$pageTitle = 'Éditer: ' . htmlspecialchars($sequence['name']);

function renderContent(): void {
    global $sequence, $emails, $stats, $sequenceId;
    ?>
    <style>
        .edit-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .edit-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .status-inactive {
            background: #fee2e2;
            color: #dc2626;
        }

        .status-draft {
            background: #f3f4f6;
            color: #6b7280;
        }

        .edit-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }

        .edit-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
        }

        .card-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .card-value {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 12px;
        }

        .stat-box {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
        }

        .stat-number {
            font-size: 20px;
            font-weight: 700;
            color: #c9a84c;
        }

        .stat-label {
            font-size: 11px;
            color: #6b7280;
            margin-top: 4px;
            text-transform: uppercase;
        }

        .emails-section {
            margin-top: 32px;
        }

        .emails-title {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 16px;
        }

        .email-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .email-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 16px;
            margin-bottom: 12px;
        }

        .email-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: #c9a84c;
            color: #fff;
            border-radius: 50%;
            font-weight: 700;
            flex-shrink: 0;
        }

        .email-meta {
            flex: 1;
        }

        .email-subject {
            font-weight: 600;
            font-size: 14px;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .email-delay {
            font-size: 12px;
            color: #6b7280;
        }

        .email-actions {
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 12px;
            transition: all .2s;
        }

        .btn-primary {
            background: #c9a84c;
            color: #10253c;
        }

        .btn-primary:hover {
            background: #b8962d;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 11px;
        }

        .actions-bar {
            display: flex;
            gap: 12px;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefeff;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 90%;
            max-width: 600px;
            border-radius: 12px;
        }

        .modal-close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .modal-close:hover {
            color: black;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #0f172a;
            font-size: 13px;
        }

        .form-input,
        .form-textarea,
        .modal select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-family: inherit;
            font-size: 13px;
        }

        .modal select {
            background-color: white;
            cursor: pointer;
        }

        .modal select:focus {
            outline: none;
            border-color: #c9a84c;
            box-shadow: 0 0 0 3px rgba(201, 168, 76, 0.1);
        }

        .form-textarea {
            min-height: 150px;
            resize: vertical;
        }
    </style>

    <div class="edit-header">
        <div>
            <h1><?= htmlspecialchars($sequence['name']) ?></h1>
            <span class="status-badge status-<?= htmlspecialchars($sequence['status']) ?>">
                <?= ucfirst($sequence['status']) ?>
            </span>
        </div>
        <a href="/admin?module=email-sequences" class="btn btn-secondary">← Retour</a>
    </div>

    <div class="edit-grid">
        <div class="edit-card">
            <div class="card-label">Objectif</div>
            <div class="card-value"><?= htmlspecialchars($sequence['objective']) ?></div>
        </div>
        <div class="edit-card">
            <div class="card-label">Persona</div>
            <div class="card-value"><?= htmlspecialchars($sequence['persona']) ?></div>
        </div>
        <div class="edit-card">
            <div class="card-label">Ville</div>
            <div class="card-value"><?= htmlspecialchars($sequence['city']) ?></div>
        </div>
        <div class="edit-card">
            <div class="card-label">Déclenchement</div>
            <div class="card-value">
                <?= ucfirst($sequence['trigger_type']) ?>
                <?php if ($sequence['form_trigger']): ?>
                    <br><small style="color: #6b7280; font-weight: normal;">Form: <?= htmlspecialchars($sequence['form_trigger']) ?></small>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="edit-card" style="grid-column: 1/-1; background: #fffbf0; border-color: #fed7aa;">
        <div class="card-label">🔗 Lien de destination (CTA)</div>
        <div style="margin-top: 12px;">
            <?php if ($sequence['destination_type']): ?>
                <div style="background: white; padding: 12px; border-radius: 6px; margin-bottom: 12px;">
                    <div style="font-weight: 600; color: #0f172a;">Type:</div>
                    <div style="color: #6b7280; margin-bottom: 8px;">
                        <?php
                            $types = ['article' => '📰 Article', 'guide' => '📥 Guide', 'rdv' => '📅 RDV'];
                            echo htmlspecialchars($types[$sequence['destination_type']] ?? $sequence['destination_type']);
                        ?>
                    </div>
                    <div style="font-weight: 600; color: #0f172a;">URL:</div>
                    <div style="color: #6b7280; font-family: monospace; margin-bottom: 8px; word-break: break-all;"><?= htmlspecialchars($sequence['destination_url']) ?></div>
                    <div style="font-weight: 600; color: #0f172a;">Texte du bouton:</div>
                    <div style="color: #6b7280; margin-bottom: 8px;"><?= htmlspecialchars($sequence['destination_label']) ?></div>
                    <?php if ($sequence['destination_contact_type']): ?>
                        <div style="font-weight: 600; color: #0f172a;">Limité à:</div>
                        <div style="color: #6b7280;"><?= htmlspecialchars($sequence['destination_contact_type']) ?></div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div style="color: #9ca3af; font-size: 13px;">Aucun lien de destination configuré</div>
            <?php endif; ?>
            <button onclick="openDestinationModal()" class="btn btn-primary btn-sm">
                <?= $sequence['destination_type'] ? '✏️ Modifier' : '➕ Ajouter' ?> destination
            </button>
        </div>
    </div>

    <div style="background: #f9fafb; padding: 16px; border-radius: 12px; margin-bottom: 32px;">
        <div class="card-label">Statistiques</div>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-number"><?= (int)$stats['total_subscribers'] ?></div>
                <div class="stat-label">Abonnés</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= (int)$stats['active_subscribers'] ?></div>
                <div class="stat-label">Actifs</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= (int)$stats['completed_subscribers'] ?></div>
                <div class="stat-label">Complétés</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= (int)$stats['total_sent'] ?></div>
                <div class="stat-label">Envoyés</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= (int)$stats['total_opened'] ?></div>
                <div class="stat-label">Ouverts</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= (int)$stats['total_clicked'] ?></div>
                <div class="stat-label">Cliqués</div>
            </div>
        </div>
    </div>

    <div class="emails-section">
        <h2 class="emails-title">📧 Les 5 emails de la séquence</h2>

        <?php foreach ($emails as $email): ?>
        <div class="email-item">
            <div class="email-header">
                <div style="display: flex; gap: 12px; flex: 1;">
                    <div class="email-number"><?= $email['email_number'] ?></div>
                    <div class="email-meta">
                        <div class="email-subject"><?= htmlspecialchars($email['subject']) ?></div>
                        <div class="email-delay">
                            <?php if ($email['delay_days'] == 0): ?>
                                Envoyé immédiatement
                            <?php else: ?>
                                Envoyé après <?= (int)$email['delay_days'] ?> jours
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="email-actions">
                    <button onclick="openEditEmailModal(<?= (int)$email['id'] ?>, <?= (int)$email['email_number'] ?>)" class="btn btn-primary btn-sm">
                        ✏️ Éditer
                    </button>
                </div>
            </div>
            <div style="margin-top: 12px; padding: 12px; background: #f9fafb; border-radius: 6px; font-size: 12px; color: #6b7280; line-height: 1.6;">
                <?php echo nl2br(htmlspecialchars(substr($email['body_html'], 0, 200))); ?>...
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="actions-bar">
        <?php if ($sequence['status'] === 'draft'): ?>
            <button onclick="activateSequence(<?= (int)$sequenceId ?>)" class="btn btn-primary">
                ✓ Activer cette séquence
            </button>
        <?php else: ?>
            <button onclick="deactivateSequence(<?= (int)$sequenceId ?>)" class="btn btn-secondary">
                Désactiver
            </button>
        <?php endif; ?>
        <a href="/admin?module=email-sequences" class="btn btn-secondary">← Retour aux séquences</a>
    </div>

    <div id="editEmailModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeEditEmailModal()">&times;</span>
            <h2 style="margin-top: 0;">Éditer l'email #<span id="modalEmailNumber">1</span></h2>

            <form id="editEmailForm">
                <input type="hidden" name="email_id" id="emailId">
                <input type="hidden" name="sequence_id" value="<?= (int)$sequenceId ?>">

                <div class="form-group">
                    <label class="form-label">Sujet du mail</label>
                    <input type="text" name="subject" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Contenu du mail</label>
                    <textarea name="body_html" class="form-textarea" required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Prévisualisation</label>
                    <input type="text" name="preview" class="form-input" placeholder="Optionnel">
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px;">
                    <button type="button" onclick="closeEditEmailModal()" class="btn btn-secondary">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Sauvegarder
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="destinationModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeDestinationModal()">&times;</span>
            <h2 style="margin-top: 0;">Configurer le lien de destination</h2>
            <p style="color: #6b7280; font-size: 13px; margin-bottom: 16px;">Définissez où les prospects seront dirigés quand ils cliquent sur le CTA dans vos emails</p>

            <form id="destinationForm">
                <input type="hidden" name="sequence_id" value="<?= (int)$sequenceId ?>">

                <div class="form-group">
                    <label class="form-label">Type de destination</label>
                    <select name="destination_type" id="destType" class="form-input" onchange="updateDestinationPlaceholder()">
                        <option value="">-- Aucune destination --</option>
                        <option value="article">📰 Article</option>
                        <option value="guide">📥 Guide à télécharger</option>
                        <option value="rdv">📅 Prise de RDV</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">URL de destination</label>
                    <input type="url" name="destination_url" id="destUrl" class="form-input"
                           placeholder="https://exemple.com/article">
                    <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                        L'URL complète de l'article, du guide, ou de la page de RDV
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Texte du bouton CTA</label>
                    <input type="text" name="destination_label" id="destLabel" class="form-input"
                           placeholder="Lire l'article">
                    <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                        Le texte affiché sur le bouton d'appel à l'action
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Limiter à un type de contact (optionnel)</label>
                    <select name="destination_contact_type" id="destContactType" class="form-input">
                        <option value="">-- Pour tous les contacts --</option>
                        <option value="prospect-jamais-contacte">🔴 Prospect jamais contacté</option>
                        <option value="prospect-relance">🟡 Prospect en relance</option>
                        <option value="client-existant">🟢 Client existant</option>
                    </select>
                    <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                        Optionnel: afficher ce lien seulement pour un type de prospect spécifique
                    </div>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px;">
                    <button type="button" onclick="closeDestinationModal()" class="btn btn-secondary">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Sauvegarder la destination
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openEditEmailModal(emailId, emailNumber) {
        const modal = document.getElementById('editEmailModal');
        document.getElementById('emailId').value = emailId;
        document.getElementById('modalEmailNumber').textContent = emailNumber;

        // Récupérer les données de l'email depuis le serveur
        fetch('/admin?module=email-sequences&action=get-email&id=' + emailId)
            .then(r => r.json())
            .then(data => {
                document.querySelector('[name="subject"]').value = data.subject || '';
                document.querySelector('[name="body_html"]').value = data.body_html || '';
                document.querySelector('[name="preview"]').value = data.preview_text || '';
            });

        modal.style.display = 'block';
    }

    function closeEditEmailModal() {
        document.getElementById('editEmailModal').style.display = 'none';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('editEmailModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    document.getElementById('editEmailForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('/admin?module=email-sequences&action=update-email', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur: ' + (data.error || 'Erreur inconnue'));
            }
        });
    });

    function activateSequence(sequenceId) {
        if (confirm('Activer cette séquence? Les prospects qui remplissent les formulaires associés recevront ces emails.')) {
            fetch('/admin?module=email-sequences&action=activate&id=' + sequenceId, {
                method: 'POST'
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
    }

    function deactivateSequence(sequenceId) {
        if (confirm('Désactiver cette séquence?')) {
            fetch('/admin?module=email-sequences&action=deactivate&id=' + sequenceId, {
                method: 'POST'
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
    }

    function openDestinationModal() {
        const modal = document.getElementById('destinationModal');
        document.getElementById('destType').value = '<?= htmlspecialchars($sequence['destination_type'] ?? '') ?>';
        document.getElementById('destUrl').value = '<?= htmlspecialchars($sequence['destination_url'] ?? '') ?>';
        document.getElementById('destLabel').value = '<?= htmlspecialchars($sequence['destination_label'] ?? '') ?>';
        document.getElementById('destContactType').value = '<?= htmlspecialchars($sequence['destination_contact_type'] ?? '') ?>';
        modal.style.display = 'block';
    }

    function closeDestinationModal() {
        document.getElementById('destinationModal').style.display = 'none';
    }

    function updateDestinationPlaceholder() {
        const type = document.getElementById('destType').value;
        const placeholders = {
            'article': 'https://exemple.com/article',
            'guide': 'https://exemple.com/guide.pdf',
            'rdv': 'https://calendly.com/votre-page'
        };
        document.getElementById('destUrl').placeholder = placeholders[type] || 'https://exemple.com';
    }

    window.onclick = function(event) {
        const emailModal = document.getElementById('editEmailModal');
        const destModal = document.getElementById('destinationModal');
        if (event.target == emailModal) {
            emailModal.style.display = 'none';
        }
        if (event.target == destModal) {
            destModal.style.display = 'none';
        }
    }

    document.getElementById('destinationForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('/admin?module=email-sequences&action=update-destination', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('✓ Destination sauvegardée!');
                location.reload();
            } else {
                alert('❌ Erreur: ' + (data.error || 'Erreur inconnue'));
            }
        })
        .catch(err => alert('Erreur réseau: ' + err));
    });
    </script>
    <?php
}

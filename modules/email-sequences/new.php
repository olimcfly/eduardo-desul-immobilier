<?php

declare(strict_types=1);

$action = preg_replace('/[^a-z0-9_-]/i', '', (string)($_GET['action'] ?? 'index'));

if ($action === 'create') {
    $name = sanitizeString($_POST['name'] ?? '');
    $objective = sanitizeString($_POST['objective'] ?? '');
    $persona = sanitizeString($_POST['persona'] ?? '');
    $city = sanitizeString($_POST['city'] ?? '');
    $description = sanitizeString($_POST['description'] ?? '');
    $triggerType = sanitizeString($_POST['trigger_type'] ?? 'manual');
    $formTrigger = sanitizeString($_POST['form_trigger'] ?? '');
    $destinationType = sanitizeString($_POST['destination_type'] ?? '');
    $destinationUrl = sanitizeString($_POST['destination_url'] ?? '');
    $destinationLabel = sanitizeString($_POST['destination_label'] ?? '');
    $destinationContactType = sanitizeString($_POST['destination_contact_type'] ?? '');

    if (!$name || !$objective || !$persona || !$city) {
        $_SESSION['error'] = 'Tous les champs obligatoires doivent être remplis';
        header('Location: /admin?module=email-sequences&action=new');
        exit;
    }

    try {
        $sequenceId = EmailSequenceService::createSequence(
            $name,
            $objective,
            $persona,
            $city,
            $description,
            $triggerType,
            $formTrigger ?: null,
            $destinationType ?: null,
            $destinationUrl ?: null,
            $destinationLabel ?: null,
            $destinationContactType ?: null
        );

        $_SESSION['success'] = 'Séquence créée avec succès! Les 5 emails ont été générés automatiquement.';
        header('Location: /admin?module=email-sequences&action=edit&id=' . $sequenceId);
        exit;
    } catch (Throwable $e) {
        error_log('Error creating sequence: ' . $e->getMessage());
        $_SESSION['error'] = 'Erreur lors de la création de la séquence';
        header('Location: /admin?module=email-sequences&action=new');
        exit;
    }
}

$pageTitle = 'Nouvelle séquence email';

function renderContent(): void {
    $objectives = [
        'Vendre rapidement' => 'Vendre rapidement',
        'Louer longue durée' => 'Louer longue durée',
        'Louer courte durée' => 'Louer courte durée',
        'Investir' => 'Investir',
        'Acquérir' => 'Acquérir',
        'Rénover' => 'Rénover',
    ];

    $personas = [
        'Propriétaire occupant' => 'Propriétaire occupant',
        'Investisseur immobilier' => 'Investisseur immobilier',
        'Primo-accédant' => 'Primo-accédant',
        'Retraité' => 'Retraité',
        'Famille' => 'Famille',
        'Professionnel' => 'Professionnel',
    ];

    $formTriggers = [
        'none' => 'Aucun (Manuel)',
        'estimation-gratuite' => 'Estimation gratuite',
        'contact' => 'Formulaire contact',
        'prendre-rendez-vous' => 'Demande RDV',
        'guide-offert' => 'Téléchargement guide',
    ];

    $cities = [];
    try {
        $stmt = db()->prepare('SELECT DISTINCT city FROM email_sequences WHERE city IS NOT NULL AND city != "" ORDER BY city');
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $row) {
            if ($row['city']) {
                $cities[$row['city']] = $row['city'];
            }
        }
    } catch (Throwable $e) {
        error_log('Error fetching cities: ' . $e->getMessage());
    }
    ?>
    <style>
        .new-sequence-form {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }

        .form-label .required {
            color: #dc2626;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #c9a84c;
            box-shadow: 0 0 0 3px rgba(201, 168, 76, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-hint {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .form-section {
            background: #f9fafb;
            padding: 16px;
            border-radius: 8px;
            margin: 24px 0;
        }

        .form-section h3 {
            margin: 0 0 16px;
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-grid .form-group {
            margin-bottom: 0;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
            font-size: 14px;
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

        .trigger-options {
            display: grid;
            gap: 12px;
            margin-top: 12px;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all .2s;
        }

        .radio-option:hover {
            background: #f9fafb;
        }

        .radio-option input[type="radio"] {
            cursor: pointer;
        }

        .radio-option input[type="radio"]:checked + label {
            font-weight: 600;
        }

        #formTriggerGroup {
            margin-top: 12px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 8px;
            display: none;
        }
    </style>

    <div class="new-sequence-form">
        <h1 style="margin: 0 0 24px; font-size: 24px; font-weight: 700; color: #0f172a;">
            Créer une nouvelle séquence email
        </h1>

        <form method="POST" action="/admin?module=email-sequences&action=create">
            <div class="form-group">
                <label class="form-label">
                    Nom de la séquence <span class="required">*</span>
                </label>
                <input type="text" name="name" class="form-input" required
                       placeholder="Ex: Séquence vendeurs rapides Lyon">
                <div class="form-hint">Le nom interne de la séquence</div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        Objectif <span class="required">*</span>
                    </label>
                    <select name="objective" class="form-select" required>
                        <option value="">Sélectionner un objectif</option>
                        <?php foreach ($objectives as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>">
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Persona <span class="required">*</span>
                    </label>
                    <select name="persona" class="form-select" required>
                        <option value="">Sélectionner une persona</option>
                        <?php foreach ($personas as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>">
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">
                    Ville/Région <span class="required">*</span>
                </label>
                <input type="text" name="city" class="form-input" required
                       placeholder="Ex: Lyon, Rhône, Région Rhône-Alpes"
                       list="citySuggestions">
                <datalist id="citySuggestions">
                    <?php foreach ($cities as $city): ?>
                        <option value="<?= htmlspecialchars($city) ?>">
                    <?php endforeach; ?>
                </datalist>
                <div class="form-hint">La localisation principale de la séquence</div>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-textarea"
                          placeholder="Description optionnelle de la séquence..."></textarea>
            </div>

            <div class="form-section">
                <h3>🔔 Déclenchement de la séquence</h3>

                <div class="trigger-options">
                    <label class="radio-option">
                        <input type="radio" name="trigger_type" value="manual" checked onchange="toggleFormTrigger()">
                        <label style="margin: 0; cursor: pointer;">
                            <strong>Activation manuelle</strong><br>
                            <small style="color: #6b7280;">Vous activez manuellement la séquence pour chaque prospect</small>
                        </label>
                    </label>

                    <label class="radio-option">
                        <input type="radio" name="trigger_type" value="automatic" onchange="toggleFormTrigger()">
                        <label style="margin: 0; cursor: pointer;">
                            <strong>Déclenchement automatique</strong><br>
                            <small style="color: #6b7280;">La séquence se lance quand le prospect remplit un formulaire</small>
                        </label>
                    </label>
                </div>

                <div id="formTriggerGroup">
                    <label class="form-label" style="margin-top: 0;">
                        Déclencher quand ce formulaire est rempli <span class="required">*</span>
                    </label>
                    <select name="form_trigger" class="form-select">
                        <option value="">Sélectionner un formulaire</option>
                        <?php foreach ($formTriggers as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>">
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-section">
                <h3>🔗 Lien de destination (CTA)</h3>
                <p style="color: #6b7280; font-size: 13px; margin-bottom: 16px;">Configurez où les prospects doivent être dirigés quand ils cliquent sur le lien dans les emails</p>

                <div class="form-group">
                    <label class="form-label">Type de destination</label>
                    <select name="destination_type" class="form-select" onchange="updateDestinationLabel()">
                        <option value="">-- Aucune destination --</option>
                        <option value="article">📰 Article</option>
                        <option value="guide">📥 Guide à télécharger</option>
                        <option value="rdv">📅 Prise de RDV</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">URL de destination</label>
                    <input type="url" name="destination_url" class="form-input"
                           placeholder="https://exemple.com/article ou /uploads/guide.pdf">
                    <div class="form-hint">L'URL de l'article, du guide, ou de la page de RDV</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Texte du bouton CTA</label>
                    <input type="text" name="destination_label" class="form-input" id="destinationLabel"
                           placeholder="Ex: Lire l'article, Télécharger, Prendre RDV">
                    <div class="form-hint">Le texte affiché sur le bouton d'appel à l'action</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Limiter à un type de contact (optionnel)</label>
                    <select name="destination_contact_type" class="form-select">
                        <option value="">-- Pour tous les contacts --</option>
                        <option value="prospect-jamais-contacte">🔴 Prospect jamais contacté</option>
                        <option value="prospect-relance">🟡 Prospect en relance</option>
                        <option value="client-existant">🟢 Client existant</option>
                    </select>
                    <div class="form-hint">Optionnel: afficher ce lien seulement pour un type de prospect spécifique</div>
                </div>
            </div>

            <div style="background: #f0fdf4; border-left: 4px solid #22c55e; padding: 12px; border-radius: 6px; margin-bottom: 24px;">
                <strong style="color: #166534;">✓ Les 5 emails seront générés automatiquement</strong><br>
                <small style="color: #15803d;">Basé sur: l'objectif, la persona et la ville</small>
            </div>

            <div class="form-actions">
                <a href="/admin?module=email-sequences" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">Créer la séquence</button>
            </div>
        </form>
    </div>

    <script>
    function toggleFormTrigger() {
        const triggerType = document.querySelector('input[name="trigger_type"]:checked').value;
        const formTriggerGroup = document.getElementById('formTriggerGroup');

        if (triggerType === 'automatic') {
            formTriggerGroup.style.display = 'block';
            document.querySelector('[name="form_trigger"]').required = true;
        } else {
            formTriggerGroup.style.display = 'none';
            document.querySelector('[name="form_trigger"]').required = false;
        }
    }

    function updateDestinationLabel() {
        const type = document.querySelector('[name="destination_type"]').value;
        const labelInput = document.getElementById('destinationLabel');
        const suggestions = {
            'article': 'Lire l\'article',
            'guide': 'Télécharger le guide',
            'rdv': 'Prendre RDV'
        };
        if (type && suggestions[type]) {
            labelInput.placeholder = suggestions[type];
            if (!labelInput.value) {
                labelInput.value = suggestions[type];
            }
        }
    }
    </script>
    <?php
}

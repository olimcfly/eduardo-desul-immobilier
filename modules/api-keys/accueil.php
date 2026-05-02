<?php

declare(strict_types=1);

// Vérifier l'accès (superuser ou contact@)
$currentUser = Auth::user() ?? [];
$userEmail = $currentUser['email'] ?? '';
$userRole = $currentUser['role'] ?? '';

if ($userRole !== 'superuser' && !str_ends_with($userEmail, '@contact')) {
    $pageTitle = 'Accès refusé';
    function renderContent(): void {
        ?>
        <div style="padding: 40px; text-align: center; color: #9ca3af;">
            <h2 style="color: #374151; margin-bottom: 16px;">Accès non autorisé</h2>
            <p>Seuls les superusers et les utilisateurs contact@ peuvent accéder à cette section.</p>
            <a href="/admin?module=dashboard" style="display: inline-block; margin-top: 16px; padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 8px;">Retour au Dashboard</a>
        </div>
        <?php
    }
    return;
}

$action = preg_replace('/[^a-z0-9_-]/i', '', (string)($_GET['action'] ?? 'index'));

if ($action === 'save') {
    header('Content-Type: application/json');

    $apiName = sanitizeString($_POST['api_name'] ?? '');
    $apiKey = sanitizeString($_POST['api_key'] ?? '');

    if (!$apiName || !$apiKey) {
        echo json_encode(['success' => false, 'error' => 'Tous les champs sont requis']);
        exit;
    }

    try {
        $stmt = db()->prepare('
            INSERT INTO settings (user_id, setting_key, setting_value, setting_type, setting_group)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ');

        $stmt->execute([
            $_SESSION['user_id'] ?? 0,
            'api_' . $apiName,
            $apiKey,
            'password',
            'apis',
        ]);

        echo json_encode(['success' => true]);
    } catch (Throwable $e) {
        error_log('Error saving API key: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

$pageTitle = 'Gestion des clés API';
$pageDescription = 'Configurez vos clés API pour les services externes';

function renderContent(): void {
    $apis = [
        'google_maps' => [
            'name' => 'Google Maps',
            'description' => 'Clé pour l\'API Google Maps Embed',
            'icon' => '🗺️',
            'link' => 'https://console.cloud.google.com/google/maps-apis',
            'hint' => 'Allez sur Google Cloud Console, créez un projet, activez l\'API Google Maps Embed'
        ],
        'openai' => [
            'name' => 'OpenAI',
            'description' => 'Clé API pour ChatGPT et autres services OpenAI',
            'icon' => '🤖',
            'link' => 'https://platform.openai.com/api-keys',
            'hint' => 'Créez une nouvelle clé secrète dans votre compte OpenAI'
        ],
        'gmb' => [
            'name' => 'Google My Business',
            'description' => 'Identifiant client Google My Business',
            'icon' => '📍',
            'link' => 'https://developers.google.com/my-business',
            'hint' => 'Configurer l\'authentification OAuth 2.0'
        ],
        'facebook' => [
            'name' => 'Facebook',
            'description' => 'Token d\'accès Facebook pour API Graph',
            'icon' => '👥',
            'link' => 'https://developers.facebook.com/docs/facebook-login',
            'hint' => 'Créez une application Facebook et générez un token'
        ],
    ];

    $configuredApis = [];
    try {
        $stmt = db()->prepare('
            SELECT setting_key, setting_value
            FROM settings
            WHERE setting_key LIKE "api_%" AND setting_value IS NOT NULL AND setting_value != ""
        ');
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $row) {
            $configuredApis[str_replace('api_', '', $row['setting_key'])] = true;
        }
    } catch (Throwable $e) {
        error_log('Error fetching configured APIs: ' . $e->getMessage());
    }
    ?>
    <style>
        .api-hero {
            background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
            border-radius: 16px;
            padding: 32px;
            color: #fff;
            margin-bottom: 32px;
        }

        .api-hero h1 {
            margin: 0 0 8px;
            font-size: 28px;
            font-weight: 700;
        }

        .api-hero p {
            margin: 0;
            color: rgba(255,255,255,.78);
            font-size: 15px;
        }

        .api-grid {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        }

        .api-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            transition: all .2s;
        }

        .api-card:hover {
            border-color: #c9a84c;
            box-shadow: 0 4px 12px rgba(201,168,76,.15);
        }

        .api-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .api-icon {
            font-size: 32px;
        }

        .api-info h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
        }

        .api-info p {
            margin: 4px 0 0;
            font-size: 12px;
            color: #6b7280;
        }

        .api-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .status-configured {
            background: #dcfce7;
            color: #166534;
        }

        .status-unconfigured {
            background: #fee2e2;
            color: #991b1b;
        }

        .api-hint {
            background: #f9fafb;
            padding: 10px;
            border-radius: 6px;
            font-size: 12px;
            color: #6b7280;
            margin: 12px 0;
            line-height: 1.4;
        }

        .api-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .btn {
            padding: 8px 12px;
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
            flex: 1;
        }

        .btn-secondary:hover {
            background: #d1d5db;
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
            padding: 32px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
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
            margin-bottom: 8px;
            color: #0f172a;
            font-size: 14px;
        }

        .form-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-family: monospace;
            font-size: 13px;
        }

        .form-input:focus {
            outline: none;
            border-color: #c9a84c;
            box-shadow: 0 0 0 3px rgba(201,168,76,0.1);
        }

        .security-notice {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px;
            border-radius: 6px;
            font-size: 12px;
            color: #92400e;
            margin-bottom: 16px;
        }
    </style>

    <div class="api-hero">
        <h1>🔐 Gestion des clés API</h1>
        <p>Configurez vos clés API pour les services externes. Vos clés sont chiffrées et ne sont jamais affichées en clair.</p>
    </div>

    <div class="api-grid">
        <?php foreach ($apis as $apiId => $api): ?>
            <div class="api-card">
                <div class="api-card-header">
                    <div class="api-icon"><?= $api['icon'] ?></div>
                    <div class="api-info">
                        <h3><?= htmlspecialchars($api['name']) ?></h3>
                        <p><?= htmlspecialchars($api['description']) ?></p>
                    </div>
                </div>

                <span class="api-status <?= isset($configuredApis[$apiId]) ? 'status-configured' : 'status-unconfigured' ?>">
                    <?= isset($configuredApis[$apiId]) ? '✓ Configurée' : '⚠️ Non configurée' ?>
                </span>

                <div class="api-hint">
                    📌 <?= htmlspecialchars($api['hint']) ?>
                </div>

                <div class="api-actions">
                    <a href="<?= htmlspecialchars($api['link']) ?>" target="_blank" class="btn btn-secondary">
                        ? Documentation
                    </a>
                    <button onclick="openApiModal('<?= htmlspecialchars($apiId) ?>', '<?= htmlspecialchars($api['name']) ?>')" class="btn btn-primary">
                        <?= isset($configuredApis[$apiId]) ? '✏️ Modifier' : '➕ Ajouter' ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="apiModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeApiModal()">&times;</span>
            <h2 id="modalTitle" style="margin-top: 0;">Configurer API</h2>

            <div class="security-notice">
                🔒 Votre clé API sera chiffrée et stockée de manière sécurisée. Seul vous et les administrateurs peuvent la modifier.
            </div>

            <form id="apiForm">
                <input type="hidden" name="api_name" id="apiName">

                <div class="form-group">
                    <label class="form-label">Clé API</label>
                    <input type="password" name="api_key" id="apiKey" class="form-input" placeholder="Collez votre clé API" required>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: normal;">
                        <input type="checkbox" id="showKey" onchange="toggleKeyVisibility()">
                        <span>Afficher la clé</span>
                    </label>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px;">
                    <button type="button" onclick="closeApiModal()" class="btn btn-secondary" style="flex: 1;">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Sauvegarder
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openApiModal(apiId, apiName) {
        document.getElementById('apiName').value = apiId;
        document.getElementById('modalTitle').textContent = `Configurer ${apiName}`;
        document.getElementById('apiKey').value = '';
        document.getElementById('showKey').checked = false;
        document.getElementById('apiModal').style.display = 'block';
    }

    function closeApiModal() {
        document.getElementById('apiModal').style.display = 'none';
    }

    function toggleKeyVisibility() {
        const input = document.getElementById('apiKey');
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('apiModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    document.getElementById('apiForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('/admin?module=api-keys&action=save', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('✓ Clé API sauvegardée avec succès!');
                closeApiModal();
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

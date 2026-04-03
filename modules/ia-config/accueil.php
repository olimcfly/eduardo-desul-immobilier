<?php
$pageTitle = 'Configuration IA';
$pageDescription = 'Configurez votre fournisseur IA et surveillez vos coûts.';

require_once __DIR__ . '/../../core/bootstrap.php';

function iaConfigEnsureTable(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS ia_configurations (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            provider VARCHAR(32) NOT NULL,
            api_key TEXT NOT NULL,
            model VARCHAR(120) NOT NULL,
            tokens_used BIGINT UNSIGNED NOT NULL DEFAULT 0,
            estimated_cost DECIMAL(12,6) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_ia_user_active (user_id, is_active, updated_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}

function iaConfigModels(): array
{
    return [
        'openai' => 'gpt-4o-mini',
        'anthropic' => 'claude-3-5-haiku-latest',
        'mistral' => 'mistral-small-latest',
    ];
}

function iaConfigPing(string $provider, string $apiKey, string $model): array
{
    $url = '';
    $headers = ['Content-Type: application/json'];
    $payload = [];

    switch ($provider) {
        case 'openai':
            $url = 'https://api.openai.com/v1/chat/completions';
            $headers[] = 'Authorization: Bearer ' . $apiKey;
            $payload = [
                'model' => $model,
                'messages' => [['role' => 'user', 'content' => 'Réponds: ok']],
                'max_tokens' => 5,
            ];
            break;

        case 'anthropic':
            $url = 'https://api.anthropic.com/v1/messages';
            $headers[] = 'x-api-key: ' . $apiKey;
            $headers[] = 'anthropic-version: 2023-06-01';
            $payload = [
                'model' => $model,
                'max_tokens' => 5,
                'messages' => [['role' => 'user', 'content' => 'Réponds: ok']],
            ];
            break;

        case 'mistral':
            $url = 'https://api.mistral.ai/v1/chat/completions';
            $headers[] = 'Authorization: Bearer ' . $apiKey;
            $payload = [
                'model' => $model,
                'messages' => [['role' => 'user', 'content' => 'Réponds: ok']],
                'max_tokens' => 5,
            ];
            break;

        default:
            return ['ok' => false, 'message' => 'Fournisseur IA non supporté.'];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 12,
    ]);

    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false || $error !== '') {
        return ['ok' => false, 'message' => 'Erreur réseau : ' . ($error ?: 'réponse vide')];
    }

    if ($httpCode >= 200 && $httpCode < 300) {
        return ['ok' => true, 'message' => 'Connexion API validée.'];
    }

    return ['ok' => false, 'message' => 'Ping API échoué (HTTP ' . $httpCode . ').'];
}

function renderContent(): void
{
    $pdo = db();
    iaConfigEnsureTable($pdo);

    $userId = (int)($_SESSION['user_id'] ?? 0);
    $models = iaConfigModels();
    $providers = ['openai' => 'OpenAI', 'anthropic' => 'Anthropic', 'mistral' => 'Mistral'];

    $flash = ['type' => '', 'message' => ''];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userId > 0) {
        $provider = strtolower(trim((string)($_POST['provider'] ?? 'openai')));
        $apiKey = trim((string)($_POST['api_key'] ?? ''));
        $model = trim((string)($_POST['model'] ?? ($models[$provider] ?? '')));
        $action = trim((string)($_POST['action_type'] ?? 'save'));

        if (!isset($providers[$provider])) {
            $flash = ['type' => 'error', 'message' => 'Réseau IA invalide.'];
        } elseif ($apiKey === '' || $model === '') {
            $flash = ['type' => 'error', 'message' => 'La clé API et le modèle sont obligatoires.'];
        } else {
            if ($action === 'test') {
                $test = iaConfigPing($provider, $apiKey, $model);
                $flash = ['type' => $test['ok'] ? 'ok' : 'error', 'message' => $test['message']];
            }

            $stmt = $pdo->prepare('UPDATE ia_configurations SET is_active = 0 WHERE user_id = :user_id');
            $stmt->execute(['user_id' => $userId]);

            $stmt = $pdo->prepare(
                'INSERT INTO ia_configurations (user_id, provider, api_key, model, is_active)
                 VALUES (:user_id, :provider, :api_key, :model, 1)'
            );
            $stmt->execute([
                'user_id' => $userId,
                'provider' => $provider,
                'api_key' => $apiKey,
                'model' => $model,
            ]);

            if ($action !== 'test') {
                $flash = ['type' => 'ok', 'message' => 'Configuration IA enregistrée.'];
            }
        }
    }

    $stmt = $pdo->prepare(
        'SELECT provider, api_key, model, tokens_used, estimated_cost
         FROM ia_configurations
         WHERE user_id = :user_id AND is_active = 1
         ORDER BY updated_at DESC, id DESC
         LIMIT 1'
    );
    $stmt->execute(['user_id' => $userId]);
    $activeConfig = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $stmt = $pdo->prepare(
        'SELECT COALESCE(SUM(tokens_used), 0) AS total_tokens,
                COALESCE(SUM(estimated_cost), 0) AS total_cost
         FROM ia_configurations
         WHERE user_id = :user_id'
    );
    $stmt->execute(['user_id' => $userId]);
    $usage = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_tokens' => 0, 'total_cost' => 0];

    $provider = (string)($activeConfig['provider'] ?? 'openai');
    $apiKey = (string)($activeConfig['api_key'] ?? '');
    $model = (string)($activeConfig['model'] ?? ($models[$provider] ?? 'gpt-4o-mini'));
    ?>
    <div class="page-header">
        <h1><i class="fas fa-microchip page-icon"></i> Configuration IA</h1>
        <p>Gérez le fournisseur IA actif, testez la connexion et suivez les coûts.</p>
    </div>

    <?php if ($flash['message'] !== ''): ?>
        <div class="ia-flash ia-flash-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <div class="ia-config-grid">
        <form method="POST" class="ia-config-card">
            <input type="hidden" name="action_type" id="ia-action-type" value="save">

            <label>Réseau IA</label>
            <select name="provider" required>
                <?php foreach ($providers as $key => $label): ?>
                    <option value="<?= htmlspecialchars($key) ?>" <?= $provider === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Clé API</label>
            <input type="text" name="api_key" value="<?= htmlspecialchars($apiKey) ?>" placeholder="sk-..." required>

            <label>Modèle</label>
            <input type="text" name="model" value="<?= htmlspecialchars($model) ?>" required>

            <div class="ia-config-actions">
                <button type="submit" class="btn-primary">Enregistrer</button>
                <button type="submit" class="btn-secondary" onclick="document.getElementById('ia-action-type').value='test';">Tester la connexion</button>
            </div>
        </form>

        <div class="ia-config-card ia-metrics">
            <h3>Utilisation IA</h3>
            <div class="ia-metric-item">
                <span>Tokens utilisés</span>
                <strong><?= number_format((int)$usage['total_tokens'], 0, ',', ' ') ?></strong>
            </div>
            <div class="ia-metric-item">
                <span>Coût estimé</span>
                <strong><?= number_format((float)$usage['total_cost'], 4, ',', ' ') ?> €</strong>
            </div>
            <div class="ia-metric-item">
                <span>Statut actuel</span>
                <strong class="status-<?= get_ia_status($userId) ?>"><?= strtoupper(get_ia_status($userId)) ?></strong>
            </div>
        </div>
    </div>

    <style>
        .ia-config-grid { display: grid; grid-template-columns: 1.2fr .8fr; gap: 16px; }
        .ia-config-card { background: #fff; border: 1px solid #e8ecf0; border-radius: 10px; padding: 16px; display: grid; gap: 10px; }
        .ia-config-card label { font-size: 13px; color: #5a6a7a; font-weight: 600; }
        .ia-config-card input, .ia-config-card select {
            border: 1px solid #dce3ea; border-radius: 8px; padding: 10px 12px; font-size: 14px;
        }
        .ia-config-actions { display: flex; gap: 10px; margin-top: 8px; }
        .btn-primary, .btn-secondary {
            border: 0; border-radius: 8px; padding: 10px 12px; cursor: pointer; font-weight: 600;
        }
        .btn-primary { background: #1d4ed8; color: #fff; }
        .btn-secondary { background: #eef2ff; color: #3730a3; }
        .ia-flash { margin-bottom: 14px; padding: 10px 12px; border-radius: 8px; font-size: 13px; }
        .ia-flash-ok { background: #ecfdf5; color: #166534; border: 1px solid #bbf7d0; }
        .ia-flash-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .ia-metrics h3 { margin-bottom: 6px; }
        .ia-metric-item { display: flex; justify-content: space-between; border-top: 1px solid #eef2f6; padding: 10px 0; font-size: 14px; }
        .status-connected { color: #16a34a; }
        .status-disconnected { color: #dc2626; }
        @media (max-width: 960px) { .ia-config-grid { grid-template-columns: 1fr; } }
    </style>
    <?php
}

require_once __DIR__ . '/../../admin/views/layout.php';

<?php

declare(strict_types=1);

$pageTitle = 'Configuration des Agents IA';
$pageDescription = 'Gérez vos agents IA et leurs modèles OpenRouter.';

function renderContent(): void
{
    $openrouterKey = openrouterApiKey();
    $service = new AgentService(db(), $openrouterKey);
    $action = preg_replace('/[^a-z0-9_-]/', '', (string)($_GET['action'] ?? 'index'));
    $agentId = (int)($_GET['id'] ?? 0);

    // ============ ACTIONS ============

    if ($action === 'save-agent' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'name' => $_POST['name'] ?? '',
            'slug' => preg_replace('/[^a-z0-9_-]/', '-', strtolower($_POST['slug'] ?? '')),
            'description' => $_POST['description'] ?? '',
            'system_prompt' => $_POST['system_prompt'] ?? '',
            'task_category' => $_POST['task_category'] ?? '',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];

        if ($id > 0) {
            $service->updateAgent($id, $data);
        } else {
            $service->createAgent($data);
        }
        header('Location: /admin?module=agents');
        exit;
    }

    if ($action === 'assign-model' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $agentId = (int)($_POST['agent_id'] ?? 0);
        $modelId = $_POST['model_id'] ?? '';
        $config = [
            'provider' => 'openrouter',
            'temperature' => (float)($_POST['temperature'] ?? 0.7),
            'max_tokens' => (int)($_POST['max_tokens'] ?? 2048),
            'is_primary' => isset($_POST['is_primary']) ? 1 : 0,
        ];
        $service->assignModel($agentId, $modelId, $config);
        header('Location: /admin?module=agents&action=edit&id=' . $agentId);
        exit;
    }

    if ($action === 'sync-models') {
        if ($openrouterKey === '') {
            echo '<div style="background:#fee;color:#c00;padding:16px;border-radius:8px;margin:16px 0;">⚠️ Clé OpenRouter non configurée : <strong>Paramètres → Intégrations &amp; API → OpenRouter</strong> (ou variable <code>OPENROUTER_API_KEY</code> dans <code>.env</code>).</div>';
        } else {
            $count = $service->syncOpenrouterModels();
            echo '<div style="background:#efe;color:#060;padding:16px;border-radius:8px;margin:16px 0;">✅ ' . $count . ' modèles synchronisés.</div>';
        }
    }

    // ============ VIEWS ============

    if ($action === 'edit' && $agentId > 0) {
        renderEditAgent($service, $agentId);
        return;
    }

    if ($action === 'new') {
        renderNewAgent($service);
        return;
    }

    renderAgentsList($service);
}

function renderAgentsList(AgentService $service): void
{
    $agents = $service->getAgents();
    ?>
    <style>
        .agents-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .agents-header h1 { margin: 0; font-size: 28px; font-weight: 700; }
        .btn-new { background: #2563eb; color: white; padding: 10px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; }
        .btn-new:hover { background: #1d4ed8; }
        .agents-grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); margin-bottom: 24px; }
        .agent-card { background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; }
        .agent-card-head { display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px; }
        .agent-name { font-size: 18px; font-weight: 700; color: #0f172a; margin: 0; }
        .agent-category { display: inline-block; background: #dbeafe; color: #1e40af; font-size: 11px; font-weight: 600; padding: 4px 8px; border-radius: 4px; margin-top: 4px; }
        .agent-description { font-size: 13px; color: #6b7280; margin: 12px 0; line-height: 1.5; }
        .agent-models { font-size: 12px; color: #4b5563; margin: 12px 0; }
        .agent-models strong { color: #0f172a; }
        .agent-actions { display: flex; gap: 8px; margin-top: 12px; }
        .agent-actions a, .agent-actions button { flex: 1; padding: 8px 12px; border-radius: 6px; border: none; font-size: 12px; font-weight: 600; cursor: pointer; text-align: center; text-decoration: none; }
        .btn-edit { background: #3b82f6; color: white; }
        .btn-edit:hover { background: #2563eb; }
        .btn-sync { background: #8b5cf6; color: white; margin-bottom: 20px; padding: 10px 16px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; }
        .btn-sync:hover { background: #7c3aed; }
        .badge-active { background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .badge-inactive { background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .config-section { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; border-radius: 6px; margin-bottom: 24px; }
    </style>

    <div class="config-section">
        <strong>🔑 Configuration OpenRouter</strong>
        <p style="margin: 8px 0 0; font-size: 13px;">Allez à <strong>Paramètres → API</strong> pour ajouter votre clé OpenRouter.</p>
    </div>

    <div class="agents-header">
        <h1>🤖 Agents IA</h1>
        <a href="/admin?module=agents&action=new" class="btn-new"><i class="fas fa-plus"></i> Nouveau Agent</a>
    </div>

    <button onclick="location.href='/admin?module=agents&action=sync-models'" class="btn-sync"><i class="fas fa-sync"></i> Synchroniser Modèles OpenRouter</button>

    <?php if (empty($agents)): ?>
        <div style="background: #f9fafb; padding: 32px; border-radius: 8px; text-align: center; color: #9ca3af;">
            <p>Aucun agent créé. <a href="/admin?module=agents&action=new">Créez votre premier agent →</a></p>
        </div>
    <?php else: ?>
        <div class="agents-grid">
            <?php foreach ($agents as $agent): ?>
                <div class="agent-card">
                    <div class="agent-card-head">
                        <div>
                            <h3 class="agent-name"><?= htmlspecialchars($agent['name']) ?></h3>
                            <span class="agent-category"><?= htmlspecialchars($agent['task_category'] ?? 'Général') ?></span>
                        </div>
                        <span class="<?= $agent['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                            <?= $agent['is_active'] ? '✅ Actif' : '⏸️ Inactif' ?>
                        </span>
                    </div>
                    <p class="agent-description"><?= htmlspecialchars($agent['description'] ?? 'Pas de description') ?></p>
                    <div class="agent-models">
                        <strong>Modèles assignés:</strong> <?php
                        $models = (new AgentService(db(), openrouterApiKey()))->getAgentModels($agent['id']);
                        if (empty($models)) {
                            echo '<em style="color:#999;">Aucun modèle assigné</em>';
                        } else {
                            echo count($models) . ' modèle(s)';
                        }
                        ?>
                    </div>
                    <div class="agent-actions">
                        <a href="/admin?module=agents&action=edit&id=<?= $agent['id'] ?>" class="btn-edit">
                            <i class="fas fa-edit"></i> Éditer
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php
}

function renderNewAgent(AgentService $service): void
{
    renderAgentForm($service, null);
}

function renderEditAgent(AgentService $service, int $id): void
{
    $agent = $service->getAgent($id);
    if (!$agent) {
        echo '<p style="color:red;">Agent non trouvé.</p>';
        return;
    }
    renderAgentForm($service, $agent);
    renderAgentModels($service, $id);
}

function renderAgentForm(AgentService $service, ?array $agent): void
{
    $isEdit = $agent !== null;
    ?>
    <style>
        .form-section { background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 14px; }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-family: inherit; font-size: 14px; }
        .form-group textarea { resize: vertical; min-height: 120px; font-family: monospace; }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .form-actions { display: flex; gap: 12px; }
        .btn-submit { background: #10b981; color: white; padding: 10px 20px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .btn-submit:hover { background: #059669; }
        .btn-cancel { background: #e5e7eb; color: #0f172a; padding: 10px 20px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-cancel:hover { background: #d1d5db; }
        .form-title { font-size: 20px; font-weight: 700; margin-bottom: 20px; }
    </style>

    <div class="form-section">
        <h2 class="form-title"><?= $isEdit ? '✏️ Éditer Agent' : '➕ Créer un Agent' ?></h2>

        <form method="POST" action="/admin?module=agents&action=save-agent">
            <input type="hidden" name="id" value="<?= $agent['id'] ?? 0 ?>">

            <div class="form-group">
                <label>Nom de l'Agent</label>
                <input type="text" name="name" required value="<?= htmlspecialchars($agent['name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Slug (identifiant unique)</label>
                <input type="text" name="slug" required value="<?= htmlspecialchars($agent['slug'] ?? '') ?>" placeholder="agent-contenu">
            </div>

            <div class="form-group">
                <label>Catégorie de Tâche</label>
                <select name="task_category">
                    <option value="">-- Sélectionner --</option>
                    <option value="content" <?= ($agent['task_category'] ?? '') === 'content' ? 'selected' : '' ?>>📝 Génération de Contenu</option>
                    <option value="analysis" <?= ($agent['task_category'] ?? '') === 'analysis' ? 'selected' : '' ?>>🔍 Analyse & Extraction</option>
                    <option value="automation" <?= ($agent['task_category'] ?? '') === 'automation' ? 'selected' : '' ?>>🤖 Automatisation</option>
                    <option value="classification" <?= ($agent['task_category'] ?? '') === 'classification' ? 'selected' : '' ?>>📊 Classification</option>
                    <option value="vision" <?= ($agent['task_category'] ?? '') === 'vision' ? 'selected' : '' ?>>👁️ Vision & Images</option>
                    <option value="embedding" <?= ($agent['task_category'] ?? '') === 'embedding' ? 'selected' : '' ?>>🔗 Embeddings</option>
                </select>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" placeholder="Décrivez le rôle de cet agent..."><?= htmlspecialchars($agent['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>System Prompt (Instructions pour le modèle)</label>
                <textarea name="system_prompt" placeholder="Vous êtes un assistant spécialisé dans..."><?= htmlspecialchars($agent['system_prompt'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" <?= ($agent['is_active'] ?? 1) ? 'checked' : '' ?>>
                    Activer cet agent
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Enregistrer</button>
                <a href="/admin?module=agents" class="btn-cancel"><i class="fas fa-arrow-left"></i> Annuler</a>
            </div>
        </form>
    </div>
    <?php
}

function renderAgentModels(AgentService $service, int $agentId): void
{
    $models = $service->getAgentModels($agentId);
    $availableModels = $service->getAvailableModels();

    if (empty($availableModels)) {
        echo '<div style="background:#fee;padding:16px;border-radius:8px;margin:24px 0;"><strong>⚠️ Aucun modèle disponible.</strong> Synchronisez d\'abord les modèles OpenRouter.</div>';
        return;
    }

    ?>
    <style>
        .models-section { background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
        .models-title { font-size: 18px; font-weight: 700; margin-bottom: 16px; }
        .models-list { display: grid; gap: 12px; margin-bottom: 20px; }
        .model-item { background: #f9fafb; padding: 12px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; border-left: 4px solid #3b82f6; }
        .model-info strong { display: block; font-size: 14px; color: #0f172a; }
        .model-info small { display: block; font-size: 12px; color: #6b7280; margin-top: 2px; }
        .model-caps { display: flex; gap: 4px; flex-wrap: wrap; margin-top: 4px; }
        .cap { background: #e0e7ff; color: #4338ca; font-size: 10px; padding: 2px 6px; border-radius: 3px; }
        .assign-form { background: #f9fafb; padding: 16px; border-radius: 8px; border: 1px solid #e5e7eb; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 12px; margin-bottom: 12px; align-items: end; }
        .form-row select,
        .form-row input { width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; }
        .btn-assign { background: #3b82f6; color: white; padding: 8px 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .btn-assign:hover { background: #2563eb; }
        .badge-primary { background: #d1fae5; color: #065f46; padding: 2px 6px; border-radius: 3px; font-size: 11px; font-weight: 600; }
    </style>

    <div class="models-section">
        <h3 class="models-title">📦 Modèles Assignés</h3>

        <?php if (empty($models)): ?>
            <p style="color: #9ca3af;">Aucun modèle assigné à cet agent.</p>
        <?php else: ?>
            <div class="models-list">
                <?php foreach ($models as $model): ?>
                    <div class="model-item">
                        <div class="model-info">
                            <strong><?= htmlspecialchars($model['model_name']) ?></strong>
                            <small><?= htmlspecialchars($model['model_id']) ?></small>
                            <?php if ($model['is_primary']): ?>
                                <span class="badge-primary">Modèle Principal</span>
                            <?php endif; ?>
                            <div class="model-caps">
                                <?php if ($cap = json_decode($model['capabilities'] ?? '{}', true)): ?>
                                    <?php foreach ($cap as $k => $v): ?>
                                        <?php if ($v): ?>
                                            <span class="cap"><?= htmlspecialchars($k) ?></span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="assign-form">
            <h4 style="margin: 0 0 12px;">Assigner un Modèle</h4>
            <form method="POST" action="/admin?module=agents&action=assign-model">
                <input type="hidden" name="agent_id" value="<?= $agentId ?>">

                <div class="form-row">
                    <select name="model_id" required>
                        <option value="">-- Sélectionner un modèle --</option>
                        <?php foreach ($availableModels as $m): ?>
                            <option value="<?= htmlspecialchars($m['model_id']) ?>">
                                <?= htmlspecialchars($m['organization'] . ' / ' . $m['model_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="temperature" placeholder="Température" value="0.7" min="0" max="2" step="0.1">
                    <input type="number" name="max_tokens" placeholder="Max tokens" value="2048" min="100" max="32000">
                    <label style="display: flex; gap: 4px; align-items: center; white-space: nowrap;">
                        <input type="checkbox" name="is_primary">
                        Modèle par défaut
                    </label>
                </div>

                <button type="submit" class="btn-assign"><i class="fas fa-link"></i> Assigner</button>
            </form>
        </div>
    </div>
    <?php
}

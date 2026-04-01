<?php
$flash = $_SESSION['auth_prompt_flash'] ?? null;
unset($_SESSION['auth_prompt_flash']);
?>

<div class="mod-hero">
    <div class="mod-hero-content">
        <h1><i class="fas fa-robot"></i> Prompt Builder IA</h1>
        <p>Créez, testez et versionnez vos prompts système immobiliers.</p>
    </div>
    <div class="mod-hero-actions" style="display:flex;gap:8px;">
        <form method="post" action="/admin/dashboard.php?page=ai-prompts" style="display:inline;">
            <input type="hidden" name="action" value="seed">
            <button class="mod-btn mod-btn-secondary" type="submit"><i class="fas fa-download"></i> Installer 10 prompts</button>
        </form>
        <a class="mod-btn mod-btn-hero" href="/admin/dashboard.php?page=ai-prompts&action=create"><i class="fas fa-plus"></i> Nouveau prompt</a>
    </div>
</div>

<?php if ($flash): ?>
<div class="mod-flash mod-flash-<?= $flash['type'] === 'error' ? 'error' : 'success' ?>">
    <i class="fas fa-info-circle"></i> <?= htmlspecialchars($flash['message']) ?>
</div>
<?php endif; ?>

<div class="mod-card" style="padding:12px;margin-bottom:12px;">
    <form method="get" action="/admin/dashboard.php" style="display:flex;gap:10px;flex-wrap:wrap;align-items:end;">
        <input type="hidden" name="page" value="ai-prompts">
        <div>
            <label>Filtrer par type</label>
            <select name="type">
                <option value="">Tous</option>
                <?php foreach ($types as $t): ?>
                    <option value="<?= $t ?>" <?= (($_GET['type'] ?? '') === $t) ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="mod-btn mod-btn-primary">Filtrer</button>
    </form>
</div>

<div class="mod-card" style="overflow:auto;">
<table class="mod-table">
    <thead>
        <tr><th>Nom</th><th>Type</th><th>Plateforme</th><th>Actif</th><th>Mis à jour</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php if (empty($prompts)): ?>
        <tr><td colspan="6">Aucun prompt enregistré.</td></tr>
    <?php endif; ?>
    <?php foreach ($prompts as $prompt): ?>
        <tr>
            <td><?= htmlspecialchars($prompt['name']) ?></td>
            <td><?= htmlspecialchars($prompt['type']) ?></td>
            <td><?= htmlspecialchars((string) ($prompt['plateforme'] ?? '-')) ?></td>
            <td><?= (int) $prompt['is_active'] === 1 ? 'Oui' : 'Non' ?></td>
            <td><?= htmlspecialchars((string) $prompt['updated_at']) ?></td>
            <td style="display:flex;gap:6px;">
                <a class="mod-btn mod-btn-secondary" href="/admin/dashboard.php?page=ai-prompts&action=edit&id=<?= (int) $prompt['id'] ?>">Modifier</a>
                <a class="mod-btn mod-btn-primary" href="/admin/dashboard.php?page=ai-prompts&test_id=<?= (int) $prompt['id'] ?>">Tester avec IA</a>
                <form method="post" action="/admin/dashboard.php?page=ai-prompts" onsubmit="return confirm('Supprimer ce prompt ?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int) $prompt['id'] ?>">
                    <button class="mod-btn" type="submit">Supprimer</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php if ($preview): ?>
<div class="mod-grid-2" style="margin-top:16px;">
    <div class="mod-card" style="padding:14px;">
        <h3>Tester avec IA · <?= htmlspecialchars($preview['name']) ?></h3>
        <form method="get" action="/admin/dashboard.php">
            <input type="hidden" name="page" value="ai-prompts">
            <input type="hidden" name="test_id" value="<?= (int) $preview['id'] ?>">
            <div class="mod-form-grid">
                <?php foreach (['ville','persona','objectif','mot_cle','niveau_conscience','type_contenu'] as $field): ?>
                <div class="mod-form-group">
                    <label><?= ucfirst(str_replace('_', ' ', $field)) ?></label>
                    <input name="<?= $field ?>" value="<?= htmlspecialchars((string) ($_GET[$field] ?? '')) ?>">
                </div>
                <?php endforeach; ?>
            </div>
            <button class="mod-btn mod-btn-primary" type="submit">Prévisualiser</button>
        </form>
    </div>
    <div class="mod-card" style="padding:14px;">
        <h3>Prompt généré</h3>
        <pre style="white-space:pre-wrap;"><?= htmlspecialchars($preview['compiled_template']) ?></pre>
        <h4>Structure complète</h4>
        <pre style="white-space:pre-wrap;"><?= htmlspecialchars($preview['full_prompt']) ?></pre>
    </div>
</div>

<div class="mod-card" style="padding:14px;margin-top:12px;">
    <h3>Suggestion de stratégie (BONUS)</h3>
    <p><strong>Article pilier :</strong> <?= htmlspecialchars($strategy['article_pilier']) ?></p>
    <p><strong>5 articles satellites :</strong></p>
    <ul>
        <?php foreach ($strategy['articles_satellites'] as $item): ?><li><?= htmlspecialchars($item) ?></li><?php endforeach; ?>
    </ul>
    <p><strong>Plan SEO local :</strong></p>
    <ul>
        <?php foreach ($strategy['plan_seo_local'] as $item): ?><li><?= htmlspecialchars($item) ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

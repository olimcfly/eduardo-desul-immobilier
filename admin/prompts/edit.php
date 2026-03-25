<div class="mod-hero">
    <div class="mod-hero-content">
        <h1><i class="fas fa-pen"></i> Modifier le prompt</h1>
        <p>Édition sécurisée du template système.</p>
    </div>
</div>

<div class="mod-card" style="padding:16px;max-width:920px;">
    <form method="post" action="/admin/dashboard.php?page=ai-prompts">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?= (int) $prompt['id'] ?>">
        <div class="mod-form-grid">
            <div class="mod-form-group"><label>Nom</label><input required name="name" value="<?= htmlspecialchars($prompt['name']) ?>"></div>
            <div class="mod-form-group"><label>Type</label><select name="type"><?php foreach ($types as $t): ?><option value="<?= $t ?>" <?= $prompt['type'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option><?php endforeach; ?></select></div>
            <div class="mod-form-group"><label>Plateforme</label><select name="plateforme"><option value="">Aucune</option><?php foreach ($platforms as $p): ?><option value="<?= $p ?>" <?= ($prompt['plateforme'] ?? '') === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option><?php endforeach; ?></select></div>
            <div class="mod-form-group"><label><input type="checkbox" name="is_active" value="1" <?= (int) $prompt['is_active'] === 1 ? 'checked' : '' ?>> Prompt actif</label></div>
            <div class="mod-form-group full">
                <label>Template guidé</label>
                <textarea name="template" rows="12" required><?= htmlspecialchars($prompt['template']) ?></textarea>
            </div>
        </div>
        <div style="display:flex;gap:8px;">
            <button class="mod-btn mod-btn-primary" type="submit">Mettre à jour</button>
            <a class="mod-btn" href="/admin/dashboard.php?page=ai-prompts">Retour</a>
        </div>
    </form>
</div>

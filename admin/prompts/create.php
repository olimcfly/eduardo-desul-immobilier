<div class="mod-hero">
    <div class="mod-hero-content">
        <h1><i class="fas fa-plus"></i> Nouveau prompt</h1>
        <p>Formulaire guidé avec variables autorisées.</p>
    </div>
</div>

<div class="mod-card" style="padding:16px;max-width:920px;">
    <form method="post" action="/admin/dashboard.php?page=ai-prompts">
        <input type="hidden" name="action" value="store">
        <div class="mod-form-grid">
            <div class="mod-form-group"><label>Nom</label><input required name="name"></div>
            <div class="mod-form-group"><label>Type</label><select name="type"><?php foreach ($types as $t): ?><option value="<?= $t ?>"><?= ucfirst($t) ?></option><?php endforeach; ?></select></div>
            <div class="mod-form-group"><label>Plateforme</label><select name="plateforme"><option value="">Aucune</option><?php foreach ($platforms as $p): ?><option value="<?= $p ?>"><?= ucfirst($p) ?></option><?php endforeach; ?></select></div>
            <div class="mod-form-group"><label><input type="checkbox" name="is_active" value="1" checked> Prompt actif</label></div>
            <div class="mod-form-group full">
                <label>Template (variables autorisées : {{ville}}, {{persona}}, {{objectif}}, {{mot_cle}}, {{niveau_conscience}}, {{type_contenu}})</label>
                <textarea name="template" rows="12" required placeholder="CONTEXTE : ... OBJECTIF : ..."></textarea>
            </div>
        </div>
        <div style="display:flex;gap:8px;">
            <button class="mod-btn mod-btn-primary" type="submit">Enregistrer</button>
            <a class="mod-btn" href="/admin/dashboard.php?page=ai-prompts">Annuler</a>
        </div>
    </form>
</div>

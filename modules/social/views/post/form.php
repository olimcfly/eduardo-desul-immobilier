<article class="post-form-wrapper">
    <h2><?= $post ? 'Modifier la publication' : 'Créer une publication' ?></h2>
    <form method="post" action="/admin?module=social&action=save-post" class="post-form-grid">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= (int) ($post['id'] ?? 0) ?>">

        <label>Titre
            <input type="text" name="titre" value="<?= e((string) ($post['titre'] ?? '')) ?>" required>
        </label>

        <label>Séquence
            <select name="sequence_id">
                <option value="0">Aucune</option>
                <?php foreach ($sequences as $sequence): ?>
                    <option value="<?= (int) $sequence['id'] ?>" <?= (int) ($post['sequence_id'] ?? 0) === (int) $sequence['id'] ? 'selected' : '' ?>>
                        <?= e((string) $sequence['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>Contenu
            <textarea name="contenu" rows="7" required><?= e((string) ($post['contenu'] ?? '')) ?></textarea>
        </label>

        <label>Planifier le
            <input type="datetime-local" name="planifie_at" value="<?= e(isset($post['planifie_at']) ? date('Y-m-d\TH:i', strtotime((string) $post['planifie_at'])) : '') ?>">
        </label>

        <fieldset>
            <legend>Réseaux</legend>
            <?php $networks = json_decode((string) ($post['reseaux'] ?? '[]'), true) ?: ['facebook']; ?>
            <label><input type="checkbox" name="reseaux[]" value="facebook" <?= in_array('facebook', $networks, true) ? 'checked' : '' ?>> Facebook</label>
            <label><input type="checkbox" name="reseaux[]" value="instagram" <?= in_array('instagram', $networks, true) ? 'checked' : '' ?>> Instagram</label>
            <label><input type="checkbox" name="reseaux[]" value="linkedin" <?= in_array('linkedin', $networks, true) ? 'checked' : '' ?>> LinkedIn</label>
        </fieldset>

        <label>Statut
            <select name="statut">
                <?php $status = (string) ($post['statut'] ?? 'brouillon'); ?>
                <option value="brouillon" <?= $status === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
                <option value="planifie" <?= $status === 'planifie' ? 'selected' : '' ?>>Planifié</option>
                <option value="publie" <?= $status === 'publie' ? 'selected' : '' ?>>Publié</option>
            </select>
        </label>

        <div class="post-form-actions">
            <button class="social-btn social-btn-primary" type="submit">Enregistrer</button>
            <a class="social-btn" href="/admin?module=social&action=sequences">Annuler</a>
        </div>
    </form>
</article>

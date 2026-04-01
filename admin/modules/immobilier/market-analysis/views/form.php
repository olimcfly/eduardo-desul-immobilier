<?php
$old = $state['old'] ?? [];
$errors = $state['errors'] ?? [];
?>

<div class="ma2-card">
    <h2 style="margin-top:0;">Nouvelle analyse de marché</h2>

    <?php if ($errors): ?>
        <ul class="ma2-errors">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars((string) $error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['auth_csrf_token'] ?? '') ?>">
        <input type="hidden" name="action" value="create">

        <div class="ma2-grid">
            <div class="ma2-field">
                <label for="city">Ville *</label>
                <input id="city" type="text" name="city" required maxlength="120" value="<?= htmlspecialchars((string) ($old['city'] ?? '')) ?>">
            </div>
            <div class="ma2-field">
                <label for="postal_code">Code postal</label>
                <input id="postal_code" type="text" name="postal_code" maxlength="12" value="<?= htmlspecialchars((string) ($old['postal_code'] ?? '')) ?>">
            </div>
            <div class="ma2-field">
                <label for="area_name">Secteur / Quartier</label>
                <input id="area_name" type="text" name="area_name" maxlength="120" value="<?= htmlspecialchars((string) ($old['area_name'] ?? '')) ?>">
            </div>
            <div class="ma2-field">
                <label for="target_type">Cible</label>
                <select id="target_type" name="target_type">
                    <?php $target = (string) ($old['target_type'] ?? 'mixte'); ?>
                    <option value="mixte" <?= $target === 'mixte' ? 'selected' : '' ?>>Mixte</option>
                    <option value="vendeur" <?= $target === 'vendeur' ? 'selected' : '' ?>>Vendeur</option>
                    <option value="acheteur" <?= $target === 'acheteur' ? 'selected' : '' ?>>Acheteur</option>
                </select>
            </div>
            <div class="ma2-field">
                <label for="property_type">Type de bien dominant</label>
                <input id="property_type" type="text" name="property_type" maxlength="80" value="<?= htmlspecialchars((string) ($old['property_type'] ?? '')) ?>">
            </div>
        </div>

        <div class="ma2-field" style="margin-top:12px;">
            <label for="manual_notes">Notes manuelles</label>
            <textarea id="manual_notes" name="manual_notes"><?= htmlspecialchars((string) ($old['manual_notes'] ?? '')) ?></textarea>
        </div>

        <div style="display:flex;gap:8px;margin-top:14px;">
            <button class="ma2-btn" type="submit"><i class="fas fa-save"></i> Créer l'analyse</button>
            <a class="ma2-btn" style="background:#6b7280" href="?page=market-analysis">Annuler</a>
        </div>
    </form>
</div>

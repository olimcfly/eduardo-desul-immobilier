<?php
/** @var array $bien */
/** @var array $errors */
/** @var array $propertyTypes */
/** @var array $propertyStatuses */

$bien = $bien ?? [];
$errors = $errors ?? [];
$propertyTypes = $propertyTypes ?? [];
$propertyStatuses = $propertyStatuses ?? [];
?>

<form method="post" enctype="multipart/form-data" class="bien-form">
    <?= csrfField() ?>

    <label>Titre
        <input type="text" name="title" value="<?= e($bien['title'] ?? $bien['titre'] ?? '') ?>" required>
    </label>

    <label>Référence
        <input type="text" name="reference" value="<?= e($bien['reference'] ?? '') ?>" required>
    </label>

    <label>Type
        <select name="type" required>
            <?php foreach ($propertyTypes as $type): ?>
                <option value="<?= e($type) ?>" <?= ($bien['type'] ?? $bien['type_bien'] ?? '') === $type ? 'selected' : '' ?>>
                    <?= ucfirst(e($type)) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Statut
        <select name="status" required>
            <?php foreach ($propertyStatuses as $status): ?>
                <option value="<?= e($status) ?>" <?= ($bien['status'] ?? $bien['statut'] ?? '') === $status ? 'selected' : '' ?>>
                    <?= ucfirst(e($status)) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Prix (€)
        <input type="number" name="price" min="1" step="1" value="<?= e((string) ($bien['price'] ?? $bien['prix'] ?? '')) ?>" required>
    </label>

    <label>Surface (m²)
        <input type="number" name="surface" min="1" step="0.1" value="<?= e((string) ($bien['surface'] ?? '')) ?>" required>
    </label>

    <label>Description
        <textarea name="description" rows="8" required><?= e($bien['description'] ?? '') ?></textarea>
    </label>

    <label>Classe énergie (DPE)
        <input type="text" name="energy_rating" maxlength="10" value="<?= e($bien['energy_rating'] ?? $bien['dpe_classe'] ?? '') ?>">
    </label>

    <label>Chauffage
        <input type="text" name="heating" value="<?= e($bien['heating'] ?? $bien['mode_chauffage'] ?? '') ?>">
    </label>

    <label>Visite virtuelle (URL)
        <input type="url" name="virtual_tour_url" value="<?= e($bien['virtual_tour_url'] ?? $bien['visite_virtuelle_url'] ?? '') ?>">
    </label>

    <label>Photos
        <input type="file" name="photos[]" multiple accept=".jpg,.jpeg,.png,.webp">
    </label>

    <?php if (!empty($errors)): ?>
        <div class="form-errors">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e((string) $error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <button type="submit">Enregistrer le bien</button>
</form>

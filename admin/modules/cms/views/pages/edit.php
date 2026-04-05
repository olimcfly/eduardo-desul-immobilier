<?php
$pageTitle = ucfirst(str_replace('-', ' ', $page_slug));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier <?= htmlspecialchars($pageTitle) ?> | CMS</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; }
        .container { max-width: 900px; margin: 0 auto; }
        .section { border: 1px solid #ddd; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; font-weight: 600; margin-bottom: 0.3rem; }
        input[type="text"], textarea { width: 100%; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; }
        textarea { min-height: 120px; }
        .btn { border: none; background: #1d4ed8; color: #fff; padding: 0.75rem 1.2rem; border-radius: 6px; cursor: pointer; }
    </style>
</head>
<body>
<main class="container">
    <h1>Modifier la page <?= htmlspecialchars($pageTitle) ?></h1>

    <?php if (!empty($_GET['success'])): ?>
        <p style="color: #15803d;">Les modifications ont été enregistrées.</p>
    <?php endif; ?>

    <form method="POST" action="/admin/cms/save">
        <input type="hidden" name="page_slug" value="<?= htmlspecialchars($page_slug) ?>">

        <?php foreach ($sections as $sectionKey => $sectionConfig): ?>
            <div class="section">
                <h3><?= htmlspecialchars($sectionConfig['title'] ?? ucfirst($sectionKey)) ?></h3>

                <?php foreach (($sectionConfig['fields'] ?? []) as $fieldKey => $fieldConfig): ?>
                    <div class="form-group">
                        <label for="<?= htmlspecialchars($sectionKey . '_' . $fieldKey) ?>">
                            <?= htmlspecialchars($fieldConfig['label'] ?? ucfirst($fieldKey)) ?>
                        </label>
                        <?php if (($fieldConfig['type'] ?? 'text') === 'textarea'): ?>
                            <textarea
                                id="<?= htmlspecialchars($sectionKey . '_' . $fieldKey) ?>"
                                name="sections[<?= htmlspecialchars($sectionKey) ?>][<?= htmlspecialchars($fieldKey) ?>]"
                            ><?= htmlspecialchars($pageData[$sectionKey][$fieldKey] ?? '') ?></textarea>
                        <?php else: ?>
                            <input
                                type="text"
                                id="<?= htmlspecialchars($sectionKey . '_' . $fieldKey) ?>"
                                name="sections[<?= htmlspecialchars($sectionKey) ?>][<?= htmlspecialchars($fieldKey) ?>]"
                                value="<?= htmlspecialchars($pageData[$sectionKey][$fieldKey] ?? '') ?>"
                            >
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="btn">Enregistrer</button>
    </form>
</main>
</body>
</html>

<?php
$items = is_array($sectionData['items'] ?? null) ? $sectionData['items'] : [];
?>

<?php if ($items !== []): ?>
<div class="stat-strip" role="region" aria-label="Chiffres clés">
    <div class="container">
        <div class="stat-strip__inner">
            <?php foreach ($items as $item): ?>
                <?php
                $value = (string) ($item['value'] ?? '');
                $label = (string) ($item['label'] ?? '');
                if ($value === '' && $label === '') {
                    continue;
                }
                ?>
                <div class="stat-item">
                    <?php if ($value !== ''): ?>
                        <span class="stat-item__value"><?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>

                    <?php if ($label !== ''): ?>
                        <span class="stat-item__label"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>
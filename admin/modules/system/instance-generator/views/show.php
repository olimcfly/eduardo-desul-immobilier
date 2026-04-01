<?php
$flash = $_SESSION['auth_instance_generator_flash'] ?? null;
unset($_SESSION['auth_instance_generator_flash']);

$mask = static function (?string $value): string {
    if ($value === null || $value === '') {
        return '-';
    }

    $len = strlen($value);
    if ($len <= 4) {
        return str_repeat('*', $len);
    }

    return substr($value, 0, 2) . str_repeat('*', max(2, $len - 4)) . substr($value, -2);
};
?>
<div class="card">
    <div class="card-hd" style="display:flex;justify-content:space-between;align-items:center;">
        <h3>Détail instance #<?= (int) $instance['id'] ?></h3>
        <div style="display:flex;gap:8px;">
            <a class="btn btn-s btn-sm" href="/admin/dashboard.php?page=instance-generator">Retour liste</a>
            <a class="btn btn-s btn-sm" href="/admin/dashboard.php?page=instance-generator&action=edit&id=<?= (int) $instance['id'] ?>">Modifier</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert <?= $flash['type'] === 'error' ? 'alert-error' : 'alert-success' ?>" style="margin-bottom:12px;">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:repeat(2,minmax(220px,1fr));gap:8px;">
        <?php foreach ($instance as $key => $value): ?>
            <div style="padding:8px;border:1px solid #e2e8f0;border-radius:8px;">
                <strong><?= htmlspecialchars($key) ?></strong><br>
                <?php if (in_array($key, ['admin_password_temp','db_pass','smtp_pass','openai_api_key','perplexity_api_key'], true)): ?>
                    <?= htmlspecialchars($mask((string) $value)) ?>
                <?php else: ?>
                    <?= htmlspecialchars((string) ($value ?? '-')) ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <form method="post" action="/admin/dashboard.php?page=instance-generator" style="margin-top:12px;">
        <input type="hidden" name="action" value="generate">
        <input type="hidden" name="id" value="<?= (int) $instance['id'] ?>">
        <button class="btn btn-p" type="submit">
            <i class="fas fa-box-archive"></i> Générer le package
        </button>
    </form>
</div>

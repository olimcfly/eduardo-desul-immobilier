<?php
$flash = $_SESSION['instance_generator_flash'] ?? null;
unset($_SESSION['instance_generator_flash']);
?>
<div class="card">
    <div class="card-hd" style="display:flex;justify-content:space-between;align-items:center;">
        <h3><?= $formAction === 'store' ? 'Créer une instance' : 'Modifier une instance' ?></h3>
        <a class="btn btn-s btn-sm" href="/admin/dashboard.php?page=instance-generator">Retour liste</a>
    </div>

    <?php if ($flash): ?>
        <div class="alert <?= $flash['type'] === 'error' ? 'alert-error' : 'alert-success' ?>" style="margin-bottom:12px;">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/admin/dashboard.php?page=instance-generator" style="display:grid;grid-template-columns:repeat(2,minmax(220px,1fr));gap:10px;">
        <input type="hidden" name="action" value="<?= htmlspecialchars($formAction) ?>">
        <?php if ($formAction === 'update'): ?>
            <input type="hidden" name="id" value="<?= (int) $instance['id'] ?>">
        <?php endif; ?>

        <?php
        $fields = [
            'client_name','business_name','domain','city','admin_email','admin_password_temp',
            'db_host','db_port','db_name','db_user','db_pass',
            'smtp_host','smtp_port','smtp_user','smtp_pass','smtp_encryption','from_email',
            'openai_api_key','perplexity_api_key','logo_path'
        ];
        foreach ($fields as $field):
        ?>
            <label style="display:flex;flex-direction:column;font-size:13px;gap:4px;">
                <span><?= htmlspecialchars($field) ?></span>
                <input
                    type="<?= str_contains($field, 'email') ? 'email' : (str_contains($field, 'password') || str_ends_with($field, '_pass') || str_contains($field, 'api_key') ? 'password' : 'text') ?>"
                    name="<?= htmlspecialchars($field) ?>"
                    value="<?= htmlspecialchars((string) ($instance[$field] ?? '')) ?>"
                    <?= in_array($field, ['client_name','business_name','domain','city','admin_email','admin_password_temp','db_host','db_port','db_name','db_user','db_pass'], true) ? 'required' : '' ?>
                >
            </label>
        <?php endforeach; ?>

        <label style="display:flex;flex-direction:column;font-size:13px;gap:4px;">
            <span>status</span>
            <select name="status">
                <?php foreach (ClientInstance::STATUSES as $status): ?>
                    <option value="<?= htmlspecialchars($status) ?>" <?= ($instance['status'] ?? 'draft') === $status ? 'selected' : '' ?>>
                        <?= htmlspecialchars($status) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <div style="grid-column:1/-1;display:flex;gap:10px;">
            <button class="btn btn-p" type="submit">Enregistrer</button>
            <a class="btn btn-s" href="/admin/dashboard.php?page=instance-generator">Annuler</a>
        </div>
    </form>
</div>

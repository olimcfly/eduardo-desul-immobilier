<?php

declare(strict_types=1);

$s = settings_group('telegram');
$v = fn(string $k, string $d = '') => htmlspecialchars((string)($s[$k] ?? $d), ENT_QUOTES, 'UTF-8');

// Récupérer les utilisateurs Telegram
try {
    $stmt = db()->prepare('
        SELECT id, telegram_id, first_name, username, is_approved, approved_at
        FROM telegram_users
        ORDER BY created_at DESC
        LIMIT 50
    ');
    $stmt->execute();
    $telegramUsers = $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
} catch (Throwable $e) {
    $telegramUsers = [];
}

?>

<form class="settings-form" method="post">
    <input type="hidden" name="section" value="telegram">

    <div class="form-section-title">Configuration du Bot Telegram</div>

    <div class="form-group">
        <label>Token du Bot (de BotFather)</label>
        <div style="display: flex; gap: 8px;">
            <input type="password" name="telegram_bot_token" value="<?= $v('telegram_bot_token') ?>"
                   placeholder="123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11" class="api-key-field">
            <button type="button" class="api-key-toggle" style="padding: 8px 12px; background: #e5e7eb; border: none; border-radius: 6px; cursor: pointer;">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        <small style="color: #6b7280; margin-top: 4px; display: block;">
            Récupérez cette valeur en parlant à <strong>@BotFather</strong> sur Telegram
        </small>
    </div>

    <div class="form-group">
        <label>Token Secret du Webhook</label>
        <div style="display: flex; gap: 8px;">
            <input type="password" name="telegram_webhook_token" value="<?= $v('telegram_webhook_token') ?>"
                   placeholder="token_secret_random" class="api-key-field">
            <button type="button" class="api-key-toggle" style="padding: 8px 12px; background: #e5e7eb; border: none; border-radius: 6px; cursor: pointer;">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        <small style="color: #6b7280; margin-top: 4px; display: block;">
            Générez un token aléatoire pour sécuriser votre webhook
        </small>
    </div>

    <div class="form-group">
        <label>IDs des Admins (séparés par des virgules)</label>
        <input type="text" name="telegram_admin_ids" value="<?= $v('telegram_admin_ids') ?>"
               placeholder="123456789,987654321">
        <small style="color: #6b7280; margin-top: 4px; display: block;">
            Les admins recevront les notifications. Trouvez votre ID avec <strong>@userinfobot</strong>
        </small>
    </div>

    <div class="form-section-title" style="margin-top: 24px;">URL du Webhook</div>
    <div style="background: #f9fafb; padding: 12px; border-radius: 6px; border-left: 4px solid #0088cc; margin-bottom: 16px;">
        <code style="font-size: 12px; word-break: break-all;">
            https://<?= $_SERVER['HTTP_HOST'] ?? 'votre-domaine.com' ?>/telegram-webhook.php?token=[TELEGRAM_WEBHOOK_TOKEN]
        </code>
        <small style="color: #6b7280; display: block; margin-top: 8px;">
            Utilisez cette URL pour configurer le webhook avec BotFather
        </small>
    </div>

    <div class="form-section-title" style="margin-top: 24px;">Utilisateurs Approuvés</div>

    <?php if (empty($telegramUsers)): ?>
        <div style="background: #f9fafb; padding: 16px; border-radius: 6px; text-align: center; color: #9ca3af;">
            Aucun utilisateur Telegram enregistré
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <th style="text-align: left; padding: 8px; font-weight: 600;">Nom</th>
                        <th style="text-align: left; padding: 8px; font-weight: 600;">Username</th>
                        <th style="text-align: left; padding: 8px; font-weight: 600;">Statut</th>
                        <th style="text-align: left; padding: 8px; font-weight: 600;">Approuvé le</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($telegramUsers as $user): ?>
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 8px;"><?= htmlspecialchars($user['first_name']) ?></td>
                        <td style="padding: 8px; color: #6b7280;">@<?= htmlspecialchars($user['username'] ?? 'N/A') ?></td>
                        <td style="padding: 8px;">
                            <?php if ($user['is_approved']): ?>
                                <span style="background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                    ✅ Approuvé
                                </span>
                            <?php else: ?>
                                <span style="background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                    ⏳ En attente
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 8px; color: #6b7280;">
                            <?= $user['approved_at'] ? date('d/m/Y', strtotime($user['approved_at'])) : '—' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <small style="color: #6b7280; display: block; margin-top: 12px;">
            Pour approuver de nouveaux utilisateurs, allez sur <strong>/admin?module=telegram</strong>
        </small>
    <?php endif; ?>

    <div class="drawer-footer">
        <button type="button" class="btn-cancel" onclick="closeSettingsDrawer()">Annuler</button>
        <button type="submit" class="btn-save"><i class="fas fa-check"></i> Enregistrer</button>
    </div>
</form>

<script>
// Toggles pour les clés API
document.querySelectorAll('.api-key-toggle').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        const inp = btn.previousElementSibling;
        if (inp.type === 'password') {
            inp.type = 'text';
            btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
        } else {
            inp.type = 'password';
            btn.innerHTML = '<i class="fas fa-eye"></i>';
        }
    });
});
</script>

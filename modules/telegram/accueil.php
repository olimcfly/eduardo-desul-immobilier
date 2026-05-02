<?php

declare(strict_types=1);

$action = preg_replace('/[^a-z0-9_-]/i', '', (string)($_GET['action'] ?? 'index'));

if ($action === 'approve') {
    header('Content-Type: application/json');
    $telegramUserId = (int)($_POST['telegram_user_id'] ?? 0);

    try {
        $stmt = db()->prepare('
            UPDATE telegram_users
            SET is_approved = TRUE, approved_at = NOW(), approved_by = ?
            WHERE id = ?
        ');
        $success = $stmt->execute([
            $_SESSION['user_id'] ?? 0,
            $telegramUserId,
        ]);

        echo json_encode(['success' => $success]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'reject') {
    header('Content-Type: application/json');
    $telegramUserId = (int)($_POST['telegram_user_id'] ?? 0);

    try {
        $stmt = db()->prepare('DELETE FROM telegram_users WHERE id = ?');
        $success = $stmt->execute([$telegramUserId]);

        echo json_encode(['success' => $success]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

$pageTitle = 'Bot Telegram';
$pageDescription = 'Configuration et gestion du bot Telegram';

function renderContent(): void {
    ?>
    <style>
        .telegram-hero { background: linear-gradient(135deg, #0088cc 0%, #005fa3 100%); border-radius: 16px; padding: 24px 20px; color: #fff; margin-bottom: 24px; }
        .telegram-hero h1 { margin: 0 0 8px; font-size: 28px; font-weight: 700; }
        .telegram-hero p { margin: 0; color: rgba(255,255,255,.78); font-size: 15px; }
        .telegram-grid { display: grid; gap: 24px; grid-template-columns: 1fr 1fr; margin-bottom: 32px; }
        .telegram-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; }
        .telegram-card h2 { margin: 0 0 12px; font-size: 16px; font-weight: 600; color: #0f172a; }
        .telegram-card p { margin: 0 0 12px; font-size: 13px; color: #6b7280; line-height: 1.6; }
        .code-block { background: #f9fafb; padding: 12px; border-radius: 6px; font-family: monospace; font-size: 12px; color: #0f172a; overflow-x: auto; margin: 12px 0; }
        .request-list { display: flex; flex-direction: column; gap: 12px; margin-top: 12px; }
        .request-item { background: #f9fafb; padding: 12px; border-radius: 6px; border-left: 4px solid #0088cc; }
        .request-name { font-weight: 600; color: #0f172a; font-size: 13px; }
        .request-info { font-size: 12px; color: #6b7280; margin: 4px 0; }
        .request-actions { display: flex; gap: 8px; margin-top: 8px; }
        .btn { padding: 6px 12px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer; font-size: 12px; }
        .btn-approve { background: #10b981; color: white; }
        .btn-approve:hover { background: #059669; }
        .btn-reject { background: #ef4444; color: white; }
        .btn-reject:hover { background: #dc2626; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-approved { background: #d1fae5; color: #065f46; }
        .config-section { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; border-radius: 6px; margin-bottom: 24px; }
    </style>

    <div class="telegram-hero">
        <h1>🤖 Bot Telegram</h1>
        <p>Gérez votre site depuis Telegram. Contrôlez les séquences, prospects et bien plus!</p>
    </div>

    <div class="config-section">
        <strong>⚙️ Configuration Requise</strong>
        <p style="margin: 8px 0 0; font-size: 13px; color: #92400e;">Avant de commencer, vous devez configurer votre bot Telegram.</p>
    </div>

    <div class="telegram-grid">
        <div class="telegram-card">
            <h2>1️⃣ Créer le Bot</h2>
            <p>Discutez avec <strong>@BotFather</strong> sur Telegram:</p>
            <ol style="margin: 8px 0; padding-left: 20px; font-size: 13px;">
                <li>Envoyez <code>/newbot</code></li>
                <li>Choisissez un nom (ex: <code>Immobilier Bot</code>)</li>
                <li>Choisissez un username (ex: <code>immo_bot</code>)</li>
                <li>Copiez le token API généré</li>
            </ol>
        </div>

        <div class="telegram-card">
            <h2>2️⃣ Configurer les Variables d'Environnement</h2>
            <p>Ajoutez à votre <code>.env</code>:</p>
            <div class="code-block">TELEGRAM_BOT_TOKEN=votre_token_ici
TELEGRAM_WEBHOOK_TOKEN=token_secret_random
TELEGRAM_ADMIN_IDS=123456,789012</div>
            <p style="margin: 8px 0 0; font-size: 12px; color: #6b7280;">
                Générez un token random pour TELEGRAM_WEBHOOK_TOKEN (pour la sécurité du webhook)
            </p>
        </div>

        <div class="telegram-card">
            <h2>3️⃣ Configurer le Webhook</h2>
            <p>Utilisez cette commande curl (remplacez les valeurs):</p>
            <div class="code-block">curl -X POST https://api.telegram.org/bot[TOKEN]/setWebhook \
  -d url="https://votre-domaine.com/telegram-webhook.php?token=[WEBHOOK_TOKEN]"</div>
        </div>

        <div class="telegram-card">
            <h2>4️⃣ Tester le Bot</h2>
            <p>Recherchez votre bot sur Telegram et envoyez <code>/start</code></p>
            <p style="margin: 8px 0 0; font-size: 12px; color: #6b7280;">
                Votre demande d'accès s'affichera ci-dessous et devra être approuvée.
            </p>
        </div>
    </div>

    <div class="telegram-card" style="grid-column: 1/-1;">
        <h2>📋 Demandes d'Accès en Attente</h2>

        <?php
        try {
            $stmt = db()->prepare('
                SELECT id, telegram_id, first_name, username, created_at
                FROM telegram_users
                WHERE is_approved = FALSE
                ORDER BY created_at DESC
            ');
            $stmt->execute();
            $pending = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($pending)):
        ?>
            <p style="color: #9ca3af; text-align: center; padding: 20px;">Aucune demande en attente</p>
        <?php else: ?>
            <div class="request-list">
                <?php foreach ($pending as $req): ?>
                    <div class="request-item">
                        <div class="request-name">👤 <?= htmlspecialchars($req['first_name']) ?></div>
                        <div class="request-info">🆔 Telegram ID: <code><?= $req['telegram_id'] ?></code></div>
                        <div class="request-info">📱 Username: @<?= htmlspecialchars($req['username'] ?? 'non défini') ?></div>
                        <div class="request-info">📅 Demande le: <?= date('d/m/Y à H:i', strtotime($req['created_at'])) ?></div>
                        <div class="request-actions">
                            <button onclick="approveUser(<?= $req['id'] ?>)" class="btn btn-approve">✅ Approuver</button>
                            <button onclick="rejectUser(<?= $req['id'] ?>)" class="btn btn-reject">❌ Rejeter</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php } catch (Throwable $e) {
            echo '<p style="color: #ef4444;">Erreur: ' . htmlspecialchars($e->getMessage()) . '</p>';
        } ?>
    </div>

    <div class="telegram-card" style="grid-column: 1/-1;">
        <h2>✅ Utilisateurs Approuvés</h2>

        <?php
        try {
            $stmt = db()->prepare('
                SELECT id, first_name, username, approved_at
                FROM telegram_users
                WHERE is_approved = TRUE
                ORDER BY approved_at DESC
            ');
            $stmt->execute();
            $approved = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($approved)):
        ?>
            <p style="color: #9ca3af; text-align: center; padding: 20px;">Aucun utilisateur approuvé</p>
        <?php else: ?>
            <div class="request-list">
                <?php foreach ($approved as $user): ?>
                    <div class="request-item" style="border-left-color: #10b981;">
                        <div class="request-name">👤 <?= htmlspecialchars($user['first_name']) ?></div>
                        <div class="request-info">📱 Username: @<?= htmlspecialchars($user['username'] ?? 'non défini') ?></div>
                        <div class="request-info">✅ Approuvé le: <?= date('d/m/Y à H:i', strtotime($user['approved_at'])) ?></div>
                        <span class="badge badge-approved">Actif</span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php } catch (Throwable $e) {
            echo '<p style="color: #ef4444;">Erreur: ' . htmlspecialchars($e->getMessage()) . '</p>';
        } ?>
    </div>

    <script>
    function approveUser(userId) {
        if (confirm('Approuver cet utilisateur?')) {
            fetch('/admin?module=telegram&action=approve', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'telegram_user_id=' + userId
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('❌ Erreur: ' + (data.error || 'Erreur inconnue'));
                }
            });
        }
    }

    function rejectUser(userId) {
        if (confirm('Rejeter cette demande?')) {
            fetch('/admin?module=telegram&action=reject', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'telegram_user_id=' + userId
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('❌ Erreur: ' + (data.error || 'Erreur inconnue'));
                }
            });
        }
    }
    </script>
    <?php
}

<?php
require_once '../../core/bootstrap.php';

if (Auth::check()) {
    header('Location: /admin/');
    exit;
}

// Créer la table si elle n'existe pas encore
db()->exec("
    CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
        `id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
        `user_id`    INT UNSIGNED     NOT NULL,
        `token_hash` VARCHAR(255)     NOT NULL,
        `expires_at` DATETIME         NOT NULL,
        `used_at`    DATETIME         DEFAULT NULL,
        `created_at` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_token`   (`token_hash`(64)),
        KEY `idx_user`    (`user_id`),
        CONSTRAINT `fk_prt_user`
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$flash = Session::getFlash();
$error = null;
$sent  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } else {
        try {
            $pdo  = db();
            $stmt = $pdo->prepare(
                "SELECT id, name FROM users WHERE email = ? AND role IN ('admin','superadmin') AND is_active = 1 LIMIT 1"
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Invalider les anciens tokens de cet utilisateur
                $pdo->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?")
                    ->execute([$user['id']]);

                // Générer un token sécurisé
                $token     = bin2hex(random_bytes(32)); // 64 chars hex
                $tokenHash = hash('sha256', $token);
                $expires   = date('Y-m-d H:i:s', time() + 3600); // 1 heure

                $pdo->prepare(
                    "INSERT INTO password_reset_tokens (user_id, token_hash, expires_at)
                     VALUES (?, ?, ?)"
                )->execute([$user['id'], $tokenHash, $expires]);

                $appUrl   = rtrim($_ENV['APP_URL'] ?? 'https://eduardo-desul-immobilier.fr', '/');
                $resetUrl = $appUrl . '/admin/reset-password?token=' . urlencode($token);

                MailService::send(
                    $email,
                    'Réinitialisation de votre mot de passe — Admin',
                    "Bonjour {$user['name']},\n\nVous avez demandé la réinitialisation de votre mot de passe.\n\nCliquez sur ce lien (valable 1 heure) :\n{$resetUrl}\n\nSi vous n'avez pas fait cette demande, ignorez cet email.",
                    "<p>Bonjour <strong>" . htmlspecialchars($user['name']) . "</strong>,</p>
                    <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
                    <p style='margin:1.5rem 0'>
                        <a href='{$resetUrl}' style='background:#c9a84c;color:#0f2237;padding:.75rem 1.5rem;border-radius:8px;font-weight:700;text-decoration:none;display:inline-block'>
                            Réinitialiser mon mot de passe
                        </a>
                    </p>
                    <p style='color:#999;font-size:12px'>Ce lien est valable <strong>1 heure</strong>.<br>
                    Si vous n'avez pas fait cette demande, ignorez cet email.</p>"
                );
            }

            // Toujours afficher le même message (ne pas révéler si l'email existe)
            Session::flash('success', 'Si cet email est enregistré, vous recevrez un lien de réinitialisation.');
            header('Location: /admin/forgot-password');
            exit;

        } catch (Exception $e) {
            error_log('Forgot password error: ' . $e->getMessage());
            $error = 'Une erreur est survenue. Veuillez réessayer.';
        }
    }
}

require '../../admin/views/forgot-password.php';

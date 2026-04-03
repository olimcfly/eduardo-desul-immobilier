<?php
require_once '../../core/bootstrap.php';
require_once '../../core/services/MailService.php';

// Déjà connecté → rediriger vers le dashboard
if (Auth::check()) {
    header('Location: /admin/');
    exit;
}

$flash = Session::getFlash();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } else {
        try {
            $pdo = db();

            // Vérifier que l'email correspond à un admin ou superadmin
            $stmt = $pdo->prepare("SELECT id, name, role FROM users WHERE email = ? AND role IN ('admin', 'superadmin') LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Générer un code OTP à 6 chiffres
                $code     = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $codeHash = password_hash($code, PASSWORD_BCRYPT, ['cost' => 10]);
                $expires  = date('Y-m-d H:i:s', time() + 600); // 10 minutes

                // Supprimer les anciens OTPs de cet utilisateur
                $pdo->prepare("DELETE FROM admin_login_otps WHERE user_id = ?")->execute([$user['id']]);

                // Insérer le nouveau OTP
                $stmt = $pdo->prepare("
                    INSERT INTO admin_login_otps (user_id, email, code_hash, ip_address, user_agent, expires_at)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $user['id'],
                    $email,
                    $codeHash,
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                    $expires,
                ]);

                $otpId = $pdo->lastInsertId();

                // Stocker l'ID OTP en session pour la vérification
                $_SESSION['otp_pending_id']    = $otpId;
                $_SESSION['otp_pending_email'] = $email;

                // Envoyer le code par email
                MailService::send(
                    $email,
                    'Votre code de connexion — Eduardo Desul Admin',
                    "Bonjour {$user['name']},\n\nVotre code de connexion est : {$code}\n\nIl est valable 10 minutes.\n\nSi vous n'avez pas demandé ce code, ignorez cet email.",
                    "<p>Bonjour <strong>{$user['name']}</strong>,</p>
                    <p>Votre code de connexion est : <strong style='font-size:24px;letter-spacing:4px'>{$code}</strong></p>
                    <p>Il est valable <strong>10 minutes</strong>.</p>
                    <p style='color:#999;font-size:12px'>Si vous n'avez pas demandé ce code, ignorez cet email.</p>"
                );

                header('Location: /admin/verify-otp');
                exit;
            }

            // Email inconnu : on affiche le même message pour ne pas révéler les comptes
            Session::flash('success', 'Si cet email est enregistré, vous recevrez un code.');
            header('Location: /admin/verify-otp');
            exit;

        } catch (Exception $e) {
            error_log('Login OTP error: ' . $e->getMessage());
            $error = 'Une erreur est survenue. Veuillez réessayer.';
        }
    }
}

$flash = $flash ?? Session::getFlash();
require '../../admin/views/login.php';

<?php
require_once '../../core/bootstrap.php';

// Déjà connecté
if (Auth::check()) {
    header('Location: /admin/');
    exit;
}

// Pas d'OTP en attente → retour login
if (empty($_SESSION['otp_pending_id']) || empty($_SESSION['otp_pending_email'])) {
    header('Location: /admin/login');
    exit;
}

$error = null;
$email = $_SESSION['otp_pending_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code  = preg_replace('/\D/', '', $_POST['code'] ?? '');
    $otpId = (int)$_SESSION['otp_pending_id'];

    if (strlen($code) !== 6) {
        $error = 'Le code doit contenir 6 chiffres.';
    } else {
        try {
            $pdo = db();

            $stmt = $pdo->prepare("
                SELECT o.*, u.id as user_id, u.name, u.email as user_email, u.role
                FROM admin_login_otps o
                JOIN users u ON u.id = o.user_id
                WHERE o.id = ?
                  AND o.consumed_at IS NULL
                  AND o.expires_at > NOW()
                  AND o.attempt_count < o.max_attempts
                LIMIT 1
            ");
            $stmt->execute([$otpId]);
            $otp = $stmt->fetch();

            if ($otp && password_verify($code, $otp['code_hash'])) {
                if (!in_array($otp['role'], ['admin', 'superadmin'], true)) {
                    $error = 'Accès réservé aux administrateurs.';
                } else {
                // Marquer l'OTP comme consommé
                $pdo->prepare("UPDATE admin_login_otps SET consumed_at = NOW() WHERE id = ?")
                    ->execute([$otpId]);

                // Nettoyer la session OTP
                unset($_SESSION['otp_pending_id'], $_SESSION['otp_pending_email']);

                // Connecter l'utilisateur
                Auth::login([
                    'id'    => $otp['user_id'],
                    'email' => $otp['user_email'],
                    'role'  => $otp['role'],
                    'name'  => $otp['name'],
                ]);

                header('Location: /admin/');
                exit;
                }

            } else {
                // Incrémenter le compteur d'essais
                if ($otp) {
                    $pdo->prepare("UPDATE admin_login_otps SET attempt_count = attempt_count + 1 WHERE id = ?")
                        ->execute([$otpId]);
                }
                $error = 'Code invalide ou expiré.';
            }

        } catch (Exception $e) {
            error_log('OTP verify error: ' . $e->getMessage());
            $error = 'Une erreur est survenue. Veuillez réessayer.';
        }
    }
}

require '../../admin/views/verify-otp.php';

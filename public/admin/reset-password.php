<?php
require_once '../../core/bootstrap.php';

if (Auth::check()) {
    header('Location: /admin/');
    exit;
}

$token     = trim($_GET['token'] ?? '');
$tokenHash = $token !== '' ? hash('sha256', $token) : '';
$error     = null;
$tokenData = null;

// Vérifier le token
if ($tokenHash !== '') {
    try {
        $stmt = db()->prepare("
            SELECT prt.id, prt.user_id, prt.expires_at, prt.used_at, u.email, u.name
            FROM   password_reset_tokens prt
            JOIN   users u ON u.id = prt.user_id
            WHERE  prt.token_hash = ?
            LIMIT  1
        ");
        $stmt->execute([$tokenHash]);
        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tokenData) {
            $error = 'Lien invalide ou expiré.';
            $tokenData = null;
        } elseif ($tokenData['used_at'] !== null) {
            $error = 'Ce lien a déjà été utilisé.';
            $tokenData = null;
        } elseif (strtotime($tokenData['expires_at']) < time()) {
            $error = 'Ce lien a expiré. Faites une nouvelle demande.';
            $tokenData = null;
        }
    } catch (Exception $e) {
        error_log('Reset password token check: ' . $e->getMessage());
        $error = 'Une erreur est survenue.';
    }
} else {
    $error = 'Lien manquant.';
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenData !== null) {
    $password  = $_POST['password']  ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (strlen($password) < 8) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères.';
    } elseif ($password !== $password2) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        try {
            $pdo  = db();
            $hash = Auth::hashPassword($password);

            // Mettre à jour le mot de passe
            $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")
                ->execute([$hash, $tokenData['user_id']]);

            // Marquer le token comme utilisé
            $pdo->prepare("UPDATE password_reset_tokens SET used_at = NOW() WHERE id = ?")
                ->execute([$tokenData['id']]);

            Session::flash('success', 'Mot de passe mis à jour. Vous pouvez vous connecter.');
            header('Location: /admin/login');
            exit;

        } catch (Exception $e) {
            error_log('Reset password update: ' . $e->getMessage());
            $error = 'Une erreur est survenue. Veuillez réessayer.';
        }
    }
}

require '../../admin/views/reset-password.php';

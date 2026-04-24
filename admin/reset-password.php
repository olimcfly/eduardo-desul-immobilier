<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_functions.php';

if (isLoggedIn()) {
    header('Location: /admin/profile.php');
    exit;
}

$error = null;
$success = null;
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = "Token manquant.";
} else {
    $validation = validatePasswordResetToken($token);

    if (!$validation['success']) {
        $error = $validation['message'];
    } else {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = (string) ($_POST['password'] ?? '');
            $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

            if (empty($newPassword) || empty($confirmPassword)) {
                $error = "Veuillez remplir tous les champs.";
            } elseif ($newPassword !== $confirmPassword) {
                $error = "Les mots de passe ne correspondent pas.";
            } elseif (strlen($newPassword) < 8) {
                $error = "Le mot de passe doit contenir au moins 8 caractères.";
            } else {
                $result = updatePassword($validation['user_id'], $newPassword);
                if ($result['success']) {
                    $success = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.";
                } else {
                    $error = "Une erreur est survenue lors de la réinitialisation.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe - Eduardo Desul Immobilier</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: #fff;
            padding: 24px;
            border-radius: 12px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 8px 0 16px;
            border: 1px solid #ccd4df;
            border-radius: 8px;
        }
        button {
            width: 100%;
            background: #0052cc;
            color: #fff;
            border: 0;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        .error {
            color: #b42318;
            background: #ffe4e1;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 12px;
        }
        .success {
            color: #059669;
            background: #d1fae5;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Réinitialiser le mot de passe</h1>

        <?php if ($error !== null): ?>
            <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if ($success !== null): ?>
            <div class="success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
            <p style="text-align: center; margin-top: 20px;">
                <a href="/admin/login.php">Retour à la connexion</a>
            </p>
        <?php else: ?>
            <form method="post" autocomplete="off">
                <label for="password">Nouveau mot de passe</label>
                <input type="password" id="password" name="password" required minlength="8">

                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8">

                <button type="submit">Réinitialiser le mot de passe</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

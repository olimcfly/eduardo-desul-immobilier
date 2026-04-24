<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_functions.php';

if (isLoggedIn()) {
    header('Location: /admin/profile.php');
    exit;
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));

    if (empty($email)) {
        $error = "Veuillez entrer votre adresse email.";
    } else {
        $result = sendPasswordResetLink($email);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du mot de passe - Eduardo Desul Immobilier</title>
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
        <h1>Réinitialisation du mot de passe</h1>
        <p>Entrez votre adresse email pour recevoir un lien de réinitialisation.</p>

        <?php if ($error !== null): ?>
            <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if ($success !== null): ?>
            <div class="success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <label for="email">Adresse email</label>
            <input type="email" id="email" name="email" required>

            <button type="submit">Envoyer le lien de réinitialisation</button>
        </form>

        <p style="margin-top: 20px; text-align: center;">
            <a href="/admin/login.php">Retour à la connexion</a>
        </p>
    </div>
</body>
</html>

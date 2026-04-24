<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// ── Démarrer la session ──────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_name('edo_immo_sess');
    session_set_cookie_params([
        'lifetime' => 28800,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ── Si déjà connecté, rediriger ─────────────────────────────
if (!empty($_SESSION['user_id']) && !empty($_SESSION['user_role'])) {
    header('Location: /admin/');
    exit;
}

$error = null;
$email = '';

// ── Logique du login (formulaire POST) ───────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        // Identifiants de test (à remplacer par une vraie base de données)
        $adminEmail = 'admin@eduardo-desul.fr';
        $adminPassword = 'admin123'; // À CHANGER en production !
        
        if ($email === $adminEmail && $password === $adminPassword) {
            // Connexion réussie
            session_regenerate_id(true);
            $_SESSION['user_id']    = 1;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role']  = 'admin';
            $_SESSION['user_name']  = 'Administrateur';
            
            header('Location: /admin/');
            exit;
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - Eduardo Desul Immobilier</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 420px;
            padding: 40px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background-color: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background-color: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .forgot-password-link {
            text-align: right;
            margin-top: -8px;
            margin-bottom: 20px;
        }

        .forgot-password-link a {
            color: #667eea;
            font-size: 13px;
            text-decoration: none;
        }

        .forgot-password-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Eduardo Desul</h1>
            <p>Tableau de bord administrateur</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($email) ?>"
                    required
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                >
            </div>

            <div class="forgot-password-link">
                <a href="/admin/forgot-password.php">Mot de passe oublié ?</a>
            </div>

            <button type="submit" class="submit-btn">Connexion</button>
        </form>

        <div class="login-footer">
            <p style="color: #999; font-size: 12px;">
                © 2026 Eduardo Desul Immobilier - Tous droits réservés
            </p>
        </div>
    </div>
</body>
</html>

<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// ── Charger la fonction de gestion de session ────────────────
require_once __DIR__ . '/session-helper.php';

// ── Démarrer la session ──────────────────────────────────────
startAdminSession();

// ── Si déjà connecté, rediriger ─────────────────────────────
if (isAdminLoggedIn()) {
    redirectAdmin('/admin/');
}

$error = null;
$email = '';

// ── Charger les identifiants admin ──────────────────────────
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/mailer.php';

// ── Charger les variables d'environnement (.env) ─────────────
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (strlen($value) >= 2 && (($value[0] === '"' && $value[-1] === '"') || ($value[0] === "'" && $value[-1] === "'"))) {
            $value = substr($value, 1, -1);
        }
        $_ENV[$key] = $value;
    }
}

// ── Logique du login (formulaire POST) ───────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    error_log("[LOGIN DEBUG] Email: $email, Password length: " . strlen($password));

    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $user = null;

        // Étape 1: Chercher dans la base de données (si disponible)
        try {
            $dbHost = $_ENV['DB_HOST'] ?? $_ENV['DATABASE_HOST'] ?? 'localhost';
            $dbPort = $_ENV['DB_PORT'] ?? $_ENV['DATABASE_PORT'] ?? 3306;
            $dbName = $_ENV['DB_NAME'] ?? $_ENV['DB_DATABASE'] ?? $_ENV['DATABASE_NAME'] ?? '';
            $dbUser = $_ENV['DB_USER'] ?? $_ENV['DB_USERNAME'] ?? $_ENV['DATABASE_USER'] ?? '';
            $dbPass = $_ENV['DB_PASS'] ?? $_ENV['DB_PASSWORD'] ?? $_ENV['DATABASE_PASSWORD'] ?? '';
            
            if ($dbName && $dbUser) {
                $pdo = new PDO(
                    "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
                    $dbUser,
                    $dbPass,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                // Chercher l'utilisateur
                $stmt = $pdo->prepare('SELECT id, email, password, role, name FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                error_log("[LOGIN DEBUG] User found: " . ($user ? 'YES' : 'NO'));
                if ($user) {
                    $verify = password_verify($password, (string) $user['password']);
                    error_log("[LOGIN DEBUG] Password verify: " . ($verify ? 'YES' : 'NO'));
                }

                if ($user && password_verify($password, (string) $user['password'])) {
                    // Succès avec l'utilisateur de la BDD
                    session_regenerate_id(true);
                    $_SESSION['user_id']    = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role']  = $user['role'] ?? 'admin';
                    $_SESSION['user_name']  = $user['name'] ?? '';
                    $_SESSION['last_activity'] = time();

                    error_log("[LOGIN DEBUG] Session set, about to redirect to /admin/");

                    // Enregistrer la connexion et envoyer alerte
                    try {
                        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                        $pdo->prepare("INSERT INTO login_logs (user_email, ip_address, success) VALUES (?, ?, 1)")
                            ->execute([$email, $ip]);

                        // Envoyer alerte email
                        require_once __DIR__ . '/../includes/config.php';
                        if (function_exists('sendEmail')) {
                            sendEmail(
                                ADMIN_EMAIL,
                                'Administrateur',
                                "🔐 Connexion au tableau de bord - " . date('d/m/Y H:i:s'),
                                "<h3>Connexion réussie au tableau de bord</h3>
                                <p><strong>Email :</strong> $email</p>
                                <p><strong>Heure :</strong> " . date('d/m/Y H:i:s') . "</p>
                                <p><strong>Adresse IP :</strong> $ip</p>"
                            );
                        }
                    } catch (Throwable $e) {
                        error_log('Login alert error: ' . $e->getMessage());
                    }

                    redirectAdmin('/admin/');
                }
            }
        } catch (Exception $e) {
            error_log('[LOGIN DEBUG] DB Connection exception: ' . $e->getMessage());
        }
        
        // Étape 2: Fallback sur les defines du config.php
        error_log("[LOGIN DEBUG] Fallback check - ADMIN_EMAIL defined: " . (defined('ADMIN_EMAIL') ? 'YES' : 'NO'));
        error_log("[LOGIN DEBUG] Fallback check - ADMIN_PASSWORD_HASH defined: " . (defined('ADMIN_PASSWORD_HASH') ? 'YES' : 'NO'));
        error_log("[LOGIN DEBUG] Fallback check - Email match: " . ($email === ADMIN_EMAIL ? 'YES' : 'NO'));

        if (defined('ADMIN_EMAIL') && defined('ADMIN_PASSWORD_HASH')) {
            $fallbackVerify = password_verify($password, ADMIN_PASSWORD_HASH);
            error_log("[LOGIN DEBUG] Fallback password verify: " . ($fallbackVerify ? 'YES' : 'NO'));
        }

        if (
            defined('ADMIN_EMAIL') &&
            defined('ADMIN_PASSWORD_HASH') &&
            $email === ADMIN_EMAIL &&
            password_verify($password, ADMIN_PASSWORD_HASH)
        ) {
            // Connexion réussie avec le compte config
            session_regenerate_id(true);
            $_SESSION['user_id']    = 1;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role']  = 'superadmin';
            $_SESSION['user_name']  = 'Administrateur';
            $_SESSION['last_activity'] = time();
            
            redirectAdmin('/admin/');
        }
        
        // Authentification échouée
        $error = 'Email ou mot de passe incorrect.';
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

        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-wrapper input {
            flex: 1;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            cursor: pointer;
            color: #667eea;
            font-size: 18px;
            padding: 4px;
        }

        .toggle-password:hover {
            color: #764ba2;
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
            color: #999;
            font-size: 12px;
        }

        .test-credentials {
            background: #f0f4ff;
            border: 1px solid #d4dff5;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 12px;
        }

        .test-credentials strong {
            color: #333;
        }

        .test-credentials code {
            background: #fff;
            padding: 2px 4px;
            border-radius: 2px;
            font-family: monospace;
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

        <div class="test-credentials">
            <strong>Identifiant de connexion :</strong><br>
            Email: <code><?= htmlspecialchars(defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'admin@example.com') ?></code><br>
            <em>Utilisez votre mot de passe administrateur</em>
        </div>

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
                <div class="password-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                    >
                    <button type="button" class="toggle-password" id="togglePassword" aria-label="Afficher/masquer le mot de passe">
                        👁️
                    </button>
                </div>
            </div>

            <button type="submit" class="submit-btn">Connexion</button>
        </form>

        <div class="login-footer">
            © 2026 Eduardo Desul Immobilier - Tous droits réservés
        </div>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function(e) {
            e.preventDefault();
            const passwordInput = document.getElementById('password');
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            this.textContent = isPassword ? '🙈' : '👁️';
        });
    </script>
</body>
</html>

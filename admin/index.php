<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// ── Charger la fonction de gestion de session ────────────────
require_once __DIR__ . '/session-helper.php';

// ── Démarrer la session ──────────────────────────────────────
startAdminSession();

// ── Vérifier authentification ────────────────────────────────
if (!isAdminLoggedIn()) {
    redirectAdmin('/admin/login');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Eduardo Desul Immobilier</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f7fa;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .navbar h1 {
            font-size: 24px;
        }

        .navbar-actions {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .navbar-actions a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background 0.3s;
        }

        .navbar-actions a:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.3) !important;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.5) !important;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .card h2 {
            color: #333;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .card p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        .user-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .user-info h2 {
            color: #333;
            margin-bottom: 15px;
        }

        .user-info p {
            color: #666;
            margin: 8px 0;
            font-size: 14px;
        }

        .label {
            font-weight: 600;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Eduardo Desul Immobilier</h1>
        <div class="navbar-actions">
            <span><?= htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['user_email'] ?? 'Utilisateur') ?></span>
            <a href="/admin/profile.php">Profil</a>
            <a href="/admin/logout.php" class="logout-btn">Déconnexion</a>
        </div>
    </div>

    <div class="container">
        <div class="user-info">
            <h2>Bienvenue dans votre tableau de bord</h2>
            <p><span class="label">Email :</span> <?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></p>
            <p><span class="label">Rôle :</span> <?= htmlspecialchars(ucfirst($_SESSION['user_role'] ?? '')) ?></p>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <h2>📊 Statistiques</h2>
                <p>Consultez vos statistiques et performances en temps réel.</p>
            </div>

            <div class="card">
                <h2>📄 Contenu</h2>
                <p>Gérez vos pages, articles et contenus.</p>
            </div>

            <div class="card">
                <h2>⚙️ Paramètres</h2>
                <p>Configurez les paramètres de votre plateforme.</p>
            </div>
        </div>
    </div>
</body>
</html>

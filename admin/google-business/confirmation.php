<?php
declare(strict_types=1);

require_once __DIR__ . '/../session-helper.php';

startAdminSession();

if (!isAdminLoggedIn()) {
    redirectAdmin('/admin/auth/login.php');
}

$status = trim((string) ($_GET['status'] ?? 'connected'));
$errorMessage = trim((string) ($_GET['gbp_error'] ?? ''));
$isSuccess = $status === 'connected';
$title = $isSuccess ? 'Connexion validée' : 'Connexion Google Business Profile';
$headline = $isSuccess ? 'Votre compte Google est connecté.' : 'La connexion Google Business Profile a échoué.';
$message = $isSuccess
    ? 'Google a accepté l’autorisation. Vous pouvez revenir au module pour charger les comptes et sélectionner la fiche.'
    : ($errorMessage !== '' ? $errorMessage : 'Une erreur est survenue pendant le retour OAuth.');

?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f6f3ec;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --border: rgba(31, 41, 55, 0.12);
            --accent: #0f766e;
            --accent-soft: rgba(15, 118, 110, 0.12);
            --danger: #b91c1c;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at top, rgba(15, 118, 110, 0.12), transparent 32%),
                linear-gradient(180deg, #fcfaf6 0%, var(--bg) 100%);
            color: var(--text);
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .card {
            width: min(720px, calc(100vw - 32px));
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.12);
            padding: 32px;
        }
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .02em;
            margin-bottom: 18px;
        }
        h1 {
            margin: 0 0 12px;
            font-size: clamp(28px, 4vw, 42px);
            line-height: 1.05;
        }
        p {
            margin: 0;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.65;
        }
        .status {
            margin-top: 24px;
            padding: 18px;
            border-radius: 18px;
            background: <?= $isSuccess ? 'rgba(15,118,110,.08)' : 'rgba(185,28,28,.08)' ?>;
            border: 1px solid <?= $isSuccess ? 'rgba(15,118,110,.18)' : 'rgba(185,28,28,.18)' ?>;
        }
        .status strong {
            display: block;
            margin-bottom: 6px;
            color: <?= $isSuccess ? 'var(--accent)' : 'var(--danger)' ?>;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 0 18px;
            border-radius: 14px;
            border: 1px solid transparent;
            text-decoration: none;
            font-weight: 700;
            transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary {
            background: var(--accent);
            color: #fff;
            box-shadow: 0 12px 30px rgba(15, 118, 110, 0.22);
        }
        .btn-secondary {
            background: #fff;
            color: var(--text);
            border-color: var(--border);
        }
        .hint {
            margin-top: 20px;
            font-size: 14px;
            color: var(--muted);
        }
        code {
            padding: 2px 6px;
            border-radius: 6px;
            background: #f3f4f6;
            color: #111827;
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="eyebrow">Google Business Profile</div>
        <h1><?= htmlspecialchars($headline, ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>

        <div class="status">
            <strong><?= $isSuccess ? 'Confirmation reçue' : 'Retour OAuth en erreur' ?></strong>
            <p>
                <?= $isSuccess
                    ? 'Le flux OAuth s’est terminé correctement côté application.'
                    : 'Vérifiez la configuration OAuth Google et réessayez.' ?>
            </p>
        </div>

        <div class="actions">
            <a class="btn btn-primary" href="/admin/?module=gmb&view=google-business-profile">Retour au module</a>
            <a class="btn btn-secondary" href="/admin/">Aller au tableau de bord</a>
        </div>

        <div class="hint">
            Si Google affiche encore une alerte <code>app_notverified</code> avant ce point, il faut valider l’écran de consentement dans Google Cloud ou ajouter le compte comme testeur.
        </div>
    </main>
</body>
</html>

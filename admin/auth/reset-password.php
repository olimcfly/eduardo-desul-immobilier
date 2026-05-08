<?php
declare(strict_types=1);

$token = (string) ($_GET['token'] ?? '');
$message = $token !== '' ? 'La réinitialisation automatique n’est pas activée sur cette installation.' : 'Token manquant.';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Réinitialiser le mot de passe</title>
    <style>
        body{font-family:Arial,sans-serif;background:#f4f7fb;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px}
        .card{background:#fff;padding:24px;border-radius:12px;max-width:420px;width:100%;box-shadow:0 6px 24px rgba(0,0,0,.08)}
        .notice{background:#eef2ff;color:#1d4ed8;padding:10px;border-radius:8px;margin:12px 0 16px}
    </style>
</head>
<body>
<div class="card">
    <h1>Réinitialiser le mot de passe</h1>
    <div class="notice"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
    <p style="margin-top:20px;text-align:center;"><a href="/admin/auth/login.php">Retour à la connexion</a></p>
</div>
</body>
</html>

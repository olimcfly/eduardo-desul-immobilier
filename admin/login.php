<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) {
    header('Location: /admin/profile.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $result = login($email, $password);
    if ($result['success'] === true) {
        if (!empty($result['force_password_reset'])) {
            header('Location: /admin/profile.php?first_login=1');
            exit;
        }

        header('Location: /admin/profile.php');
        exit;
    }

    $error = $result['message'];
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion Admin</title>
    <style>
        body{font-family:Arial,sans-serif;background:#f4f7fb;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
        .card{background:#fff;padding:24px;border-radius:12px;max-width:420px;width:100%;box-shadow:0 6px 24px rgba(0,0,0,.08)}
        input{width:100%;padding:12px;margin:8px 0 16px;border:1px solid #ccd4df;border-radius:8px}
        button{width:100%;background:#0052cc;color:#fff;border:0;padding:12px;border-radius:8px;font-weight:600;cursor:pointer}
        .error{color:#b42318;background:#ffe4e1;padding:10px;border-radius:8px;margin-bottom:12px}
    </style>
</head>
<body>
<div class="card">
    <h1>Administration</h1>
    <?php if ($error !== null): ?>
        <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form method="post" autocomplete="off">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" required minlength="8">

        <button type="submit">Se connecter</button>
    </form>
</div>
</body>
</html>

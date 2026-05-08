<?php
declare(strict_types=1);

$error = null;
$success = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Veuillez entrer une adresse email valide.';
    } else {
        $success = 'La réinitialisation automatique n’est pas configurée sur cette installation. Utilisez le compte admin ou contactez le support.';
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mot de passe oublié</title>
    <style>
        body{font-family:Arial,sans-serif;background:#f4f7fb;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px}
        .card{background:#fff;padding:24px;border-radius:12px;max-width:420px;width:100%;box-shadow:0 6px 24px rgba(0,0,0,.08)}
        input,button{width:100%;padding:12px;border-radius:8px}
        input{border:1px solid #ccd4df;margin:8px 0 16px}
        button{background:#0052cc;color:#fff;border:0;font-weight:600;cursor:pointer}
        .error{color:#b42318;background:#ffe4e1;padding:10px;border-radius:8px;margin-bottom:12px}
        .success{color:#059669;background:#d1fae5;padding:10px;border-radius:8px;margin-bottom:12px}
    </style>
</head>
<body>
<div class="card">
    <h1>Mot de passe oublié</h1>
    <p>Cette installation ne dispose pas encore du flux d’envoi automatique de lien de réinitialisation.</p>
    <?php if ($error !== null): ?><div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
    <?php if ($success !== null): ?><div class="success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
    <form method="post">
        <label for="email">Adresse email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
        <button type="submit">Vérifier</button>
    </form>
    <p style="margin-top:20px;text-align:center;"><a href="/admin/auth/login.php">Retour à la connexion</a></p>
</div>
</body>
</html>

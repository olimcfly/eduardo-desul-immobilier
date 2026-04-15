<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

requireAdminAuth();

$success = null;
$error = null;
$firstLogin = isset($_GET['first_login']) || isPasswordResetRequired();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'email') {
        $newEmail = (string) ($_POST['new_email'] ?? '');
        $result = updateAdminEmail($newEmail);
        $success = $result['success'] ? $result['message'] : null;
        $error = !$result['success'] ? $result['message'] : null;
    }

    if ($action === 'password') {
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        if ($newPassword !== $confirmPassword) {
            $error = 'La confirmation du mot de passe ne correspond pas.';
        } else {
            $result = updateAdminPassword($currentPassword, $newPassword);
            $success = $result['success'] ? $result['message'] : null;
            $error = !$result['success'] ? $result['message'] : null;
            if ($result['success']) {
                $firstLogin = false;
            }
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil administrateur</title>
    <style>
        body{font-family:Arial,sans-serif;background:#f7fafc;margin:0;padding:20px}
        .wrap{max-width:780px;margin:0 auto;background:#fff;border-radius:12px;padding:22px;box-shadow:0 6px 24px rgba(0,0,0,.07)}
        .grid{display:grid;grid-template-columns:1fr;gap:18px}
        @media (min-width:900px){.grid{grid-template-columns:1fr 1fr}}
        .card{border:1px solid #e5e7eb;border-radius:12px;padding:16px}
        input{width:100%;padding:10px;margin-top:6px;margin-bottom:12px;border:1px solid #cfd8e3;border-radius:8px}
        button,a.btn{background:#0052cc;color:#fff;text-decoration:none;border:0;border-radius:8px;padding:10px 14px;display:inline-block;cursor:pointer}
        .warning{background:#fff7d6;color:#7a5d00;padding:10px;border-radius:8px;margin-bottom:12px}
        .success{background:#ddf7e5;color:#146c2e;padding:10px;border-radius:8px;margin-bottom:12px}
        .error{background:#ffe4e1;color:#9f1c12;padding:10px;border-radius:8px;margin-bottom:12px}
    </style>
</head>
<body>
<div class="wrap">
    <h1>Profil administrateur</h1>

    <?php if ($firstLogin): ?>
        <div class="warning">Vous utilisez un mot de passe temporaire. Merci de le changer immédiatement.</div>
    <?php endif; ?>

    <?php if ($success): ?><div class="success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>

    <div class="grid">
        <div class="card">
            <h2>Changer l'email</h2>
            <form method="post">
                <input type="hidden" name="action" value="email">
                <label for="new_email">Nouvel email</label>
                <input type="email" id="new_email" name="new_email" value="<?php echo htmlspecialchars((string) ($_SESSION['admin_email'] ?? ADMIN_EMAIL), ENT_QUOTES, 'UTF-8'); ?>" required>
                <button type="submit">Mettre à jour l'email</button>
            </form>
        </div>

        <div class="card">
            <h2>Changer le mot de passe</h2>
            <form method="post">
                <input type="hidden" name="action" value="password">
                <label for="current_password">Mot de passe actuel</label>
                <input type="password" id="current_password" name="current_password" required>
                <label for="new_password">Nouveau mot de passe</label>
                <input type="password" id="new_password" name="new_password" minlength="8" required>
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
                <button type="submit">Mettre à jour le mot de passe</button>
            </form>
        </div>
    </div>

    <p style="margin-top:16px"><a class="btn" href="/admin/logout.php">Se déconnecter</a></p>
</div>
</body>
</html>

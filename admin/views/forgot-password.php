<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié — Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #0f2237; min-height: 100vh;
            display: flex; align-items: center; justify-content: center; padding: 1rem;
        }
        .login-wrap { width: 100%; max-width: 420px; }
        .login-brand { text-align: center; margin-bottom: 2rem; }
        .login-brand__logo {
            display: inline-flex; align-items: center; justify-content: center;
            width: 64px; height: 64px; background: #c9a84c; border-radius: 16px; margin-bottom: 1rem;
        }
        .login-brand__logo svg { width: 32px; height: 32px; color: #fff; }
        .login-brand h1 { font-family: 'Georgia', serif; font-size: 1.35rem; color: #fff; font-weight: 600; }
        .login-brand p { font-size: .82rem; color: #7a9ab8; margin-top: .25rem; }
        .login-card { background: #1a3c5e; border-radius: 16px; padding: 2rem; box-shadow: 0 24px 64px rgba(0,0,0,.45); }
        .flash { padding: .75rem 1rem; border-radius: 8px; font-size: .875rem; margin-bottom: 1.25rem; display: flex; align-items: flex-start; gap: .5rem; }
        .flash--error   { background: rgba(220,53,69,.15); color: #f87171; border: 1px solid rgba(220,53,69,.3); }
        .flash--success { background: rgba(34,197,94,.15); color: #4ade80; border: 1px solid rgba(34,197,94,.3); }
        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; font-size: .8rem; font-weight: 600; color: #a8c4dc; margin-bottom: .4rem; letter-spacing: .04em; text-transform: uppercase; }
        .form-input { width: 100%; padding: .75rem 1rem; background: #0f2237; border: 1.5px solid #2a5278; border-radius: 8px; color: #e8f1f9; font-size: .95rem; outline: none; transition: border-color .2s, box-shadow .2s; }
        .form-input:focus { border-color: #c9a84c; box-shadow: 0 0 0 3px rgba(201,168,76,.15); }
        .form-input::placeholder { color: #3d6585; }
        .btn-login { width: 100%; padding: .875rem; background: #c9a84c; color: #0f2237; font-size: .95rem; font-weight: 700; border: none; border-radius: 8px; cursor: pointer; margin-top: .5rem; transition: background .2s, transform .1s; }
        .btn-login:hover { background: #dbb95a; }
        .hint { margin-top: .75rem; color: #7a9ab8; font-size: .8rem; line-height: 1.5; }
        .login-footer { text-align: center; margin-top: 1.5rem; font-size: .78rem; color: #3d6585; }
        .login-footer a { color: #5a8ab0; text-decoration: none; }
        .login-footer a:hover { color: #c9a84c; }
    </style>
</head>
<body>
<div class="login-wrap">

    <div class="login-brand">
        <div class="login-brand__logo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/>
                <path d="M9 21V12h6v9"/>
            </svg>
        </div>
        <h1><?= htmlspecialchars(defined('APP_NAME') ? APP_NAME : 'Administration') ?></h1>
        <p>Réinitialisation du mot de passe</p>
    </div>

    <div class="login-card">

        <?php if (!empty($error)): ?>
        <div class="flash flash--error"><?= htmlspecialchars($error) ?></div>
        <?php elseif (!empty($flash)): ?>
        <div class="flash flash--<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>

        <form method="POST" action="/admin/forgot-password" novalidate>
            <div class="form-group">
                <label class="form-label" for="email">Votre adresse e-mail admin</label>
                <input id="email" name="email" type="email" class="form-input"
                       placeholder="vous@example.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       autocomplete="email" required autofocus>
            </div>
            <button type="submit" class="btn-login">Envoyer le lien de réinitialisation</button>
            <p class="hint">Vous recevrez un email avec un lien valable 1 heure.</p>
        </form>

    </div>

    <div class="login-footer">
        <a href="/admin/login">&larr; Retour à la connexion</a>
    </div>

</div>
</body>
</html>

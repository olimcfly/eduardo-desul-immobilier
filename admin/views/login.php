<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #0f2237;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-wrap { width: 100%; max-width: 420px; }

        .login-brand { text-align: center; margin-bottom: 2rem; }
        .login-brand__logo {
            display: inline-flex; align-items: center; justify-content: center;
            width: 64px; height: 64px; background: #c9a84c; border-radius: 16px; margin-bottom: 1rem;
        }
        .login-brand__logo svg { width: 32px; height: 32px; color: #fff; }
        .login-brand h1 {
            font-family: 'Georgia', serif; font-size: 1.35rem; color: #ffffff;
            font-weight: 600; letter-spacing: .01em;
        }
        .login-brand p { font-size: .82rem; color: #7a9ab8; margin-top: .25rem; }

        .login-card {
            background: #1a3c5e; border-radius: 16px; padding: 2rem;
            box-shadow: 0 24px 64px rgba(0,0,0,.45);
        }

        .flash {
            padding: .75rem 1rem; border-radius: 8px; font-size: .875rem;
            margin-bottom: 1.25rem; display: flex; align-items: center; gap: .5rem;
        }
        .flash--error   { background: rgba(220,53,69,.15); color: #f87171; border: 1px solid rgba(220,53,69,.3); }
        .flash--success { background: rgba(34,197,94,.15); color: #4ade80; border: 1px solid rgba(34,197,94,.3); }

        .form-group { margin-bottom: 1.25rem; }
        .form-label {
            display: block; font-size: .8rem; font-weight: 600; color: #a8c4dc;
            margin-bottom: .4rem; letter-spacing: .04em; text-transform: uppercase;
        }
        .form-input {
            width: 100%; padding: .75rem 1rem; background: #0f2237;
            border: 1.5px solid #2a5278; border-radius: 8px; color: #e8f1f9;
            font-size: .95rem; transition: border-color .2s, box-shadow .2s; outline: none;
        }
        .form-input::placeholder { color: #3d6585; }
        .form-input:focus {
            border-color: #c9a84c; box-shadow: 0 0 0 3px rgba(201,168,76,.15);
        }

        .password-wrap { position: relative; }
        .password-toggle {
            position: absolute; right: .75rem; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: #7a9ab8; cursor: pointer;
            padding: .25rem; display: flex; align-items: center;
        }
        .password-toggle:hover { color: #c9a84c; }

        .forgot-link {
            display: block; text-align: right; font-size: .78rem;
            color: #7a9ab8; text-decoration: none; margin-top: .4rem;
            transition: color .2s;
        }
        .forgot-link:hover { color: #c9a84c; }

        .btn-login {
            width: 100%; padding: .875rem; background: #c9a84c; color: #0f2237;
            font-size: .95rem; font-weight: 700; border: none; border-radius: 8px;
            cursor: pointer; letter-spacing: .03em; transition: background .2s, transform .1s;
            margin-top: .5rem;
        }
        .btn-login:hover  { background: #dbb95a; }
        .btn-login:active { transform: scale(.98); }

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
        <p>Espace d'administration</p>
    </div>

    <div class="login-card">

        <?php if (!empty($error)): ?>
        <div class="flash flash--error" role="alert">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php elseif (!empty($flash)): ?>
        <div class="flash flash--<?= htmlspecialchars($flash['type']) ?>" role="alert">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="/admin/login" novalidate autocomplete="on">

            <div class="form-group">
                <label class="form-label" for="email">Adresse e-mail</label>
                <input
                    id="email" name="email" type="email"
                    class="form-input"
                    placeholder="vous@example.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    autocomplete="email"
                    required autofocus
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Mot de passe</label>
                <div class="password-wrap">
                    <input
                        id="password" name="password" type="password"
                        class="form-input"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="password-toggle" id="pwToggle"
                            aria-label="Afficher/masquer le mot de passe">
                        <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                <a href="/admin/forgot-password" class="forgot-link">Mot de passe oublié ?</a>
            </div>

            <button type="submit" class="btn-login">Se connecter</button>

        </form>
    </div>

    <div class="login-footer">
        <a href="/">&larr; Retour au site</a>
        &nbsp;&nbsp;·&nbsp;&nbsp;
        <?php if (defined('APP_URL') && APP_URL !== ''): ?>
        <a href="<?= htmlspecialchars(rtrim(APP_URL, '/') . '/webmail') ?>" target="_blank" rel="noopener" style="opacity:.5;font-size:.72rem">
            Webmail
        </a>
        <?php endif; ?>
    </div>

</div>

<script>
    document.getElementById('pwToggle').addEventListener('click', function () {
        const input = document.getElementById('password');
        const icon  = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/>' +
                '<path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>' +
                '<line x1="1" y1="1" x2="23" y2="23"/>';
        } else {
            input.type = 'password';
            icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        }
    });
</script>

</body>
</html>

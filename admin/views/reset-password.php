<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe — Admin</title>
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
        .password-wrap { position: relative; }
        .form-input { width: 100%; padding: .75rem 1rem; background: #0f2237; border: 1.5px solid #2a5278; border-radius: 8px; color: #e8f1f9; font-size: .95rem; outline: none; transition: border-color .2s, box-shadow .2s; }
        .form-input:focus { border-color: #c9a84c; box-shadow: 0 0 0 3px rgba(201,168,76,.15); }
        .form-input::placeholder { color: #3d6585; }
        .password-toggle { position: absolute; right: .75rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #7a9ab8; cursor: pointer; padding: .25rem; display: flex; align-items: center; }
        .password-toggle:hover { color: #c9a84c; }
        .strength-bar { height: 4px; border-radius: 2px; margin-top: .4rem; background: #0f2237; overflow: hidden; }
        .strength-bar__fill { height: 100%; width: 0; border-radius: 2px; transition: width .3s, background .3s; }
        .strength-hint { font-size: .72rem; color: #7a9ab8; margin-top: .3rem; }
        .btn-login { width: 100%; padding: .875rem; background: #c9a84c; color: #0f2237; font-size: .95rem; font-weight: 700; border: none; border-radius: 8px; cursor: pointer; margin-top: .5rem; transition: background .2s, transform .1s; }
        .btn-login:hover { background: #dbb95a; }
        .btn-login:disabled { opacity: .5; cursor: not-allowed; }
        .invalid-token { text-align: center; color: #f87171; padding: 1rem 0; }
        .invalid-token svg { width: 40px; height: 40px; margin-bottom: .75rem; opacity: .7; }
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
        <p>Définir un nouveau mot de passe</p>
    </div>

    <div class="login-card">

        <?php if (!empty($error) && $tokenData === null): ?>
        <!-- Token invalide / expiré -->
        <div class="invalid-token">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                 stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <p><?= htmlspecialchars($error) ?></p>
        </div>
        <div style="text-align:center;margin-top:1.25rem">
            <a href="/admin/forgot-password"
               style="color:#c9a84c;font-size:.9rem;font-weight:600;text-decoration:none">
                Faire une nouvelle demande
            </a>
        </div>

        <?php elseif ($tokenData !== null): ?>
        <!-- Formulaire de reset -->
        <?php if (!empty($error)): ?>
        <div class="flash flash--error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST"
              action="/admin/reset-password?token=<?= urlencode($token) ?>"
              novalidate id="resetForm">

            <div class="form-group">
                <label class="form-label" for="password">Nouveau mot de passe</label>
                <div class="password-wrap">
                    <input id="password" name="password" type="password"
                           class="form-input" placeholder="Minimum 8 caractères"
                           autocomplete="new-password" required minlength="8"
                           oninput="checkStrength(this.value)">
                    <button type="button" class="password-toggle"
                            onclick="togglePw('password', this)">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                <div class="strength-bar"><div class="strength-bar__fill" id="strengthFill"></div></div>
                <div class="strength-hint" id="strengthHint">Au moins 8 caractères</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password2">Confirmer le mot de passe</label>
                <div class="password-wrap">
                    <input id="password2" name="password2" type="password"
                           class="form-input" placeholder="Répétez le mot de passe"
                           autocomplete="new-password" required>
                    <button type="button" class="password-toggle"
                            onclick="togglePw('password2', this)">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login" id="submitBtn">
                Enregistrer le nouveau mot de passe
            </button>

        </form>
        <?php endif; ?>

    </div>

    <div class="login-footer">
        <a href="/admin/login">&larr; Retour à la connexion</a>
    </div>

</div>

<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}

function checkStrength(val) {
    const fill = document.getElementById('strengthFill');
    const hint = document.getElementById('strengthHint');
    let score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
        { w: '0%',   bg: 'transparent', label: 'Au moins 8 caractères' },
        { w: '25%',  bg: '#ef4444',     label: 'Très faible' },
        { w: '50%',  bg: '#f97316',     label: 'Faible' },
        { w: '75%',  bg: '#eab308',     label: 'Moyen' },
        { w: '90%',  bg: '#22c55e',     label: 'Fort' },
        { w: '100%', bg: '#16a34a',     label: 'Très fort' },
    ];
    const l = levels[Math.min(score, 5)];
    fill.style.width      = val.length ? l.w : '0%';
    fill.style.background = l.bg;
    hint.textContent      = val.length ? l.label : 'Au moins 8 caractères';
}
</script>

</body>
</html>

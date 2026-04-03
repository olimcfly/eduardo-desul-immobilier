<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification OTP — Admin <?= htmlspecialchars(trim((string) setting('advisor_firstname', '') . ' ' . (string) setting('advisor_lastname', '')) ?: (ADVISOR_NAME ?: APP_NAME)) ?></title>
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
        .box {
            width: 100%;
            max-width: 420px;
            background: #1a3c5e;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 24px 64px rgba(0,0,0,.45);
        }
        h1 { color: #fff; font-size: 1.2rem; margin-bottom: .4rem; }
        p  { color: #a8c4dc; font-size: .9rem; margin-bottom: 1.25rem; }
        .flash {
            padding: .75rem 1rem; border-radius: 8px; font-size: .875rem;
            margin-bottom: 1.25rem;
        }
        .flash--error   { background: rgba(220,53,69,.15); color: #f87171; border: 1px solid rgba(220,53,69,.3); }
        .flash--success { background: rgba(34,197,94,.15); color: #4ade80; border: 1px solid rgba(34,197,94,.3); }
        .field { margin-bottom: 1rem; }
        label {
            display: block;
            font-size: .8rem;
            margin-bottom: .35rem;
            color: #a8c4dc;
            text-transform: uppercase;
            letter-spacing: .04em;
            font-weight: 600;
        }
        input {
            width: 100%;
            padding: .75rem 1rem;
            border-radius: 8px;
            border: 1.5px solid #2a5278;
            background: #0f2237;
            color: #e8f1f9;
            font-size: .95rem;
            outline: none;
        }
        input:focus {
            border-color: #c9a84c;
            box-shadow: 0 0 0 3px rgba(201,168,76,.15);
        }
        .otp {
            text-align: center;
            letter-spacing: .35em;
            font-weight: 700;
            font-size: 1.1rem;
        }
        button {
            width: 100%;
            margin-top: .5rem;
            padding: .85rem;
            background: #c9a84c;
            color: #0f2237;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
        }
        .links {
            margin-top: 1rem;
            display: flex;
            justify-content: space-between;
            gap: .75rem;
            font-size: .85rem;
        }
        .links a { color: #7a9ab8; text-decoration: none; }
        .links a:hover { color: #c9a84c; }
    </style>
</head>
<body>
<div class="box">
    <h1>Vérification OTP</h1>
    <p>Entrez le code à 6 chiffres envoyé à <strong><?= e($email ?? '') ?></strong>.</p>

    <?php if (!empty($error)): ?>
        <div class="flash flash--error" role="alert"><?= e($error) ?></div>
    <?php elseif (!empty($flash)): ?>
        <div class="flash flash--<?= e($flash['type']) ?>" role="alert"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <form method="POST" action="/admin/verify-otp" novalidate>
        <div class="field">
            <label for="code">Code OTP</label>
            <input id="code" name="code" class="otp" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="000000" required autocomplete="one-time-code" autofocus>
        </div>
        <button type="submit">Valider le code</button>
    </form>

    <div class="links">
        <a href="/admin/login">Renvoyer un code</a>
        <a href="/">Retour au site</a>
    </div>
</div>
</body>
</html>

<?php
/**
 * 🔐 ADMIN LOGIN PREMIUM
 * /admin/login.php
 */

/* ─────────────────────────────────────────
   Charger configuration
───────────────────────────────────────── */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/includes/classes/EmailService.php';

/* Déjà connecté */
$alreadyLoggedIn = !empty($_SESSION['auth_admin_id']) && !empty($_SESSION['auth_admin_email']) && !empty($_SESSION['auth_admin_logged_in']);

/* ─────────────────────────────────────────
   Connexion base
───────────────────────────────────────── */

if (!class_exists('Database')) require_once ROOT_PATH . '/includes/classes/Database.php';
try {
    if (!class_exists('Database')) require_once ROOT_PATH . '/includes/classes/Database.php';
    $db = Database::getInstance();
} catch (Exception $e) {
    die("Impossible de se connecter à la base de données");
}

/* ─────────────────────────────────────────
   Fonctions
───────────────────────────────────────── */

function sanitize($input, $type = 'string') {

    if ($type === 'email') {
        return filter_var($input, FILTER_SANITIZE_EMAIL);
    }

    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function sendOTPEmail($to, $otp) {

    $subject = '[' . SITE_TITLE . '] Code de connexion sécurisé';

    $message  = "Bonjour,\n\n";
    $message .= "Votre code de connexion est : $otp\n\n";
    $message .= "Ce code est valide pendant 10 minutes.\n\n";
    $message .= SITE_URL . "\n\n";
    $message .= "Si vous n'avez pas demandé ce code, ignorez ce message.";

    $headers = "From: " . ADMIN_EMAIL . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // 1) Priorité SMTP si config présente (diagnostic explicite)
    $smtpConfigFile = ROOT_PATH . '/config/smtp.php';
    if (file_exists($smtpConfigFile)) {
        try {
            $emailService = new EmailService();
            $result = $emailService->sendEmail(
                $to,
                $subject,
                nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')),
                [
                    'from_name'  => SITE_TITLE,
                    'reply_to'   => ADMIN_EMAIL,
                ]
            );

            if (!empty($result['success'])) {
                writeLog("OTP envoyé via SMTP à {$to}", 'INFO');
                return ['success' => true, 'transport' => 'smtp'];
            }

            $smtpError = $result['error'] ?? 'Erreur SMTP inconnue';
            writeLog("Échec SMTP OTP pour {$to}: {$smtpError}", 'ERROR');

            // IMPORTANT: pas de fallback mail() si un SMTP est configuré.
            // Objectif: éviter le faux positif "code envoyé" alors que
            // le serveur local accepte mail() sans délivrer réellement.
            writeLog("OTP non envoyé: fallback mail() désactivé (SMTP configuré)", 'WARNING');
            return ['success' => false, 'transport' => 'smtp', 'error' => $smtpError];
        } catch (Throwable $e) {
            writeLog("Exception SMTP OTP pour {$to}: " . $e->getMessage(), 'ERROR');
            return ['success' => false, 'transport' => 'smtp_exception', 'error' => $e->getMessage()];
        }
    }

    // 2) Sans SMTP, on refuse l'envoi OTP pour éviter les faux positifs mail().
    // Le mode OTP email doit s'appuyer sur un SMTP vérifié.
    writeLog("OTP non envoyé: config SMTP absente", 'ERROR');
    return ['success' => false, 'transport' => 'none', 'error' => 'Configuration SMTP absente'];
}

/* ─────────────────────────────────────────
   Variables
───────────────────────────────────────── */

$error = '';
$success = '';
$step = $_POST['step'] ?? 'email';

/* ─────────────────────────────────────────
   Traitement formulaire
───────────────────────────────────────── */

if (!$alreadyLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {

    /* Étape 1 : Email */

    if ($step === 'email') {

        $email = sanitize($_POST['email'] ?? '', 'email');
        if (!$email || !isValidEmail($email)) {

            $error = "Email invalide";

        } else {

            $stmt = $db->prepare("SELECT id, email, role, name, is_active FROM admins WHERE email=? LIMIT 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if (!$admin) {

                $error = "Email non reconnu";

            } elseif (isset($admin['is_active']) && !$admin['is_active']) {

                $error = "Compte désactivé. Contactez le Super Administrateur.";

            } else {

                $otp = str_pad(random_int(0,999999),6,'0',STR_PAD_LEFT);

                $_SESSION['auth_otp'] = $otp;
                $_SESSION['auth_otp_email'] = $email;
                $_SESSION['auth_otp_time'] = time();

                $sendResult = sendOTPEmail($email, $otp);

                if (!empty($sendResult['success'])) {
                    $success = "Code envoyé par email";
                    $step = "otp";
                } else {
                    $error = "Impossible d'envoyer le code de connexion. Vérifiez la configuration SMTP/env (outil: /diagnostic-smtp.php).";
                    if (!empty($sendResult['error'])) {
                        $error .= " Détail: " . $sendResult['error'];
                    }
                    $step = "email";
                }
            }
        }
    }

    /* Étape 2 : OTP */

    elseif ($step === 'otp') {

        $otp = sanitize($_POST['otp'] ?? '');

        if (!isset($_SESSION['auth_otp'])) {

            $error = "Session expirée";
            $step='email';

        }

        elseif (time() - $_SESSION['auth_otp_time'] > 600) {

            $error="Code expiré";
            $step='email';

            unset($_SESSION['auth_otp']);
        }

        elseif ($otp !== $_SESSION['auth_otp']) {

            $error="Code incorrect";

        }

        else {

            $stmt=$db->prepare("SELECT id, email, role, name FROM admins WHERE email=? LIMIT 1");
            $stmt->execute([$_SESSION['auth_otp_email']]);
            $admin=$stmt->fetch();

            $_SESSION['auth_admin_id']=$admin['id'];
            $_SESSION['auth_admin_email']=$admin['email'];
            $_SESSION['auth_admin_role']=$admin['role'] ?? 'admin';
            $_SESSION['auth_admin_name']=$admin['name'] ?? '';
            $_SESSION['auth_admin_logged_in']=true;
            $_SESSION['auth_admin_login_time']=time();
            session_regenerate_id(true);

            // Mettre à jour last_login
            $db->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);

            unset($_SESSION['auth_otp']);
            unset($_SESSION['auth_otp_email']);
            unset($_SESSION['auth_otp_time']);

            header("Location:/admin/dashboard.php");
            exit;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Connexion administration</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* ── RESET & BASE ───────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --primary:       #2563eb;
    --primary-dark:  #1d4ed8;
    --primary-light: #eff6ff;
    --success:       #16a34a;
    --danger:        #dc2626;
    --text:          #111827;
    --text-muted:    #6b7280;
    --border:        #e5e7eb;
    --bg-light:      #f9fafb;
    --white:         #ffffff;
    --shadow-lg:     0 8px 40px rgba(0,0,0,.12);
    --radius:        16px;
}

body.auth-body {
    font-family: 'Inter', system-ui, sans-serif;
    background: var(--bg-light);
    min-height: 100vh;
    display: flex;
    align-items: stretch;
    color: var(--text);
    -webkit-font-smoothing: antialiased;
}

.auth-wrapper {
    display: grid;
    grid-template-columns: 1fr 1fr;
    width: 100%;
    min-height: 100vh;
}

.auth-brand {
    background: linear-gradient(145deg, #1e3a8a 0%, #2563eb 60%, #3b82f6 100%);
    color: #fff;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 3rem;
    position: relative;
    overflow: hidden;
}
.auth-brand::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='28'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
}
.auth-brand__inner { position: relative; z-index: 1; }
.auth-brand__logo {
    width: 64px;
    height: 64px;
    background: rgba(255,255,255,.15);
    border-radius: 16px;
    display: grid;
    place-items: center;
    font-size: 1.75rem;
    margin-bottom: 2rem;
    backdrop-filter: blur(10px);
}
.auth-brand h1 {
    font-size: 1.9rem;
    font-weight: 800;
    margin-bottom: .75rem;
    line-height: 1.2;
}
.auth-brand > .auth-brand__inner > p {
    font-size: 1rem;
    opacity: .88;
    line-height: 1.7;
    margin-bottom: 2.5rem;
}
.auth-brand__info-box {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: rgba(255,255,255,.12);
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 2rem;
    backdrop-filter: blur(10px);
}
.info-box__icon {
    width: 44px;
    height: 44px;
    background: rgba(255,255,255,.2);
    border-radius: 10px;
    display: grid;
    place-items: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.auth-brand__info-box strong {
    display: block;
    font-size: .8rem;
    opacity: .7;
    margin-bottom: .2rem;
}
.auth-brand__info-box span { font-size: .95rem; font-weight: 600; }
.auth-brand__tips {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: .65rem;
}
.auth-brand__tips li {
    display: flex;
    align-items: center;
    gap: .6rem;
    font-size: .85rem;
    opacity: .85;
}
.auth-brand__footer {
    position: relative;
    z-index: 1;
    font-size: .78rem;
    opacity: .7;
    text-align: center;
    padding-top: 2rem;
    border-top: 1px solid rgba(255,255,255,.15);
}

.auth-form-side {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background: var(--bg-light);
}

.auth-card {
    background: var(--white);
    border-radius: var(--radius);
    padding: 2.5rem 2rem;
    box-shadow: var(--shadow-lg);
    width: 100%;
    max-width: 420px;
    border: 1px solid var(--border);
}
.auth-card__header { text-align: center; margin-bottom: 2rem; }
.auth-card__icon {
    width: 64px;
    height: 64px;
    background: var(--primary-light);
    border-radius: 16px;
    display: grid;
    place-items: center;
    font-size: 1.6rem;
    color: var(--primary);
    margin: 0 auto 1.25rem;
}
.auth-card__icon--verify { background: #fef3c7; color: #d97706; }
.auth-card__header h2 { font-size: 1.5rem; font-weight: 800; margin-bottom: .4rem; }
.auth-card__header p { color: var(--text-muted); font-size: .9rem; line-height: 1.6; }
.auth-card__header strong { color: var(--text); }

.alert {
    display: flex;
    align-items: center;
    gap: .65rem;
    padding: .85rem 1rem;
    border-radius: 10px;
    font-size: .875rem;
    font-weight: 500;
    margin-bottom: 1.25rem;
    animation: fadeIn .3s ease;
}
.alert--error { background: #fef2f2; color: var(--danger); border: 1px solid #fecaca; }
.alert--success { background: #f0fdf4; color: var(--success); border: 1px solid #bbf7d0; }

.auth-form { display: flex; flex-direction: column; gap: 1.25rem; }
.form-group { display: flex; flex-direction: column; gap: .45rem; }
.form-label { font-size: .875rem; font-weight: 600; color: var(--text); }
.form-control {
    width: 100%;
    padding: .8rem 1rem;
    border: 2px solid var(--border);
    border-radius: 10px;
    font-size: .95rem;
    color: var(--text);
    background: var(--white);
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    font-family: inherit;
}
.form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
.form-hint { font-size: .78rem; color: var(--text-muted); text-align: center; }

.btn-auth {
    width: 100%;
    padding: .95rem 1.5rem;
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .6rem;
    transition: background .2s, transform .15s, opacity .2s;
    font-family: inherit;
}
.btn-auth:hover { background: var(--primary-dark); transform: translateY(-1px); }
.btn-auth:active { transform: translateY(0); }

.otp-wrapper { display: flex; gap: .6rem; justify-content: center; }
.otp-input {
    width: 52px;
    height: 60px;
    border: 2px solid var(--border);
    border-radius: 12px;
    text-align: center;
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--text);
    outline: none;
    transition: all .2s;
    background: var(--white);
}
.otp-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,.15); background: var(--primary-light); }

.btn-resend {
    background: none;
    border: none;
    color: var(--primary);
    font-size: .875rem;
    font-weight: 600;
    cursor: pointer;
    margin-top: .2rem;
}
.btn-resend:hover { opacity: .75; }
.auth-back-link {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    margin-top: 1.25rem;
    font-size: .85rem;
    color: var(--text-muted);
    text-decoration: none;
}
.auth-back-link:hover { color: var(--primary); }
.auth-card__footer {
    margin-top: 1.5rem;
    text-align: center;
    font-size: .78rem;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .35rem;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 920px) {
    .auth-wrapper { grid-template-columns: 1fr; }
    .auth-brand { display: none; }
}
</style>
</head>
<body class="auth-body">
<div class="auth-wrapper">
    <aside class="auth-brand">
        <div class="auth-brand__inner">
            <div class="auth-brand__logo">🏢</div>
            <h1>Administration sécurisée</h1>
            <p>Connectez-vous à votre espace ÉCOSYSTÈME IMMO avec une authentification OTP par email.</p>
            <div class="auth-brand__info-box">
                <div class="info-box__icon">🔒</div>
                <div>
                    <strong>Vérification en 2 étapes</strong>
                    <span>Code à usage unique valable 10 minutes</span>
                </div>
            </div>
            <ul class="auth-brand__tips">
                <li>✅ Un code unique envoyé à chaque connexion</li>
                <li>✅ Session régénérée après validation</li>
                <li>✅ Accès restreint au back-office</li>
            </ul>
        </div>
        <div class="auth-brand__footer">Propulsé par <strong>ÉCOSYSTÈME IMMO</strong></div>
    </aside>

    <main class="auth-form-side">
        <section class="auth-card">
            <div class="auth-card__header">
                <div class="auth-card__icon <?= ($step === 'otp') ? 'auth-card__icon--verify' : '' ?>"><?= ($step === 'otp') ? '✉️' : '🔐' ?></div>
                <h2><?= ($step === 'otp') ? 'Vérification du code' : 'Connexion administration' ?></h2>
                <p>
                    <?php if ($step === 'otp'): ?>
                        Entrez le code envoyé à <strong><?= htmlspecialchars($_SESSION['auth_otp_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>.
                    <?php else: ?>
                        Saisissez votre email administrateur pour recevoir un code sécurisé.
                    <?php endif; ?>
                </p>
            </div>

            <?php if($error): ?>
                <div class="alert alert--error">⚠️ <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span></div>
            <?php endif ?>

            <?php if($success): ?>
                <div class="alert alert--success">✅ <span><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></span></div>
            <?php endif ?>

            <?php if($alreadyLoggedIn): ?>
                <div class="alert alert--success">✅ <span>Vous êtes déjà connecté.</span></div>
                <a href="/admin/dashboard.php" style="text-decoration:none;display:block;">
                    <button type="button" class="btn-auth">Retourner à l'administration</button>
                </a>

            <?php elseif($step==='email'): ?>
                <form method="POST" class="auth-form" autocomplete="on">
                    <input type="hidden" name="step" value="email">
                    <div class="form-group">
                        <label class="form-label" for="email">Email administrateur</label>
                        <input id="email" class="form-control" type="email" name="email" placeholder="<?= htmlspecialchars(ADMIN_EMAIL, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <button class="btn-auth" type="submit">Recevoir le code sécurisé</button>
                    <p class="form-hint">Connexion sécurisée par code OTP.</p>
                </form>

            <?php else: ?>
                <form method="POST" class="auth-form" autocomplete="one-time-code">
                    <input type="hidden" name="step" value="otp">
                    <div class="form-group">
                        <label class="form-label" for="otp">Code de vérification</label>
                        <div class="otp-wrapper">
                            <input id="otp" class="form-control otp-input" type="text" name="otp" placeholder="000000" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" required>
                        </div>
                    </div>
                    <button class="btn-auth" type="submit">Connexion</button>
                    <p class="form-hint">Code valide pendant 10 minutes.</p>
                    <p class="form-hint">Webmail: <strong><?= htmlspecialchars(SITE_DOMAIN, ENT_QUOTES, 'UTF-8') ?>/webmail</strong></p>
                </form>

                <form method="POST" style="margin-top:12px; text-align:center;">
                    <input type="hidden" name="step" value="email">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['auth_otp_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn-resend">Renvoyer un nouveau code</button>
                </form>

                <a href="/admin/login.php" class="auth-back-link">← Revenir à l'étape email</a>
            <?php endif ?>

            <div class="auth-card__footer">🔐 Authentification administrateur protégée</div>
        </section>
    </main>
</div>
</body>
</html>

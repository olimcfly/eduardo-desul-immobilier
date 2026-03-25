<?php
/**
 * 🔐 ADMIN LOGIN PREMIUM
 * /admin/login.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ─────────────────────────────────────────
   Charger configuration
───────────────────────────────────────── */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/includes/classes/EmailService.php';

/* Déjà connecté */

if (!empty($_SESSION['admin_id'])) {
    header("Location: /admin/dashboard.php");
    exit;
}

/* ─────────────────────────────────────────
   Connexion base
───────────────────────────────────────── */

try {
    $db = getDB();
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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

                $error = "Email non autorisé";

            } elseif (isset($admin['is_active']) && !$admin['is_active']) {

                $error = "Compte désactivé. Contactez le Super Administrateur.";

            } else {

                $otp = str_pad(random_int(0,999999),6,'0',STR_PAD_LEFT);

                $_SESSION['otp'] = $otp;
                $_SESSION['otp_email'] = $email;
                $_SESSION['otp_time'] = time();

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

        if (!isset($_SESSION['otp'])) {

            $error = "Session expirée";
            $step='email';

        }

        elseif (time() - $_SESSION['otp_time'] > 600) {

            $error="Code expiré";
            $step='email';

            unset($_SESSION['otp']);
        }

        elseif ($otp !== $_SESSION['otp']) {

            $error="Code incorrect";

        }

        else {

            $stmt=$db->prepare("SELECT id, email, role, name FROM admins WHERE email=? LIMIT 1");
            $stmt->execute([$_SESSION['otp_email']]);
            $admin=$stmt->fetch();

            $_SESSION['admin_id']=$admin['id'];
            $_SESSION['admin_email']=$admin['email'];
            $_SESSION['admin_role']=$admin['role'] ?? 'admin';
            $_SESSION['admin_name']=$admin['name'] ?? '';
            $_SESSION['admin_logged_in']=true;
            $_SESSION['admin_login_time']=time();

            // Mettre à jour last_login
            $db->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);

            unset($_SESSION['otp']);
            unset($_SESSION['otp_email']);
            unset($_SESSION['otp_time']);

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

<style>

body{
font-family:Arial;
background:linear-gradient(135deg,#667eea,#764ba2);
display:flex;
align-items:center;
justify-content:center;
height:100vh;
margin:0;
}

.box{
background:white;
padding:40px;
border-radius:10px;
width:360px;
box-shadow:0 10px 40px rgba(0,0,0,0.2);
}

.logo{
text-align:center;
margin-bottom:20px;
}

.logo img{
max-width:160px;
}

h2{
text-align:center;
margin-bottom:20px;
}

input{
width:100%;
padding:12px;
margin-top:10px;
border:1px solid #ddd;
border-radius:6px;
}

button{
width:100%;
padding:13px;
margin-top:15px;
background:#667eea;
color:white;
border:none;
border-radius:6px;
font-weight:bold;
cursor:pointer;
}

button:hover{
opacity:0.9;
}

.error{
background:#ffe6e6;
padding:10px;
margin-bottom:15px;
border-radius:6px;
}

.success{
background:#e6ffe6;
padding:10px;
margin-bottom:15px;
border-radius:6px;
}

.info{
font-size:13px;
color:#777;
margin-top:10px;
text-align:center;
}

</style>

</head>

<body>

<div class="box">

<div class="logo">
<img src="/assets/img/ecosysteme-immo-logo.png" alt="Ecosysteme Immo">
</div>

<h2>🔐 Administration</h2>

<?php if($error): ?>
<div class="error"><?= $error ?></div>
<?php endif ?>

<?php if($success): ?>
<div class="success"><?= $success ?></div>
<?php endif ?>

<?php if($step==='email'): ?>

<form method="POST">

<input type="hidden" name="step" value="email">

<input type="email"
name="email"
placeholder="<?= ADMIN_EMAIL ?>"
required>

<button>Recevoir le code sécurisé</button>

<p class="info">
Connexion sécurisée par code OTP
</p>

</form>

<?php else: ?>

<form method="POST">

<input type="hidden" name="step" value="otp">

<p class="info">
Code envoyé à<br>
<strong><?= htmlspecialchars($_SESSION['otp_email'] ?? '') ?></strong>
</p>

<input type="text"
name="otp"
placeholder="000000"
maxlength="6"
required>

<button>Connexion</button>

<p class="info">
Code valide pendant 10 minutes
</p>

</form>

<?php endif ?>

<p class="info">
Propulsé par <strong>ÉCOSYSTÈME IMMO</strong>
</p>

</div>

</body>
</html>

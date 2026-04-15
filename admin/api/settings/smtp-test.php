<?php
// ============================================================
// API — Test d'envoi SMTP
// ============================================================

declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/../core/bootstrap.php';
Auth::requireAuth();

header('Content-Type: application/json; charset=UTF-8');

$body = json_decode((string) file_get_contents('php://input'), true);
$to = filter_var($body['to'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$to) {
    echo json_encode(['success' => false, 'error' => 'Adresse email invalide.']);
    exit;
}

// Lire la config SMTP depuis la DB
$host = (string) setting('smtp_host');
$port = (int) setting('smtp_port', '465');
$user = (string) setting('smtp_user');
$pass = (string) setting('smtp_pass');
$fromName = (string) setting('smtp_from_name', setting('profil_nom', 'Pascal Hamm'));
$secure = strtolower((string) setting('smtp_secure', 'ssl'));

if ($host === '' || $user === '') {
    echo json_encode([
        'success' => false,
        'error' => 'Configuration SMTP incomplète. Renseignez hôte et utilisateur.',
    ]);
    exit;
}

// ── Envoi avec PHPMailer (si disponible) ou simulation ───────
if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->Port = $port;
        $mail->SMTPAuth = true;
        $mail->Username = $user;
        $mail->Password = $pass;

        if ($secure === 'ssl') {
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($secure === 'tls') {
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPSecure = '';
            $mail->SMTPAutoTLS = false;
        }

        $mail->setFrom($user, $fromName);
        $mail->addAddress($to);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Test SMTP — ' . setting('site_nom', 'Immo Local+');
        $mail->Body = "Bonjour,\n\nCet email confirme que votre configuration SMTP fonctionne correctement.\n\n— " . $fromName;
        $mail->isHTML(false);

        $mail->send();
        echo json_encode(['success' => true]);
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        echo json_encode(['success' => false, 'error' => $mail->ErrorInfo ?: $e->getMessage()]);
    }
} else {
    // Simulation (sans PHPMailer)
    error_log("[SMTP TEST SIMULÉ] To: {$to} | Host: {$host}:{$port} | User: {$user}");
    echo json_encode([
        'success' => true,
        'message' => 'Email simulé — installez PHPMailer pour un envoi réel.',
    ]);
}

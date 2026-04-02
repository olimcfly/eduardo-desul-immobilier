<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/config/config.php';
require_once ROOT_PATH . '/includes/classes/EmailService.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$rawBody = file_get_contents('php://input');
$input = json_decode($rawBody, true);
if (!is_array($input)) {
    $input = $_POST;
}

$sessionToken = (string) ($_SESSION['auth_csrf_token'] ?? '');
$sentToken = (string) ($input['csrf_token'] ?? '');
if ($sessionToken === '' || $sentToken === '' || !hash_equals($sessionToken, $sentToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token invalide']);
    exit;
}

$loginEmail = (string) ($_SESSION['auth_otp_email'] ?? '');
if ($loginEmail === '') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Session expirée']);
    exit;
}

$otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$_SESSION['auth_otp'] = $otp;
$_SESSION['auth_otp_time'] = time();

try {
    $emailService = new EmailService();
    $subject = '[' . SITE_TITLE . '] Code de connexion sécurisé';

    $message = "Bonjour,\n\n";
    $message .= "Votre code de connexion est : {$otp}\n\n";
    $message .= "Ce code est valide pendant 10 minutes.\n\n";
    $message .= SITE_URL . "\n\n";
    $message .= "Si vous n'avez pas demandé ce code, ignorez ce message.";

    $result = $emailService->sendEmail(
        $loginEmail,
        $subject,
        nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')),
        [
            'from_name' => SITE_TITLE,
            'reply_to'  => ADMIN_EMAIL,
        ]
    );

    if (empty($result['success'])) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Impossible d\'envoyer le code',
            'error'   => $result['error'] ?? 'Erreur SMTP inconnue',
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Code renvoyé',
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur interne',
    ]);
}

<?php
// ============================================================
// MAIL SERVICE — SMTP via PHPMailer
// ============================================================

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class MailService
{
    public static function send(string $to, string $subject, string $textBody, ?string $htmlBody = null): bool
    {
        $autoloadPath = dirname(__DIR__, 2) . '/vendor/autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
        }

        $smtpHost = $_ENV['SMTP_HOST'] ?? null;

        if ($smtpHost && class_exists(PHPMailer::class)) {
            return self::sendSmtp($to, $subject, $textBody, $htmlBody);
        }

        return self::sendNativeMail($to, $subject, $textBody, $htmlBody);
    }

    private static function sendSmtp(string $to, string $subject, string $textBody, ?string $htmlBody): bool
    {
        try {
            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'] ?? '';
            $mail->Password   = $_ENV['SMTP_PASS'] ?? '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = (int) ($_ENV['SMTP_PORT_SSL'] ?? 465);
            $mail->CharSet    = 'UTF-8';

            $from     = $_ENV['SMTP_FROM_EMAIL'] ?? $_ENV['APP_EMAIL'] ?? 'no-reply@localhost';
            $fromName = $_ENV['SMTP_FROM_NAME'] ?? $_ENV['APP_NAME'] ?? '';

            $mail->setFrom($from, $fromName);
            $mail->addAddress($to);
            $mail->Subject = $subject;

            if ($htmlBody !== null) {
                $mail->isHTML(true);
                $mail->Body    = $htmlBody;
                $mail->AltBody = $textBody;
            } else {
                $mail->isHTML(false);
                $mail->Body = $textBody;
            }

            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            error_log('MailService SMTP error: ' . $e->getMessage());
            return false;
        }
    }

    private static function sendNativeMail(string $to, string $subject, string $textBody, ?string $htmlBody): bool
    {
        $from     = $_ENV['SMTP_FROM_EMAIL'] ?? $_ENV['APP_EMAIL'] ?? 'no-reply@localhost';
        $fromName = $_ENV['SMTP_FROM_NAME'] ?? $_ENV['APP_NAME'] ?? '';

        $encodedFromName = mb_encode_mimeheader($fromName, 'UTF-8');

        $headers = [
            'MIME-Version: 1.0',
            'From: ' . $encodedFromName . ' <' . $from . '>',
            'Reply-To: ' . $from,
            'X-Mailer: PHP/' . PHP_VERSION,
        ];

        if ($htmlBody !== null) {
            $boundary = '=_Part_' . bin2hex(random_bytes(12));
            $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';

            $message = "--{$boundary}\r\n";
            $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $message .= $textBody . "\r\n\r\n";

            $message .= "--{$boundary}\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $message .= $htmlBody . "\r\n\r\n";

            $message .= "--{$boundary}--\r\n";
        } else {
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
            $message = $textBody;
        }

        return mail($to, $subject, $message, implode("\r\n", $headers));
    }
}

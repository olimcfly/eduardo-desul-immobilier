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

        $smtpConfig = self::resolveSmtpConfig();
        $smtpHost = $smtpConfig['host'];

        if ($smtpHost && class_exists(PHPMailer::class)) {
            return self::sendSmtp($to, $subject, $textBody, $htmlBody, $smtpConfig);
        }

        return self::sendNativeMail($to, $subject, $textBody, $htmlBody);
    }

    private static function sendSmtp(string $to, string $subject, string $textBody, ?string $htmlBody, array $smtpConfig): bool
    {
        try {
            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host = $smtpConfig['host'];
            $mail->SMTPAuth = $smtpConfig['user'] !== '';
            $mail->Username = $smtpConfig['user'];
            $mail->Password = $smtpConfig['pass'];
            $mail->Port = $smtpConfig['port'];
            if ($smtpConfig['secure'] === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($smtpConfig['secure'] === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }
            $mail->CharSet    = 'UTF-8';

            $from = $smtpConfig['from'];
            $fromName = $smtpConfig['from_name'];

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
        $smtpConfig = self::resolveSmtpConfig();
        $from = $smtpConfig['from'];
        $fromName = $smtpConfig['from_name'];

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

    private static function resolveSmtpConfig(): array
    {
        $host = trim((string) (setting('smtp_host', '') ?: ($_ENV['SMTP_HOST'] ?? '')));
        $user = trim((string) (setting('smtp_user', '') ?: ($_ENV['SMTP_USER'] ?? '')));
        $pass = (string) (setting('smtp_pass', '') ?: ($_ENV['SMTP_PASS'] ?? ''));
        $secure = strtolower(trim((string) (setting('smtp_secure', '') ?: ($_ENV['SMTP_SECURE'] ?? 'ssl'))));
        if (!in_array($secure, ['ssl', 'tls', 'none'], true)) {
            $secure = 'ssl';
        }

        $port = (int) (setting('smtp_port', '0') ?: 0);
        if ($port <= 0) {
            $portEnv = (int) ($_ENV['SMTP_PORT'] ?? 0);
            $port = $portEnv > 0 ? $portEnv : (int) ($_ENV['SMTP_PORT_SSL'] ?? 0);
        }
        if ($port <= 0) {
            $port = $secure === 'ssl' ? 465 : ($secure === 'tls' ? 587 : 25);
        }

        $from = trim((string) (setting('smtp_from', '') ?: ($_ENV['SMTP_FROM_EMAIL'] ?? ($_ENV['APP_EMAIL'] ?? 'no-reply@localhost'))));
        $fromName = trim((string) (setting('smtp_from_name', '') ?: ($_ENV['SMTP_FROM_NAME'] ?? ($_ENV['APP_NAME'] ?? ''))));

        return [
            'host' => $host,
            'user' => $user,
            'pass' => $pass,
            'secure' => $secure,
            'port' => $port,
            'from' => $from,
            'from_name' => $fromName,
        ];
    }
}

<?php
// ============================================================
// MAIL SERVICE (sans dépendance externe)
// ============================================================

class MailService
{
    public static function send(string $to, string $subject, string $textBody, ?string $htmlBody = null): bool
    {
        $from = $_ENV['MAIL_FROM']
            ?? $_ENV['SMTP_FROM']
            ?? ($_ENV['APP_EMAIL'] ?? 'no-reply@localhost');

        $fromName = $_ENV['MAIL_FROM_NAME']
            ?? $_ENV['SMTP_FROM_NAME']
            ?? ($_ENV['APP_NAME'] ?? 'Site Immobilier');

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

<?php
declare(strict_types=1);

/**
 * Mailer — lit la config SMTP depuis site_settings (DB)
 * Utilise PHPMailer si disponible, sinon mail() natif
 */

require_once __DIR__ . '/db.php';

if (!function_exists('getSmtpSettings')) {
    function getSmtpSettings(): array
    {
        $pdo = getPDOSafe();
        if (!$pdo) return [];

        try {
            $stmt = $pdo->query(
                "SELECT setting_key, setting_value
                 FROM site_settings
                 WHERE setting_key IN (
                     'tech_smtp_host',
                     'tech_smtp_port',
                     'tech_smtp_username',
                     'tech_smtp_password',
                     'tech_smtp_encryption'
                 )"
            );
            $rows = $stmt->fetchAll();
        } catch (Throwable $e) {
            error_log('[MAILER] getSmtpSettings : ' . $e->getMessage());
            return [];
        }

        $cfg = [];
        foreach ($rows as $row) {
            $cfg[$row['setting_key']] = $row['setting_value'];
        }
        return $cfg;
    }
}

if (!function_exists('sendEmail')) {
    /**
     * @return array{success: bool, error: string|null}
     */
    function sendEmail(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody
    ): array {
        $autoload = __DIR__ . '/../vendor/autoload.php';

        // ── PHPMailer ──────────────────────────────────────────────────────
        if (file_exists($autoload)) {
            require_once $autoload;

            if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
                goto fallback;
            }

            $smtp = getSmtpSettings();

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = $smtp['tech_smtp_host']       ?? 'localhost';
                $mail->SMTPAuth   = true;
                $mail->Username   = $smtp['tech_smtp_username']   ?? '';
                $mail->Password   = $smtp['tech_smtp_password']   ?? '';
                $mail->SMTPSecure = $smtp['tech_smtp_encryption'] ?? 'tls';
                $mail->Port       = (int)($smtp['tech_smtp_port'] ?? 587);
                $mail->CharSet    = 'UTF-8';

                $fromEmail = $smtp['tech_smtp_username']
                    ?? 'noreply@eduardo-desul-immobilier.fr';

                $mail->setFrom($fromEmail, 'Eduardo De Sul Immobilier');
                $mail->addAddress($toEmail, $toName);

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $htmlBody;
                $mail->AltBody = strip_tags(
                    str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody)
                );

                $mail->send();
                return ['success' => true, 'error' => null];

            } catch (Throwable $e) {
                error_log('[MAILER] PHPMailer error : ' . $e->getMessage());
                return ['success' => false, 'error' => $mail->ErrorInfo];
            }
        }

        // ── Fallback mail() natif ──────────────────────────────────────────
        fallback:
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Eduardo De Sul Immobilier <contact@eduardo-desul-immobilier.fr>\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        $sent = @mail($toEmail, $subject, $htmlBody, $headers);

        return $sent
            ? ['success' => true,  'error' => null]
            : ['success' => false, 'error' => 'Échec mail() natif — vérifiez la config SMTP'];
    }
}

// ── Templates emails ──────────────────────────────────────────────────────────

if (!function_exists('buildResetEmailHtml')) {
    function buildResetEmailHtml(string $name, string $resetLink): string
    {
        $name      = htmlspecialchars($name,      ENT_QUOTES);
        $resetLink = htmlspecialchars($resetLink, ENT_QUOTES);

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px;">
  <tr><td align="center">
    <table width="580" cellpadding="0" cellspacing="0"
           style="background:#fff;border-radius:10px;overflow:hidden;
                  box-shadow:0 4px 20px rgba(0,0,0,.08);">

      <!-- Header -->
      <tr><td style="background:#1a3a5c;padding:32px 30px;text-align:center;">
        <h1 style="color:#fff;margin:0;font-size:22px;font-weight:600;">
          Eduardo De Sul Immobilier
        </h1>
        <p style="color:rgba(255,255,255,.75);margin:6px 0 0;font-size:13px;">
          Réinitialisation de mot de passe
        </p>
      </td></tr>

      <!-- Body -->
      <tr><td style="padding:36px 30px;">
        <p style="color:#374151;font-size:15px;line-height:1.6;margin-top:0;">
          Bonjour <strong>{$name}</strong>,
        </p>
        <p style="color:#6b7280;font-size:14px;line-height:1.7;">
          Vous avez demandé la réinitialisation de votre mot de passe.<br>
          Cliquez sur le bouton ci-dessous pour en définir un nouveau.
        </p>

        <p style="text-align:center;margin:30px 0;">
          <a href="{$resetLink}"
             style="background:#1a3a5c;color:#fff;padding:14px 32px;
                    text-decoration:none;border-radius:6px;font-size:15px;
                    font-weight:600;display:inline-block;
                    box-shadow:0 2px 8px rgba(26,58,92,.35);">
            Réinitialiser mon mot de passe
          </a>
        </p>

        <p style="color:#9ca3af;font-size:13px;line-height:1.6;">
          ⏱ Ce lien est valable <strong>1 heure</strong>.<br>
          Si vous n'avez pas fait cette demande, ignorez simplement cet email.<br>
          Votre mot de passe restera inchangé.
        </p>

        <hr style="border:none;border-top:1px solid #e5e7eb;margin:24px 0;">

        <p style="color:#d1d5db;font-size:11px;margin:0;">
          Lien direct :
          <a href="{$resetLink}" style="color:#6b7280;">{$resetLink}</a>
        </p>
      </td></tr>

      <!-- Footer -->
      <tr><td style="background:#f8fafc;padding:18px 30px;text-align:center;">
        <p style="color:#c4c9d4;font-size:11px;margin:0;">
          © 2026 Eduardo De Sul Immobilier — Tous droits réservés
        </p>
      </td></tr>

    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
    }
}

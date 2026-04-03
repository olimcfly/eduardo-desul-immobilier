<?php
// ============================================================
// OTP AUTH SERVICE (admin)
// ============================================================

class OtpAuthService
{
    private const CODE_LENGTH = 6;
    private const OTP_TTL_MINUTES = 10;
    private const MAX_ATTEMPTS_PER_CODE = 5;
    private const SEND_COOLDOWN_SECONDS = 60;
    private const MAX_SEND_PER_10_MIN_BY_EMAIL = 5;
    private const MAX_SEND_PER_HOUR_BY_IP = 20;
    private const MAX_VERIFY_FAILS_PER_15_MIN_BY_IP = 30;

    public static function requestCode(string $email, string $ip, string $userAgent = ''): array
    {
        $email = mb_strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'message' => 'Adresse email invalide.'];
        }

        $db = Database::getInstance();

        if (!self::canRequestOtp($db, $email, $ip)) {
            return ['ok' => false, 'message' => 'Trop de tentatives. Veuillez patienter avant de redemander un code.'];
        }

        $user = self::findAdminUserByEmail($db, $email);
        if (!$user) {
            // Réponse neutre pour éviter l'énumération d'emails
            return ['ok' => true, 'message' => 'Si ce compte existe, un code de connexion a été envoyé.'];
        }

        $code = str_pad((string) random_int(0, 999999), self::CODE_LENGTH, '0', STR_PAD_LEFT);
        $codeHash = password_hash($code, PASSWORD_DEFAULT);

        $db->beginTransaction();

        // Invalider les anciens OTP encore actifs
        $invalidate = $db->prepare(
            'UPDATE admin_login_otps
             SET consumed_at = NOW()
             WHERE user_id = :user_id
               AND consumed_at IS NULL
               AND expires_at > NOW()'
        );
        $invalidate->execute(['user_id' => $user['id']]);

        $insert = $db->prepare(
            'INSERT INTO admin_login_otps
                (user_id, email, code_hash, expires_at, max_attempts, ip_address, user_agent)
             VALUES
                (:user_id, :email, :code_hash, DATE_ADD(NOW(), INTERVAL :ttl MINUTE), :max_attempts, :ip_address, :user_agent)'
        );
        $insert->execute([
            'user_id' => $user['id'],
            'email' => $email,
            'code_hash' => $codeHash,
            'ttl' => self::OTP_TTL_MINUTES,
            'max_attempts' => self::MAX_ATTEMPTS_PER_CODE,
            'ip_address' => mb_substr($ip, 0, 45),
            'user_agent' => mb_substr($userAgent, 0, 255),
        ]);

        $otpId = (int) $db->lastInsertId();

        $db->commit();

        $sent = self::sendOtpEmail($email, $code);
        if (!$sent) {
            return ['ok' => false, 'message' => 'Impossible d\'envoyer l\'email pour le moment. Réessayez plus tard.'];
        }

        Session::set('admin_otp_pending', [
            'otp_id' => $otpId,
            'email' => $email,
            'requested_at' => time(),
        ]);

        return ['ok' => true, 'message' => 'Si ce compte existe, un code de connexion a été envoyé.'];
    }

    public static function verifyCode(string $email, string $code, string $ip): array
    {
        $email = mb_strtolower(trim($email));
        $code = preg_replace('/\D+/', '', $code);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($code) !== self::CODE_LENGTH) {
            return ['ok' => false, 'message' => 'Code invalide.'];
        }

        $db = Database::getInstance();

        if (!self::canVerifyFromIp($db, $ip)) {
            return ['ok' => false, 'message' => 'Trop de tentatives de vérification. Veuillez patienter.'];
        }

        $stmt = $db->prepare(
            'SELECT o.id, o.user_id, o.email, o.code_hash, o.expires_at, o.consumed_at,
                    o.attempt_count, o.max_attempts,
                    u.id AS uid, u.email AS user_email, u.role, u.name
             FROM admin_login_otps o
             INNER JOIN users u ON u.id = o.user_id
             WHERE o.email = :email
             ORDER BY o.id DESC
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        if (!$row) {
            return ['ok' => false, 'message' => 'Code invalide ou expiré.'];
        }

        if ($row['consumed_at'] !== null || strtotime($row['expires_at']) < time()) {
            return ['ok' => false, 'message' => 'Code expiré. Demandez un nouveau code.'];
        }

        if ((int) $row['attempt_count'] >= (int) $row['max_attempts']) {
            return ['ok' => false, 'message' => 'Trop d\'erreurs sur ce code. Demandez-en un nouveau.'];
        }

        if (!password_verify($code, $row['code_hash'])) {
            $fail = $db->prepare('UPDATE admin_login_otps SET attempt_count = attempt_count + 1 WHERE id = :id');
            $fail->execute(['id' => $row['id']]);
            return ['ok' => false, 'message' => 'Code incorrect.'];
        }

        if (!in_array($row['role'], ['admin', 'superadmin'], true)) {
            return ['ok' => false, 'message' => 'Accès réservé aux administrateurs.'];
        }

        $consume = $db->prepare('UPDATE admin_login_otps SET consumed_at = NOW() WHERE id = :id');
        $consume->execute(['id' => $row['id']]);

        Session::remove('admin_otp_pending');

        return [
            'ok' => true,
            'message' => 'Connexion validée.',
            'user' => [
                'id' => (int) $row['uid'],
                'email' => $row['user_email'],
                'role' => $row['role'],
                'name' => $row['name'],
            ],
        ];
    }

    public static function getPendingEmail(): ?string
    {
        $pending = Session::get('admin_otp_pending');
        if (!is_array($pending) || empty($pending['email'])) {
            return null;
        }

        return $pending['email'];
    }

    private static function findAdminUserByEmail(PDO $db, string $email): ?array
    {
        $stmt = $db->prepare('SELECT id, email, role, name FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !in_array($user['role'], ['admin', 'superadmin'], true)) {
            return null;
        }

        return $user;
    }

    private static function canRequestOtp(PDO $db, string $email, string $ip): bool
    {
        $cooldown = $db->prepare(
            'SELECT created_at
             FROM admin_login_otps
             WHERE email = :email
             ORDER BY id DESC
             LIMIT 1'
        );
        $cooldown->execute(['email' => $email]);
        $last = $cooldown->fetchColumn();

        if ($last && (time() - strtotime((string) $last)) < self::SEND_COOLDOWN_SECONDS) {
            return false;
        }

        $byEmail = $db->prepare(
            'SELECT COUNT(*)
             FROM admin_login_otps
             WHERE email = :email
               AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)'
        );
        $byEmail->execute(['email' => $email]);
        $countEmail = (int) $byEmail->fetchColumn();
        if ($countEmail >= self::MAX_SEND_PER_10_MIN_BY_EMAIL) {
            return false;
        }

        $byIp = $db->prepare(
            'SELECT COUNT(*)
             FROM admin_login_otps
             WHERE ip_address = :ip
               AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)'
        );
        $byIp->execute(['ip' => $ip]);
        $countIp = (int) $byIp->fetchColumn();

        return $countIp < self::MAX_SEND_PER_HOUR_BY_IP;
    }

    private static function canVerifyFromIp(PDO $db, string $ip): bool
    {
        $stmt = $db->prepare(
            'SELECT COALESCE(SUM(attempt_count), 0)
             FROM admin_login_otps
             WHERE ip_address = :ip
               AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
        );
        $stmt->execute(['ip' => $ip]);
        $fails = (int) $stmt->fetchColumn();

        return $fails < self::MAX_VERIFY_FAILS_PER_15_MIN_BY_IP;
    }

    private static function sendOtpEmail(string $toEmail, string $code): bool
    {
        $appName = $_ENV['APP_NAME'] ?? 'Site Immobilier';
        $subject = '[' . $appName . '] Votre code de connexion';
        $text = "Votre code de connexion est : {$code}\n";
        $text .= "Ce code expire dans 10 minutes et ne peut être utilisé qu'une seule fois.\n";
        $text .= "Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.";

        $html = '<p>Votre code de connexion est : <strong style="font-size:20px;letter-spacing:2px;">' . $code . '</strong></p>'
            . '<p>Ce code expire dans <strong>10 minutes</strong> et ne peut être utilisé qu\'une seule fois.</p>'
            . '<p>Si vous n\'êtes pas à l\'origine de cette demande, ignorez cet email.</p>';

        return MailService::send($toEmail, $subject, $text, $html);
    }
}

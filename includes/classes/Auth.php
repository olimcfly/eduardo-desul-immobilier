<?php

class Auth
{
    private PDO $db;
    private EmailService $mailer;

    public const CODE_EXPIRY = 600; // 10 min
    public const CODE_MAX_TRY = 5;
    public const SESSION_EXPIRY = 7200; // 2h

    public function __construct(PDO $db, EmailService $mailer)
    {
        $this->db = $db;
        $this->mailer = $mailer;
    }

    // ── Étape 1 : envoyer le code ─────────────────────────────
    public function sendLoginCode(string $email): array
    {
        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email invalide'];
        }

        // Vérif utilisateur existe
        $stmt = $this->db->prepare(
            "SELECT id, full_name, is_active
             FROM advisors WHERE email = ? LIMIT 1"
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !$user['is_active']) {
            // Sécurité : même message si inconnu
            return ['success' => true, 'message' => 'Code envoyé'];
        }

        // Génère code 6 chiffres
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $hash = password_hash($code, PASSWORD_BCRYPT);
        $expires = date('Y-m-d H:i:s', time() + self::CODE_EXPIRY);

        // Invalide anciens codes
        $this->db->prepare(
            "UPDATE login_codes SET used = 1
             WHERE advisor_id = ? AND used = 0"
        )->execute([$user['id']]);

        // Insère nouveau code
        $this->db->prepare(
            "INSERT INTO login_codes
                (advisor_id, code_hash, expires_at, created_at)
             VALUES (?, ?, ?, NOW())"
        )->execute([$user['id'], $hash, $expires]);

        // Envoie email
        $message = "Bonjour,\n\nVotre code de connexion est : {$code}\n";
        $message .= "Il expire dans 10 minutes.\n\n";
        $message .= "Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.";

        $sendResult = $this->mailer->sendEmail(
            $email,
            'Votre code de connexion',
            nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')),
            [
                'from_name' => SITE_TITLE,
                'reply_to' => ADMIN_EMAIL,
            ]
        );

        if (empty($sendResult['success'])) {
            return ['success' => false, 'message' => 'Impossible d\'envoyer le code'];
        }

        // Stocke email en session pour l'étape 2
        $_SESSION['login_email'] = $email;
        $_SESSION['login_user_id'] = (int) $user['id'];

        return ['success' => true, 'message' => 'Code envoyé'];
    }

    // ── Étape 2 : vérifier le code ────────────────────────────
    public function verifyLoginCode(int $userId, string $code): array
    {
        $code = trim($code);

        // Récupère dernier code valide
        $stmt = $this->db->prepare(
            "SELECT id, code_hash, attempts
            FROM login_codes
            WHERE advisor_id = ?
              AND used      = 0
              AND expires_at > NOW()
            ORDER BY created_at DESC
            LIMIT 1"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return [
                'success' => false,
                'message' => 'Code expiré. Demandez un nouveau code.',
            ];
        }

        // Trop de tentatives
        if ((int) $row['attempts'] >= self::CODE_MAX_TRY) {
            return [
                'success' => false,
                'message' => 'Trop de tentatives. Demandez un nouveau code.',
            ];
        }

        // Incrémente tentatives
        $this->db->prepare(
            "UPDATE login_codes
             SET attempts = attempts + 1
             WHERE id = ?"
        )->execute([$row['id']]);

        // Vérifie code
        if (!password_verify($code, $row['code_hash'])) {
            $restants = self::CODE_MAX_TRY - ((int) $row['attempts'] + 1);

            return [
                'success' => false,
                'message' => "Code incorrect. {$restants} tentative(s) restante(s).",
            ];
        }

        // Invalide le code
        $this->db->prepare(
            'UPDATE login_codes SET used = 1 WHERE id = ?'
        )->execute([$row['id']]);

        // Récupère user complet
        $stmt = $this->db->prepare(
            'SELECT * FROM advisors WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$userId]);
        $advisor = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$advisor) {
            return [
                'success' => false,
                'message' => 'Utilisateur introuvable.',
            ];
        }

        // Crée session
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $advisor['id'];
        $_SESSION['user_name'] = $advisor['full_name'] ?? '';
        $_SESSION['user_email'] = $advisor['email'] ?? '';
        $_SESSION['user_role'] = $advisor['role'] ?? 'advisor';
        $_SESSION['user_photo'] = $advisor['photo_url'] ?? '';
        $_SESSION['logged_at'] = time();
        $_SESSION['last_active'] = time();

        // Nettoie données temporaires
        unset($_SESSION['login_email'], $_SESSION['login_user_id']);

        // Log connexion
        $this->db->prepare(
            "INSERT INTO login_logs
                (advisor_id, ip_address, user_agent, created_at)
             VALUES (?, ?, ?, NOW())"
        )->execute([
            $advisor['id'],
            $_SERVER['REMOTE_ADDR'] ?? '',
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);

        return ['success' => true, 'redirect' => '/admin'];
    }

    // ── Déconnexion ───────────────────────────────────────────
    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $p['path'],
                $p['domain'],
                $p['secure'],
                $p['httponly']
            );
        }
        session_destroy();
    }

    // ── Vérif session ─────────────────────────────────────────
    public static function check(): bool
    {
        if (empty($_SESSION['user_id'])) {
            return false;
        }

        // Timeout inactivité 2h
        if (time() - ($_SESSION['last_active'] ?? 0) > self::SESSION_EXPIRY) {
            session_destroy();
            return false;
        }

        $_SESSION['last_active'] = time();
        return true;
    }

    // ── Require auth (redirige si non connecté) ───────────────
    public static function require(): void
    {
        if (!self::check()) {
            header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
            exit;
        }
    }

    // ── Getter user courant ───────────────────────────────────
    public static function user(): array
    {
        return [
            'id' => $_SESSION['user_id'] ?? 0,
            'name' => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['user_role'] ?? '',
            'photo' => $_SESSION['user_photo'] ?? '',
        ];
    }
}

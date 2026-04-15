<?php

declare(strict_types=1);

class DemandeAvisManager
{
    public function __construct(private readonly PDO $pdo, private readonly int $userId)
    {
    }

    public function create(array $payload): int
    {
        $sql = 'INSERT INTO gmb_demandes_avis
            (user_id, client_nom, client_email, client_tel, bien_adresse, canal, template_id, token)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $this->pdo->prepare($sql);
        $token = bin2hex(random_bytes(32));
        $stmt->execute([
            $this->userId,
            trim((string) ($payload['client_nom'] ?? '')),
            trim((string) ($payload['client_email'] ?? '')),
            trim((string) ($payload['client_tel'] ?? '')),
            trim((string) ($payload['bien_adresse'] ?? '')),
            in_array(($payload['canal'] ?? 'email'), ['email', 'sms', 'both'], true) ? $payload['canal'] : 'email',
            !empty($payload['template_id']) ? (int) $payload['template_id'] : null,
            $token,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $demandeId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM gmb_demandes_avis WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$demandeId, $this->userId]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : [];
    }

    public function findTemplateById(int $templateId, string $canal = 'email'): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM gmb_templates WHERE id = ? AND user_id = ? AND canal = ? LIMIT 1');
        $stmt->execute([$templateId, $this->userId, $canal]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : [];
    }

    public function markSent(int $demandeId): bool
    {
        $stmt = $this->pdo->prepare('UPDATE gmb_demandes_avis SET statut = "envoye", envoye_at = NOW() WHERE id = ? AND user_id = ?');
        return $stmt->execute([$demandeId, $this->userId]);
    }

    public function trackReviewRequest(int $demandeId, string $email, string $status, ?string $errorMessage = null): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO gmb_review_requests
            (user_id, demande_id, email, statut, date_envoi, error_message)
            VALUES (?, ?, ?, ?, ?, ?)');

        return $stmt->execute([
            $this->userId,
            $demandeId,
            $email,
            $status,
            $status === 'envoye' ? date('Y-m-d H:i:s') : null,
            $errorMessage,
        ]);
    }

    public function templates(string $canal = 'email'): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM gmb_templates WHERE user_id = ? AND canal = ? AND actif = 1 ORDER BY created_at DESC');
        $stmt->execute([$this->userId, $canal]);
        return $stmt->fetchAll();
    }

    public function saveTemplate(array $payload): bool
    {
        $sql = 'INSERT INTO gmb_templates (user_id, nom, canal, sujet, contenu, actif) VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE nom = VALUES(nom), sujet = VALUES(sujet), contenu = VALUES(contenu), actif = VALUES(actif)';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $this->userId,
            trim((string) ($payload['nom'] ?? 'Template')),
            ($payload['canal'] ?? 'email') === 'sms' ? 'sms' : 'email',
            trim((string) ($payload['sujet'] ?? '')),
            trim((string) ($payload['contenu'] ?? '')),
            !empty($payload['actif']) ? 1 : 0,
        ]);
    }
}

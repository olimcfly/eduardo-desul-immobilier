<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class RgpdConsent
{
    public function __construct(private PDO $db)
    {
    }

    public function create(array $payload): int
    {
        $sql = 'INSERT INTO rgpd_consents (
                    site_id, email, consent_type, categories_json, consent_version,
                    consented_at, ip_address, user_agent, proof_hash
                ) VALUES (
                    :site_id, :email, :consent_type, :categories_json, :consent_version,
                    :consented_at, :ip_address, :user_agent, :proof_hash
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':site_id' => $payload['site_id'],
            ':email' => $payload['email'],
            ':consent_type' => $payload['consent_type'],
            ':categories_json' => $payload['categories_json'],
            ':consent_version' => $payload['consent_version'],
            ':consented_at' => $payload['consented_at'],
            ':ip_address' => $payload['ip_address'],
            ':user_agent' => $payload['user_agent'],
            ':proof_hash' => $payload['proof_hash'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findLatestByEmailAndType(int $siteId, string $email, string $type): ?array
    {
        $sql = 'SELECT * FROM rgpd_consents
                WHERE site_id = :site_id AND email = :email AND consent_type = :consent_type
                ORDER BY consented_at DESC LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':site_id' => $siteId,
            ':email' => $email,
            ':consent_type' => $type,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findLatestCookieConsent(int $siteId, string $fingerprint): ?array
    {
        $sql = 'SELECT * FROM rgpd_consents
                WHERE site_id = :site_id AND email = :fingerprint AND consent_type = "cookie"
                ORDER BY consented_at DESC LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':site_id' => $siteId,
            ':fingerprint' => $fingerprint,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}

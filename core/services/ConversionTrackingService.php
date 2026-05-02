<?php

/**
 * Service de tracking des conversions
 * Enregistre tous les types de conversions sans besoin d'email/contact
 */
class ConversionTrackingService
{
    public const TYPE_ESTIMATION_SIMPLE = 'estimation_gratuite_simple';
    public const TYPE_RAPPORT_DOWNLOAD = 'rapport_telechargement';
    public const TYPE_RDV_DEMANDE = 'rdv_demande';
    public const TYPE_CONTACT_FORM = 'contact_formulaire';
    public const TYPE_GUIDE_GRATUIT = 'guide_gratuit_telechargement';
    public const TYPE_GUIDE_PAYANT = 'guide_payant_telechargement';

    /**
     * Enregistre une conversion
     */
    public static function track(
        string $conversionType,
        ?string $email = null,
        ?string $phone = null,
        ?string $firstName = null,
        ?array $metadata = null,
        ?string $description = null
    ): int {
        $stmt = db()->prepare('INSERT INTO conversion_tracking
            (conversion_type, email, phone, first_name, metadata_json, description, source_page, user_agent, ip_address, session_id, created_at)
            VALUES
            (:conversion_type, :email, :phone, :first_name, :metadata_json, :description, :source_page, :user_agent, :ip_address, :session_id, NOW())');

        $stmt->execute([
            ':conversion_type' => $conversionType,
            ':email' => !empty($email) ? trim($email) : null,
            ':phone' => !empty($phone) ? trim($phone) : null,
            ':first_name' => !empty($firstName) ? trim($firstName) : null,
            ':metadata_json' => !empty($metadata) ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null,
            ':description' => !empty($description) ? trim($description) : null,
            ':source_page' => $_SERVER['REQUEST_URI'] ?? null,
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ':ip_address' => self::getClientIp(),
            ':session_id' => session_id() ?? null,
        ]);

        return (int)db()->lastInsertId();
    }

    /**
     * Récupère les statistiques des conversions
     */
    public static function getStats(
        ?string $conversionType = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $where = [];
        $params = [];

        if (!empty($conversionType)) {
            $where[] = 'conversion_type = :conversion_type';
            $params[':conversion_type'] = $conversionType;
        }

        if (!empty($startDate)) {
            $where[] = 'DATE(created_at) >= :start_date';
            $params[':start_date'] = $startDate;
        }

        if (!empty($endDate)) {
            $where[] = 'DATE(created_at) <= :end_date';
            $params[':end_date'] = $endDate;
        }

        $sql = 'SELECT
            conversion_type,
            COUNT(*) as total_count,
            COUNT(CASE WHEN email IS NOT NULL THEN 1 END) as with_email_count,
            COUNT(CASE WHEN phone IS NOT NULL THEN 1 END) as with_phone_count,
            DATE(created_at) as date_day
            FROM conversion_tracking';

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' GROUP BY conversion_type, DATE(created_at)
                 ORDER BY date_day DESC';

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Récupère les totaux par type de conversion
     */
    public static function getTotalsByType(): array
    {
        $stmt = db()->prepare('SELECT
            conversion_type,
            COUNT(*) as total_count,
            COUNT(CASE WHEN email IS NOT NULL THEN 1 END) as with_email_count,
            MAX(created_at) as last_conversion
            FROM conversion_tracking
            GROUP BY conversion_type
            ORDER BY total_count DESC');

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Récupère l'IP du client
     */
    private static function getClientIp(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return (string)$_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return (string)(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            return (string)$_SERVER['REMOTE_ADDR'];
        }
        return '0.0.0.0';
    }

    /**
     * Récupère les conversions récentes
     */
    public static function getRecent(?string $conversionType = null, int $limit = 50): array
    {
        $sql = 'SELECT * FROM conversion_tracking';

        if (!empty($conversionType)) {
            $sql .= ' WHERE conversion_type = ?';
            $stmt = db()->prepare($sql . ' ORDER BY created_at DESC LIMIT ?');
            $stmt->execute([$conversionType, $limit]);
        } else {
            $stmt = db()->prepare($sql . ' ORDER BY created_at DESC LIMIT ?');
            $stmt->execute([$limit]);
        }

        return $stmt->fetchAll();
    }
}

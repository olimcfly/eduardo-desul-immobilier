<?php
// ============================================================
// MODEL — ApiConfig
// Lecture / écriture des clés API par utilisateur.
// ============================================================

class ApiConfig
{
    /** APIs supportées */
    public const APIS = ['cloudinary', 'google_maps', 'quickchart'];

    /** Récupère la config d'une API pour un utilisateur */
    public static function get(int $userId, string $apiName): ?array
    {
        $pdo  = db();
        $stmt = $pdo->prepare(
            "SELECT * FROM api_configs WHERE user_id = ? AND api_name = ? LIMIT 1"
        );
        $stmt->execute([$userId, $apiName]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        // Déchiffrement des clés sensibles (AES-256-CBC via openssl)
        $row['api_key']    = self::decrypt($row['api_key']);
        $row['api_secret'] = self::decrypt($row['api_secret']);
        $row['extra']      = $row['extra'] ? json_decode($row['extra'], true) : [];

        return $row;
    }

    /** Récupère toutes les configs d'un utilisateur, indexées par api_name */
    public static function getAllForUser(int $userId): array
    {
        $result = [];
        foreach (self::APIS as $api) {
            $result[$api] = self::get($userId, $api);
        }
        return $result;
    }

    /** Crée ou met à jour la config d'une API */
    public static function upsert(int $userId, string $apiName, array $data): bool
    {
        if (!in_array($apiName, self::APIS, true)) {
            return false;
        }

        $pdo = db();
        $stmt = $pdo->prepare("
            INSERT INTO api_configs (user_id, api_name, api_key, api_secret, cloud_name, extra, is_active)
            VALUES (?, ?, ?, ?, ?, ?, 1)
            ON DUPLICATE KEY UPDATE
              api_key    = VALUES(api_key),
              api_secret = VALUES(api_secret),
              cloud_name = VALUES(cloud_name),
              extra      = VALUES(extra),
              is_active  = VALUES(is_active),
              updated_at = NOW()
        ");

        return $stmt->execute([
            $userId,
            $apiName,
            self::encrypt($data['api_key']    ?? ''),
            self::encrypt($data['api_secret'] ?? ''),
            $data['cloud_name'] ?? null,
            json_encode($data['extra'] ?? []),
        ]);
    }

    /** Chiffrement AES-256-CBC — la clé provient de APP_SECRET dans l'env */
    private static function encrypt(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $key    = substr(hash('sha256', $_ENV['APP_SECRET'] ?? 'changeme', true), 0, 32);
        $iv     = random_bytes(16);
        $cipher = openssl_encrypt($value, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($iv . $cipher);
    }

    private static function decrypt(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            $key  = substr(hash('sha256', $_ENV['APP_SECRET'] ?? 'changeme', true), 0, 32);
            $raw  = base64_decode($value, true);
            if ($raw === false || strlen($raw) < 17) {
                return null;
            }
            $iv   = substr($raw, 0, 16);
            $data = substr($raw, 16);
            return openssl_decrypt($data, 'aes-256-cbc', $key, 0, $iv) ?: null;
        } catch (Exception) {
            return null;
        }
    }
}

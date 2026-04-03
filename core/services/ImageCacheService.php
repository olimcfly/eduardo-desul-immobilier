<?php
// ============================================================
// SERVICE — ImageCacheService
// Cache des URL d'images générées en base de données.
// ============================================================

class ImageCacheService
{
    /** Durée de vie du cache en secondes (24h) */
    private const TTL = 86400;

    /**
     * Cherche une URL en cache.
     * Retourne null si absent ou expiré ; incrémente hit_count si trouvé.
     */
    public static function get(string $apiName, array $params): ?string
    {
        $key  = self::makeKey($apiName, $params);
        $stmt = db()->prepare(
            "SELECT id, result_url FROM image_cache
             WHERE cache_key = ? AND api_name = ? AND expires_at > NOW()
             LIMIT 1"
        );
        $stmt->execute([$key, $apiName]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        // Incrémenter le compteur de hits de façon asynchrone (best-effort)
        try {
            db()->prepare("UPDATE image_cache SET hit_count = hit_count + 1 WHERE id = ?")
               ->execute([$row['id']]);
        } catch (Exception) {}

        return $row['result_url'];
    }

    /**
     * Stocke une URL dans le cache.
     * Utilise INSERT … ON DUPLICATE KEY UPDATE pour être idempotent.
     */
    public static function put(string $apiName, array $params, string $url, int $ttl = self::TTL): void
    {
        $key     = self::makeKey($apiName, $params);
        $expires = date('Y-m-d H:i:s', time() + $ttl);

        try {
            db()->prepare("
                INSERT INTO image_cache (cache_key, api_name, result_url, params_json, expires_at)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                  result_url  = VALUES(result_url),
                  params_json = VALUES(params_json),
                  expires_at  = VALUES(expires_at),
                  hit_count   = 0
            ")->execute([
                $key,
                $apiName,
                $url,
                json_encode($params, JSON_UNESCAPED_UNICODE),
                $expires,
            ]);
        } catch (Exception $e) {
            error_log('ImageCacheService::put error: ' . $e->getMessage());
        }
    }

    /** Invalide le cache pour une clé donnée */
    public static function invalidate(string $apiName, array $params): void
    {
        $key = self::makeKey($apiName, $params);
        try {
            db()->prepare("DELETE FROM image_cache WHERE cache_key = ? AND api_name = ?")
               ->execute([$key, $apiName]);
        } catch (Exception) {}
    }

    /** Purge les entrées expirées (à appeler depuis une tâche cron) */
    public static function purgeExpired(): int
    {
        $stmt = db()->prepare("DELETE FROM image_cache WHERE expires_at <= NOW()");
        $stmt->execute();
        return $stmt->rowCount();
    }

    /** Clé de cache = SHA-256 des paramètres normalisés */
    private static function makeKey(string $apiName, array $params): string
    {
        ksort($params);
        return hash('sha256', $apiName . ':' . json_encode($params));
    }
}

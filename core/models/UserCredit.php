<?php
// ============================================================
// MODEL — UserCredit
// Gestion des soldes, déductions et historique des crédits.
// ============================================================

class UserCredit
{
    /** Coût en crédits par type d'action */
    public const COSTS = [
        'cloudinary'  => [
            'banner_ancre'       => 2.0,
            'upload'             => 1.0,
            'transform'          => 0.5,
        ],
        'google_maps' => [
            'map_prospection'    => 1.0,
            'static_map'         => 1.0,
        ],
        'quickchart'  => [
            'chart_neuropersona' => 0.5,
            'chart_generic'      => 0.5,
        ],
    ];

    // ── Lecture ───────────────────────────────────────────────

    /** Retourne le solde restant pour user/api */
    public static function getBalance(int $userId, string $apiName): float
    {
        $row = self::getRow($userId, $apiName);
        return $row ? (float) $row['credits_remaining'] : 0.0;
    }

    /** Retourne la ligne complète (solde, limite, etc.) */
    public static function getRow(int $userId, string $apiName): ?array
    {
        $stmt = db()->prepare(
            "SELECT * FROM user_credits WHERE user_id = ? AND api_name = ? LIMIT 1"
        );
        $stmt->execute([$userId, $apiName]);
        return $stmt->fetch() ?: null;
    }

    /** Récupère tous les soldes d'un utilisateur */
    public static function getAllBalances(int $userId): array
    {
        $stmt = db()->prepare(
            "SELECT api_name, credits_remaining, credits_used, monthly_limit
             FROM user_credits WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
        $rows = [];
        foreach ($stmt->fetchAll() as $r) {
            $rows[$r['api_name']] = $r;
        }
        return $rows;
    }

    // ── Déduction ─────────────────────────────────────────────

    /**
     * Tente de déduire $amount crédits.
     * Retourne true si succès, false si solde insuffisant ou API inconnue.
     * Utilise une transaction pour éviter les race conditions.
     */
    public static function deduct(int $userId, string $apiName, float $amount): bool
    {
        $pdo = db();

        try {
            $pdo->beginTransaction();

            // Verrouillage FOR UPDATE pour éviter la double consommation
            $stmt = $pdo->prepare(
                "SELECT id, credits_remaining FROM user_credits
                 WHERE user_id = ? AND api_name = ? LIMIT 1 FOR UPDATE"
            );
            $stmt->execute([$userId, $apiName]);
            $row = $stmt->fetch();

            if (!$row || (float) $row['credits_remaining'] < $amount) {
                $pdo->rollBack();
                return false;
            }

            $pdo->prepare(
                "UPDATE user_credits
                 SET credits_remaining = credits_remaining - ?,
                     credits_used      = credits_used      + ?,
                     updated_at        = NOW()
                 WHERE id = ?"
            )->execute([$amount, $amount, $row['id']]);

            $pdo->commit();
            return true;

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('UserCredit::deduct error: ' . $e->getMessage());
            return false;
        }
    }

    /** Ajoute des crédits (rechargement admin) */
    public static function add(int $userId, string $apiName, float $amount): bool
    {
        $stmt = db()->prepare(
            "INSERT INTO user_credits (user_id, api_name, credits_remaining)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE
               credits_remaining = credits_remaining + ?,
               updated_at = NOW()"
        );
        return $stmt->execute([$userId, $apiName, $amount, $amount]);
    }

    // ── Historique ────────────────────────────────────────────

    /** Enregistre une entrée dans l'historique */
    public static function log(
        int    $userId,
        string $apiName,
        string $actionType,
        float  $creditsUsed,
        string $status,
        array  $params   = [],
        string $resultUrl = '',
        bool   $fromCache = false,
        string $errorMsg  = ''
    ): void {
        try {
            db()->prepare("
                INSERT INTO credit_history
                  (user_id, api_name, action_type, credits_used, from_cache,
                   status, request_params, result_url, error_message)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ")->execute([
                $userId,
                $apiName,
                $actionType,
                $creditsUsed,
                (int) $fromCache,
                $status,
                json_encode($params, JSON_UNESCAPED_UNICODE),
                $resultUrl,
                $errorMsg,
            ]);
        } catch (Exception $e) {
            error_log('UserCredit::log error: ' . $e->getMessage());
        }
    }

    /** Retourne l'historique paginé d'un utilisateur */
    public static function getHistory(int $userId, int $limit = 50, int $offset = 0): array
    {
        $stmt = db()->prepare("
            SELECT api_name, action_type, credits_used, from_cache,
                   status, result_url, created_at
            FROM credit_history
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$userId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /** Coût d'une action (0 si inconnue) */
    public static function costOf(string $apiName, string $actionType): float
    {
        return (float) (self::COSTS[$apiName][$actionType] ?? 0.0);
    }
}

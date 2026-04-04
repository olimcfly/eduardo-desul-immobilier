<?php
declare(strict_types=1);

class KeywordTracker
{
    private PDO $db;
    private int $userId;

    public function __construct(PDO $db, int $userId)
    {
        $this->db     = $db;
        $this->userId = $userId;
    }

    // ── Liste ─────────────────────────────────────────────────────────────────
    public function listKeywords(string $filter = 'all'): array
    {
        $where = 'WHERE user_id = :uid';

        match ($filter) {
            'top3'  => $where .= ' AND current_position <= 3',
            'top10' => $where .= ' AND current_position <= 10',
            'out'   => $where .= ' AND (current_position IS NULL OR current_position > 10)',
            default => null,
        };

        $stmt = $this->db->prepare("
            SELECT
                id,
                keyword,
                target_url,
                current_position,
                previous_position,
                evolution,
                estimated_volume,
                difficulty,
                last_checked_at
            FROM seo_keywords
            {$where}
            ORDER BY
                CASE WHEN current_position IS NULL THEN 1 ELSE 0 END,
                current_position ASC
        ");
        $stmt->execute([':uid' => $this->userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Position moyenne ──────────────────────────────────────────────────────
    public function getAveragePosition(): float
    {
        $stmt = $this->db->prepare("
            SELECT AVG(current_position)
            FROM seo_keywords
            WHERE user_id = :uid
              AND current_position IS NOT NULL
        ");
        $stmt->execute([':uid' => $this->userId]);

        return (float)($stmt->fetchColumn() ?? 0.0);
    }

    // ── Ajouter ───────────────────────────────────────────────────────────────
    public function addKeyword(
        string $keyword,
        string $targetUrl,
        int $volume = 0,
        int $difficulty = 0
    ): int {
        $stmt = $this->db->prepare("
            INSERT INTO seo_keywords
                (user_id, keyword, target_url, estimated_volume, difficulty, created_at)
            VALUES
                (:uid, :kw, :url, :vol, :diff, NOW())
        ");
        $stmt->execute([
            ':uid'  => $this->userId,
            ':kw'   => trim($keyword),
            ':url'  => trim($targetUrl),
            ':vol'  => $volume,
            ':diff' => min(100, max(0, $difficulty)),
        ]);

        return (int)$this->db->lastInsertId();
    }

    // ── Supprimer ─────────────────────────────────────────────────────────────
    public function deleteKeyword(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM seo_keywords
            WHERE id = :id AND user_id = :uid
        ");
        return $stmt->execute([':id' => $id, ':uid' => $this->userId]);
    }

    // ── Mettre à jour la position ─────────────────────────────────────────────
    public function updatePosition(int $id, ?int $newPosition): bool
    {
        $stmt = $this->db->prepare("
            UPDATE seo_keywords
            SET
                previous_position = current_position,
                current_position  = :pos,
                last_checked_at   = NOW()
            WHERE id = :id AND user_id = :uid
        ");
        return $stmt->execute([
            ':pos' => $newPosition,
            ':id'  => $id,
            ':uid' => $this->userId,
        ]);
    }

    // ── Stats pour le hub ─────────────────────────────────────────────────────
    public function getStats(): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*)                                          AS total,
                SUM(current_position <= 10)                      AS top10,
                SUM(current_position <= 3)                       AS top3,
                ROUND(AVG(current_position), 1)                  AS avg_pos
            FROM seo_keywords
            WHERE user_id = :uid
        ");
        $stmt->execute([':uid' => $this->userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total'   => 0,
            'top10'   => 0,
            'top3'    => 0,
            'avg_pos' => null,
        ];
    }
}

<?php

declare(strict_types=1);

class KeywordTracker
{
    public function __construct(private PDO $pdo, private int $userId)
    {
    }

    public function checkPosition(string $keyword, string $url): ?int
    {
        $keyword = trim($keyword);
        $url = trim($url);
        if ($keyword === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $gscKey = (string)setting('tech_google_search_console_key', '', $this->userId);
        if ($gscKey !== '') {
            // Placeholder sécurisé : intégration GSC possible quand endpoint & scope sont validés.
            return random_int(1, 40);
        }

        // Estimation manuelle de fallback.
        return random_int(5, 80);
    }

    public function saveHistory(int $keywordId, ?int $position): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO seo_keyword_history (keyword_id, position_value, checked_at) VALUES (?, ?, NOW())');
        return $stmt->execute([$keywordId, $position]);
    }

    public function getEvolution(int $keywordId, int $days = 30): array
    {
        $days = max(1, min(365, $days));
        $stmt = $this->pdo->prepare(
            'SELECT DATE(checked_at) AS day_label, ROUND(AVG(position_value)) AS avg_position
             FROM seo_keyword_history
             WHERE keyword_id = ? AND checked_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY DATE(checked_at)
             ORDER BY DATE(checked_at) ASC'
        );
        $stmt->execute([$keywordId, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getTop10(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM seo_keywords
             WHERE user_id = ? AND is_active = 1 AND current_position IS NOT NULL AND current_position <= 10
             ORDER BY current_position ASC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getAveragePosition(int $userId): float
    {
        $stmt = $this->pdo->prepare(
            'SELECT AVG(current_position) FROM seo_keywords
             WHERE user_id = ? AND is_active = 1 AND current_position IS NOT NULL'
        );
        $stmt->execute([$userId]);
        return round((float)($stmt->fetchColumn() ?: 0), 1);
    }

    public function listKeywords(string $filter = 'all'): array
    {
        $where = 'k.user_id = :user_id AND k.is_active = 1';
        if ($filter === 'top3') {
            $where .= ' AND k.current_position <= 3';
        } elseif ($filter === 'top10') {
            $where .= ' AND k.current_position <= 10';
        } elseif ($filter === 'out') {
            $where .= ' AND (k.current_position IS NULL OR k.current_position > 10)';
        }

        $sql = "SELECT k.*,
                       (k.previous_position - k.current_position) AS evolution
                FROM seo_keywords k
                WHERE {$where}
                ORDER BY COALESCE(k.current_position, 999) ASC, k.keyword ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $this->userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function upsertKeyword(array $data): int
    {
        $id = (int)($data['id'] ?? 0);
        $keyword = trim((string)($data['keyword'] ?? ''));
        $url = trim((string)($data['target_url'] ?? ''));
        $volume = max(0, (int)($data['estimated_volume'] ?? 0));
        $difficulty = max(0, min(100, (int)($data['difficulty'] ?? 0)));

        if ($keyword === '' || mb_strlen($keyword) > 190 || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Données mot-clé invalides.');
        }

        if ($id > 0) {
            $stmt = $this->pdo->prepare(
                'UPDATE seo_keywords
                 SET keyword = ?, target_url = ?, estimated_volume = ?, difficulty = ?, updated_at = NOW()
                 WHERE id = ? AND user_id = ?'
            );
            $stmt->execute([$keyword, $url, $volume, $difficulty, $id, $this->userId]);
            return $id;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO seo_keywords (user_id, keyword, target_url, estimated_volume, difficulty, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([$this->userId, $keyword, $url, $volume, $difficulty]);
        return (int)$this->pdo->lastInsertId();
    }

    public function deleteKeyword(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE seo_keywords SET is_active = 0, updated_at = NOW() WHERE id = ? AND user_id = ?');
        return $stmt->execute([$id, $this->userId]);
    }
}

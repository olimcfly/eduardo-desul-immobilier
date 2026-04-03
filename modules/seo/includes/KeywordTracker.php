<?php
declare(strict_types=1);

class KeywordTracker
{
    public function __construct(private PDO $pdo, private int $userId)
    {
    }

    public function listKeywords(): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM seo_keywords WHERE user_id = :user_id ORDER BY created_at DESC');
        $stmt->execute(['user_id' => $this->userId]);

        return $stmt->fetchAll() ?: [];
    }

    public function saveKeyword(array $data): int
    {
        $keyword = trim((string)($data['keyword'] ?? ''));
        if ($keyword === '') {
            throw new InvalidArgumentException('Le mot-clé est obligatoire.');
        }

        $stmt = $this->pdo->prepare('INSERT INTO seo_keywords (user_id, keyword, target_url, volume, difficulty, created_at) VALUES (:user_id, :keyword, :target_url, :volume, :difficulty, NOW())');
        $stmt->execute([
            'user_id' => $this->userId,
            'keyword' => mb_substr($keyword, 0, 255),
            'target_url' => !empty($data['target_url']) ? mb_substr((string)$data['target_url'], 0, 500) : null,
            'volume' => ($data['volume'] ?? '') !== '' ? (int)$data['volume'] : null,
            'difficulty' => ($data['difficulty'] ?? '') !== '' ? max(0, min(100, (int)$data['difficulty'])) : null,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function mockCheckPosition(int $keywordId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, keyword, position FROM seo_keywords WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $keywordId, 'user_id' => $this->userId]);
        $keyword = $stmt->fetch();

        if (!$keyword) {
            throw new RuntimeException('Mot-clé introuvable.');
        }

        $newPosition = random_int(1, 40);
        $prevPosition = $keyword['position'] !== null ? (int)$keyword['position'] : null;

        $update = $this->pdo->prepare('UPDATE seo_keywords SET position_prev = :position_prev, position = :position, top10 = :top10, last_checked = NOW() WHERE id = :id AND user_id = :user_id');
        $update->execute([
            'position_prev' => $prevPosition,
            'position' => $newPosition,
            'top10' => $newPosition <= 10 ? 1 : 0,
            'id' => $keywordId,
            'user_id' => $this->userId,
        ]);

        $history = $this->pdo->prepare('INSERT INTO seo_keyword_history (keyword_id, position, checked_at) VALUES (:keyword_id, :position, NOW())');
        $history->execute(['keyword_id' => $keywordId, 'position' => $newPosition]);

        return ['keyword' => $keyword['keyword'], 'position' => $newPosition, 'position_prev' => $prevPosition];
    }
}

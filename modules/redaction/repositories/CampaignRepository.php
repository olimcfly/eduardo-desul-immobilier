<?php

declare(strict_types=1);

class CampaignRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.*, COUNT(ca.id) AS nb_articles
             FROM blog_campaigns c
             LEFT JOIN blog_campaign_articles ca ON ca.campaign_id = c.id
             WHERE c.user_id = ?
             GROUP BY c.id
             ORDER BY c.updated_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM blog_campaigns WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getArticles(int $campaignId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ca.*, a.titre, a.statut AS article_statut, a.slug
             FROM blog_campaign_articles ca
             LEFT JOIN blog_articles a ON a.id = ca.article_id
             WHERE ca.campaign_id = ?
             ORDER BY ca.ordre ASC'
        );
        $stmt->execute([$campaignId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function save(array $data): int
    {
        if (!empty($data['id'])) {
            $id = (int)$data['id'];
            unset($data['id']);
            $set = implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($data)));
            $stmt = $this->pdo->prepare("UPDATE blog_campaigns SET $set, updated_at = NOW() WHERE id = ?");
            $stmt->execute([...array_values($data), $id]);
            return $id;
        }
        $cols = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
        $vals = implode(', ', array_fill(0, count($data), '?'));
        $stmt = $this->pdo->prepare("INSERT INTO blog_campaigns ($cols) VALUES ($vals)");
        $stmt->execute(array_values($data));
        return (int)$this->pdo->lastInsertId();
    }

    public function saveArticles(int $campaignId, array $articles): void
    {
        $this->pdo->prepare('DELETE FROM blog_campaign_articles WHERE campaign_id = ?')
                  ->execute([$campaignId]);
        $stmt = $this->pdo->prepare(
            'INSERT INTO blog_campaign_articles (campaign_id, article_id, role, niveau_conscience, ordre)
             VALUES (?, ?, ?, ?, ?)'
        );
        foreach ($articles as $i => $a) {
            $stmt->execute([
                $campaignId,
                !empty($a['article_id']) ? (int)$a['article_id'] : null,
                $a['role'] ?? 'conscience',
                !empty($a['niveau_conscience']) ? (int)$a['niveau_conscience'] : null,
                $i,
            ]);
        }
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM blog_campaign_articles WHERE campaign_id = ?')->execute([$id]);
        $this->pdo->prepare('DELETE FROM blog_campaigns WHERE id = ?')->execute([$id]);
    }
}

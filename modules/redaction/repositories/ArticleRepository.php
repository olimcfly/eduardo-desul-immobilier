<?php

declare(strict_types=1);

class ArticleRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll(int $userId, array $filters = []): array
    {
        $where = ['a.user_id = ?'];
        $params = [$userId];

        if (!empty($filters['statut'])) {
            $where[] = 'a.statut = ?';
            $params[] = $filters['statut'];
        }
        if (!empty($filters['type'])) {
            $where[] = 'a.type = ?';
            $params[] = $filters['type'];
        }
        if (!empty($filters['q'])) {
            $where[] = 'a.titre LIKE ?';
            $params[] = '%' . $filters['q'] . '%';
        }

        $sql = 'SELECT a.*, s.nom AS silo_nom
                FROM blog_articles a
                LEFT JOIN blog_silos s ON s.id = a.silo_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY a.updated_at DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT a.*, s.nom AS silo_nom
             FROM blog_articles a
             LEFT JOIN blog_silos s ON s.id = a.silo_id
             WHERE a.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function save(array $data): int
    {
        if (!empty($data['id'])) {
            $id = (int)$data['id'];
            unset($data['id']);
            $set = implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($data)));
            $stmt = $this->pdo->prepare("UPDATE blog_articles SET $set WHERE id = ?");
            $stmt->execute([...array_values($data), $id]);
            return $id;
        }

        $cols = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
        $vals = implode(', ', array_fill(0, count($data), '?'));
        $stmt = $this->pdo->prepare("INSERT INTO blog_articles ($cols) VALUES ($vals)");
        $stmt->execute(array_values($data));
        return (int)$this->pdo->lastInsertId();
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM blog_articles WHERE id = ?')->execute([$id]);
        $this->pdo->prepare('DELETE FROM blog_keywords WHERE article_id = ?')->execute([$id]);
        $this->pdo->prepare('UPDATE blog_campaign_articles SET article_id = NULL WHERE article_id = ?')->execute([$id]);
    }

    public function countByStatut(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT statut, COUNT(*) AS n FROM blog_articles WHERE user_id = ? GROUP BY statut"
        );
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = ['brouillon' => 0, 'planifié' => 0, 'publié' => 0, 'archivé' => 0];
        foreach ($rows as $r) {
            $out[$r['statut']] = (int)$r['n'];
        }
        return $out;
    }

    public function searchForLink(int $userId, string $q): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, titre, slug FROM blog_articles
             WHERE user_id = ? AND titre LIKE ? AND statut != 'archivé'
             LIMIT 15"
        );
        $stmt->execute([$userId, '%' . $q . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveKeywords(int $articleId, int $websiteId, array $keywords): void
    {
        $this->pdo->prepare('DELETE FROM blog_keywords WHERE article_id = ?')->execute([$articleId]);
        if (empty($keywords)) {
            return;
        }
        $stmt = $this->pdo->prepare(
            'INSERT INTO blog_keywords (website_id, article_id, mot_cle, statut) VALUES (?, ?, ?, ?)'
        );
        foreach ($keywords as $kw) {
            $kw = trim((string)$kw);
            if ($kw !== '') {
                $stmt->execute([$websiteId, $articleId, $kw, 'validé']);
            }
        }
    }

    public function getKeywords(int $articleId): array
    {
        $stmt = $this->pdo->prepare('SELECT mot_cle FROM blog_keywords WHERE article_id = ?');
        $stmt->execute([$articleId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

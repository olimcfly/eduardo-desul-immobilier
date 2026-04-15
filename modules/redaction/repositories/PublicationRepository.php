<?php

declare(strict_types=1);

class PublicationRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll(int $userId, array $filters = []): array
    {
        $where = ['p.user_id = ?'];
        $params = [$userId];

        if (!empty($filters['reseau'])) {
            $where[] = 'p.reseau = ?';
            $params[] = $filters['reseau'];
        }
        if (!empty($filters['statut'])) {
            $where[] = 'p.statut = ?';
            $params[] = $filters['statut'];
        }

        $sql = 'SELECT p.*, a.titre AS article_titre, a.slug AS article_slug
                FROM blog_publications p
                LEFT JOIN blog_articles a ON a.id = p.article_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY COALESCE(p.planifie_at, p.created_at) DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM blog_publications WHERE id = ?');
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
            $stmt = $this->pdo->prepare("UPDATE blog_publications SET $set WHERE id = ?");
            $stmt->execute([...array_values($data), $id]);
            return $id;
        }
        $cols = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
        $vals = implode(', ', array_fill(0, count($data), '?'));
        $stmt = $this->pdo->prepare("INSERT INTO blog_publications ($cols) VALUES ($vals)");
        $stmt->execute(array_values($data));
        return (int)$this->pdo->lastInsertId();
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM blog_publications WHERE id = ?')->execute([$id]);
    }

    public function countByReseau(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT reseau, COUNT(*) AS n FROM blog_publications WHERE user_id = ? GROUP BY reseau"
        );
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = ['gmb' => 0, 'facebook' => 0, 'linkedin' => 0, 'instagram' => 0];
        foreach ($rows as $r) {
            $out[$r['reseau']] = (int)$r['n'];
        }
        return $out;
    }

    public function getJournal(int $userId, int $limit = 50): array
    {
        $sql = "
            (SELECT 'article' AS type, a.id, a.titre AS titre,
                    a.statut, a.type AS sous_type,
                    COALESCE(a.date_publication, a.updated_at) AS event_at,
                    a.slug, NULL AS reseau
             FROM blog_articles a
             WHERE a.user_id = ?
             ORDER BY event_at DESC
             LIMIT 100)
            UNION ALL
            (SELECT 'publication' AS type, p.id, COALESCE(p.titre, a.titre) AS titre,
                    p.statut, NULL AS sous_type,
                    COALESCE(p.planifie_at, p.created_at) AS event_at,
                    NULL AS slug, p.reseau
             FROM blog_publications p
             LEFT JOIN blog_articles a ON a.id = p.article_id
             WHERE p.user_id = ?
             ORDER BY event_at DESC
             LIMIT 100)
            ORDER BY event_at DESC
            LIMIT $limit
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

<?php

class Blog
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ── Liste articles publiés ────────────────────────────────
    public function getPublished(
        int $limit = 9,
        int $offset = 0,
        ?string $category = null,
        ?string $search = null
    ): array {
        $where = ["status = 'published'"];
        $params = [];

        if ($category) {
            $where[] = 'category = ?';
            $params[] = $category;
        }

        if ($search) {
            $where[] = '(title LIKE ? OR excerpt LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $sql = "SELECT id, title, slug, excerpt, category,
                       read_time, featured_image, published_at,
                       views, is_featured
                FROM   articles
                WHERE  " . implode(' AND ', $where) . "
                ORDER  BY is_featured DESC, published_at DESC
                LIMIT  ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Article par slug ──────────────────────────────────────
    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM articles
             WHERE slug = ? AND status = 'published'
             LIMIT 1"
        );
        $stmt->execute([$slug]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$article) {
            return null;
        }

        // Incrémente vues
        $this->db->prepare(
            'UPDATE articles SET views = views + 1 WHERE id = ?'
        )->execute([$article['id']]);

        return $article;
    }

    // ── Articles similaires ───────────────────────────────────
    public function getRelated(int $id, string $category, int $limit = 3): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, title, slug, excerpt, category,
                    read_time, featured_image, published_at
             FROM   articles
             WHERE  status = 'published'
               AND  category = ?
               AND  id != ?
             ORDER  BY published_at DESC
             LIMIT  ?"
        );
        $stmt->execute([$category, $id, $limit]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Catégories avec comptage ──────────────────────────────
    public function getCategories(): array
    {
        $stmt = $this->db->query(
            "SELECT category, COUNT(*) as total
             FROM   articles
             WHERE  status = 'published'
             GROUP  BY category
             ORDER  BY total DESC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Total pour pagination ─────────────────────────────────
    public function countPublished(?string $category = null, ?string $search = null): int
    {
        $where = ["status = 'published'"];
        $params = [];

        if ($category) {
            $where[] = 'category = ?';
            $params[] = $category;
        }

        if ($search) {
            $where[] = '(title LIKE ? OR excerpt LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM articles WHERE ' . implode(' AND ', $where)
        );
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }
}

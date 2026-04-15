<?php

class FunnelRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findAll(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['canal'])) {
            $where[] = 'canal = :canal';
            $params[':canal'] = $filters['canal'];
        }
        if (!empty($filters['ville'])) {
            $where[] = 'ville LIKE :ville';
            $params[':ville'] = '%' . $filters['ville'] . '%';
        }

        $sql = 'SELECT * FROM funnels WHERE ' . implode(' AND ', $where)
             . ' ORDER BY created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM funnels WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM funnels WHERE slug = :slug AND status = "published"');
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $fields = array_keys($data);
        $placeholders = array_map(fn($f) => ":$f", $fields);

        $sql = sprintf(
            'INSERT INTO funnels (%s) VALUES (%s)',
            implode(', ', $fields),
            implode(', ', $placeholders)
        );

        $params = [];
        foreach ($data as $k => $v) {
            $params[":$k"] = $v;
        }

        $this->db->prepare($sql)->execute($params);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sets = array_map(fn($f) => "$f = :$f", array_keys($data));
        $sql = 'UPDATE funnels SET ' . implode(', ', $sets) . ' WHERE id = :id';

        $params = [':id' => $id];
        foreach ($data as $k => $v) {
            $params[":$k"] = $v;
        }

        return $this->db->prepare($sql)->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM funnels WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function getStats(int $funnelId): array
    {
        $stmt = $this->db->prepare('
            SELECT
                event_type,
                COUNT(*) as total,
                COUNT(DISTINCT session_id) as unique_count
            FROM funnel_events
            WHERE funnel_id = :id
            GROUP BY event_type
        ');
        $stmt->execute([':id' => $funnelId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stats = ['view' => 0, 'submit' => 0, 'download' => 0, 'cta_click' => 0];
        foreach ($rows as $row) {
            $stats[$row['event_type']] = (int) $row['total'];
        }

        $stats['conversion_rate'] = $stats['view'] > 0
            ? round(($stats['submit'] / $stats['view']) * 100, 1)
            : 0;

        return $stats;
    }

    public function slugExists(string $slug, int $excludeId = 0): bool
    {
        $sql = 'SELECT COUNT(*) FROM funnels WHERE slug = :slug AND id != :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':slug' => $slug, ':id' => $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }
}

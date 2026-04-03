<?php
declare(strict_types=1);

final class BlogService
{
    public function __construct(private PDO $db)
    {
    }

    public function getDashboardData(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $whereSql = ['a.status != :archived'];
        $params = ['archived' => 'archived'];

        if (!empty($filters['status'])) {
            $whereSql[] = 'a.status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['persona_id'])) {
            $whereSql[] = 'a.persona_id = :persona_id';
            $params['persona_id'] = (int)$filters['persona_id'];
        }
        if (!empty($filters['region'])) {
            $whereSql[] = 'a.region = :region';
            $params['region'] = $filters['region'];
        }
        if (!empty($filters['consciousness_level'])) {
            $whereSql[] = 'a.consciousness_level = :consciousness_level';
            $params['consciousness_level'] = (int)$filters['consciousness_level'];
        }

        $whereClause = 'WHERE ' . implode(' AND ', $whereSql);

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM articles a {$whereClause}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $sql = "
            SELECT
                a.id,
                a.title,
                a.slug,
                a.position,
                a.position_trend,
                a.monthly_traffic,
                a.status,
                a.index_status,
                a.updated_at,
                p.name AS persona,
                k.keyword AS main_keyword,
                k.search_volume,
                k.competition,
                (k.search_volume / NULLIF(k.competition, 0)) AS golden_ratio
            FROM articles a
            LEFT JOIN personas p ON p.id = a.persona_id
            LEFT JOIN keywords k ON a.main_keyword = k.keyword
            {$whereClause}
            ORDER BY a.position ASC, a.updated_at DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(':' . $key, $value, $type);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'articles' => $stmt->fetchAll(),
            'stats' => [
                'positions' => $this->getPositionDistribution(),
                'golden_ratios' => $this->getGoldenRatioDistribution(),
                'traffic' => $this->getTotalTraffic(),
            ],
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => max(1, (int)ceil($total / $perPage)),
            ],
        ];
    }

    public function getPersonas(): array
    {
        return $this->db->query('SELECT id, name FROM personas ORDER BY name ASC')->fetchAll();
    }

    public function getArticleById(int $articleId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM articles WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $articleId]);
        $article = $stmt->fetch();

        return $article ?: null;
    }

    private function getPositionDistribution(): array
    {
        $sql = "
            SELECT
                CASE
                    WHEN position BETWEEN 1 AND 3 THEN 'Top 3'
                    WHEN position BETWEEN 4 AND 10 THEN 'Top 10'
                    WHEN position BETWEEN 11 AND 20 THEN '11-20'
                    ELSE '20+'
                END AS position_bucket,
                COUNT(*) AS article_count
            FROM articles
            WHERE status != 'archived'
            GROUP BY position_bucket
            ORDER BY article_count DESC
        ";

        return $this->db->query($sql)->fetchAll();
    }

    private function getGoldenRatioDistribution(): array
    {
        $sql = "
            SELECT
                CASE
                    WHEN (k.search_volume / NULLIF(k.competition, 0)) > 1.5 THEN 'Excellent'
                    WHEN (k.search_volume / NULLIF(k.competition, 0)) > 1 THEN 'Bon'
                    ELSE 'Faible'
                END AS ratio_bucket,
                COUNT(*) AS ratio_count
            FROM articles a
            LEFT JOIN keywords k ON a.main_keyword = k.keyword
            WHERE a.status != 'archived'
            GROUP BY ratio_bucket
        ";

        return $this->db->query($sql)->fetchAll();
    }

    private function getTotalTraffic(): array
    {
        $sql = "
            SELECT
                COALESCE(SUM(monthly_traffic), 0) AS total_traffic,
                COALESCE(SUM(monthly_traffic_prev), 0) AS total_traffic_prev
            FROM articles
            WHERE status != 'archived'
        ";

        $stats = $this->db->query($sql)->fetch() ?: ['total_traffic' => 0, 'total_traffic_prev' => 0];
        $prev = (int)$stats['total_traffic_prev'];
        $current = (int)$stats['total_traffic'];

        return [
            'current' => $current,
            'previous' => $prev,
            'delta_percent' => $prev > 0 ? round((($current - $prev) / $prev) * 100, 1) : 100.0,
        ];
    }
}

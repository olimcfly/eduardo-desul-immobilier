<?php
declare(strict_types=1);

final class SeoService
{
    public function __construct(private PDO $db)
    {
    }

    public function analyzeArticleContent(string $content): array
    {
        $plainText = trim(strip_tags($content));
        $wordCount = str_word_count(mb_strtolower($plainText, 'UTF-8'));
        $readingTime = max(1, (int)ceil($wordCount / 200));
        $h2Count = preg_match_all('/<h2\b[^>]*>/i', $content) ?: 0;

        $keywords = $this->extractKeywords($plainText);
        $keywordDensity = $this->calculateKeywordDensity($plainText, $keywords);

        return [
            'word_count' => $wordCount,
            'reading_time' => $readingTime,
            'h2_count' => $h2Count,
            'keywords' => $keywords,
            'keyword_density' => $keywordDensity,
            'seo_score' => $this->calculateSEOScore($wordCount, $h2Count, $keywordDensity),
        ];
    }

    public function getKeywords(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = 'status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['intent'])) {
            $where[] = 'search_intent = :intent';
            $params['intent'] = $filters['intent'];
        }
        if (!empty($filters['volume_min'])) {
            $where[] = 'search_volume >= :volume_min';
            $params['volume_min'] = (int)$filters['volume_min'];
        }
        if (!empty($filters['volume_max'])) {
            $where[] = 'search_volume <= :volume_max';
            $params['volume_max'] = (int)$filters['volume_max'];
        }
        if (!empty($filters['competition_band'])) {
            if ($filters['competition_band'] === 'low') {
                $where[] = 'competition < 30';
            } elseif ($filters['competition_band'] === 'mid') {
                $where[] = 'competition BETWEEN 30 AND 60';
            } elseif ($filters['competition_band'] === 'high') {
                $where[] = 'competition > 60';
            }
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT
                id,
                keyword,
                search_volume,
                competition,
                (search_volume / NULLIF(competition, 0)) AS golden_ratio,
                search_intent,
                status,
                position,
                position_trend
            FROM keywords
            {$whereSql}
            ORDER BY golden_ratio DESC, search_volume DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function getKeywordOpportunities(): array
    {
        return $this->db->query(" 
            SELECT keyword, search_volume, competition,
                   (search_volume / NULLIF(competition, 0)) as golden_ratio,
                   position, position_trend
            FROM keywords
            WHERE status = 'pending'
              AND (search_volume / NULLIF(competition, 0)) > 1.5
              AND competition < 30
            ORDER BY golden_ratio DESC
            LIMIT 20
        ")->fetchAll();
    }

    public function getSerpSimulation(string $keyword): array
    {
        $stmt = $this->db->prepare('SELECT * FROM serp_snapshots WHERE keyword = :keyword ORDER BY rank_position ASC LIMIT 10');
        $stmt->execute(['keyword' => $keyword]);

        return $stmt->fetchAll();
    }

    public function getSilos(): array
    {
        return $this->db->query('SELECT id, name FROM silos ORDER BY name ASC')->fetchAll();
    }

    public function getSiloStructure(int $siloId): array
    {
        $pillarStmt = $this->db->prepare(" 
            SELECT a.*, s.position as silo_position, p.name AS persona
            FROM articles a
            JOIN silo_articles s ON a.id = s.article_id
            LEFT JOIN personas p ON a.persona_id = p.id
            WHERE s.silo_id = :silo_id AND s.position = 0
        ");
        $pillarStmt->execute(['silo_id' => $siloId]);

        $satStmt = $this->db->prepare(" 
            SELECT a.*, s.position as silo_position, p.name as persona
            FROM articles a
            JOIN silo_articles s ON a.id = s.article_id
            LEFT JOIN personas p ON a.persona_id = p.id
            WHERE s.silo_id = :silo_id AND s.position > 0
            ORDER BY s.position ASC
        ");
        $satStmt->execute(['silo_id' => $siloId]);

        return [
            'pillar' => $pillarStmt->fetch() ?: null,
            'satellites' => $satStmt->fetchAll(),
        ];
    }

    public function getSiloOpportunities(int $siloId): array
    {
        $stmt = $this->db->prepare(" 
            SELECT title, description, position, impact
            FROM silo_opportunities
            WHERE silo_id = :silo_id
            ORDER BY impact DESC
        ");
        $stmt->execute(['silo_id' => $siloId]);

        return $stmt->fetchAll();
    }

    private function extractKeywords(string $content): array
    {
        $content = mb_strtolower($content, 'UTF-8');
        $tokens = preg_split('/\PL+/u', $content) ?: [];

        $stopWords = ['de', 'la', 'le', 'les', 'des', 'un', 'une', 'du', 'et', 'ou', 'pour', 'dans', 'sur', 'avec'];
        $filtered = array_filter($tokens, static fn (string $word): bool => mb_strlen($word) > 3 && !in_array($word, $stopWords, true));

        $counts = array_count_values($filtered);
        arsort($counts);

        return array_slice(array_keys($counts), 0, 8);
    }

    private function calculateKeywordDensity(string $content, array $keywords): array
    {
        $wordCount = max(1, str_word_count(mb_strtolower($content, 'UTF-8')));
        $densities = [];

        foreach ($keywords as $keyword) {
            $occurrences = preg_match_all('/\b' . preg_quote($keyword, '/') . '\b/ui', mb_strtolower($content, 'UTF-8')) ?: 0;
            $densities[] = [
                'keyword' => $keyword,
                'occurrences' => $occurrences,
                'density' => round(($occurrences / $wordCount) * 100, 2),
            ];
        }

        return $densities;
    }

    private function calculateSEOScore(int $wordCount, int $h2Count, array $keywordDensity): int
    {
        $score = 0;
        $score += min(30, (int)($wordCount / 20));
        $score += min(20, $h2Count * 5);
        $score += min(50, (int)round(array_sum(array_column($keywordDensity, 'density')) * 3));

        return (int)min(100, $score);
    }
}

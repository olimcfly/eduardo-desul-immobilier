<?php
declare(strict_types=1);

class PerformanceAudit
{
    public function __construct(private PDO $pdo, private int $userId)
    {
    }

    public function runMockAudit(string $url): array
    {
        $result = [
            'url_tested' => $url,
            'score_perf' => random_int(55, 99),
            'score_seo' => random_int(70, 100),
            'score_access' => random_int(65, 100),
            'score_bp' => random_int(70, 100),
            'lcp' => round(random_int(120, 450) / 100, 2),
            'fid' => round(random_int(4, 30) / 100, 2),
            'cls' => round(random_int(1, 25) / 1000, 3),
            'ttfb' => round(random_int(40, 200) / 100, 2),
            'issues' => [
                'images_non_optimisees' => random_int(0, 8),
                'liens_sans_title' => random_int(0, 6),
                'css_bloquant' => random_int(0, 3),
            ],
        ];

        $stmt = $this->pdo->prepare('INSERT INTO seo_audits (user_id, url_tested, score_perf, score_seo, score_access, score_bp, lcp, fid, cls, ttfb, issues, raw_report, created_at) VALUES (:user_id, :url_tested, :score_perf, :score_seo, :score_access, :score_bp, :lcp, :fid, :cls, :ttfb, :issues, :raw_report, NOW())');
        $stmt->execute([
            'user_id' => $this->userId,
            'url_tested' => $result['url_tested'],
            'score_perf' => $result['score_perf'],
            'score_seo' => $result['score_seo'],
            'score_access' => $result['score_access'],
            'score_bp' => $result['score_bp'],
            'lcp' => $result['lcp'],
            'fid' => $result['fid'],
            'cls' => $result['cls'],
            'ttfb' => $result['ttfb'],
            'issues' => json_encode($result['issues'], JSON_UNESCAPED_UNICODE),
            'raw_report' => json_encode($result, JSON_UNESCAPED_UNICODE),
        ]);

        $result['id'] = (int)$this->pdo->lastInsertId();

        return $result;
    }

    public function listAudits(int $limit = 15): array
    {
        $limit = max(1, min(100, $limit));
        $stmt = $this->pdo->prepare("SELECT * FROM seo_audits WHERE user_id = :user_id ORDER BY created_at DESC LIMIT {$limit}");
        $stmt->execute(['user_id' => $this->userId]);

        return $stmt->fetchAll() ?: [];
    }
}

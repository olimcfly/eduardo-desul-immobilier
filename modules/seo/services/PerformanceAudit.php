<?php

declare(strict_types=1);

class PerformanceAudit
{
    public function __construct(private PDO $pdo, private int $userId)
    {
    }

    public function runAudit(string $url, string $device = 'mobile'): array
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('URL invalide.');
        }

        $key = (string)setting('tech_google_psi_key', '', $this->userId);
        $api = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=' . rawurlencode($url) . '&strategy=' . rawurlencode($device);
        if ($key !== '') {
            $api .= '&key=' . rawurlencode($key);
        }

        $ch = curl_init($api);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);

        $response = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $status >= 400) {
            throw new RuntimeException('API PageSpeed indisponible: ' . ($error !== '' ? $error : 'HTTP ' . $status));
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Réponse API invalide.');
        }

        return [
            'scores' => $this->parsePageSpeed($decoded),
            'core_web_vitals' => $this->getCoreWebVitals($decoded),
            'opportunities' => $this->getOpportunities($decoded),
            'diagnostics' => $this->getDiagnostics($decoded),
            'raw' => $decoded,
        ];
    }

    public function parsePageSpeed(array $response): array
    {
        $categories = $response['lighthouseResult']['categories'] ?? [];
        return [
            'performance' => (int)round(((float)($categories['performance']['score'] ?? 0)) * 100),
            'seo' => (int)round(((float)($categories['seo']['score'] ?? 0)) * 100),
            'accessibility' => (int)round(((float)($categories['accessibility']['score'] ?? 0)) * 100),
            'best_practices' => (int)round(((float)($categories['best-practices']['score'] ?? 0)) * 100),
        ];
    }

    public function getCoreWebVitals(array $response): array
    {
        $audits = $response['lighthouseResult']['audits'] ?? [];
        return [
            'lcp' => (int)($audits['largest-contentful-paint']['numericValue'] ?? 0),
            'inp' => (int)($audits['interaction-to-next-paint']['numericValue'] ?? $audits['max-potential-fid']['numericValue'] ?? 0),
            'cls' => (float)($audits['cumulative-layout-shift']['numericValue'] ?? 0),
            'ttfb' => (int)($audits['server-response-time']['numericValue'] ?? 0),
        ];
    }

    public function getOpportunities(array $response): array
    {
        $audits = $response['lighthouseResult']['audits'] ?? [];
        $list = [];
        foreach ($audits as $audit) {
            if (($audit['details']['type'] ?? '') === 'opportunity') {
                $list[] = [
                    'title' => $audit['title'] ?? 'Opportunité',
                    'description' => $audit['description'] ?? '',
                    'savings_ms' => (int)($audit['details']['overallSavingsMs'] ?? 0),
                ];
            }
        }
        return $list;
    }

    public function getDiagnostics(array $response): array
    {
        $audits = $response['lighthouseResult']['audits'] ?? [];
        $list = [];
        foreach ($audits as $audit) {
            if (($audit['scoreDisplayMode'] ?? '') === 'informative') {
                $list[] = [
                    'title' => $audit['title'] ?? 'Diagnostic',
                    'description' => $audit['description'] ?? '',
                ];
            }
        }
        return $list;
    }

    public function saveAudit(int $userId, array $results): int
    {
        $scores = $results['scores'] ?? [];
        $vitals = $results['core_web_vitals'] ?? [];
        $stmt = $this->pdo->prepare(
            'INSERT INTO seo_performance_audits
            (user_id, audited_url, device, perf_score, seo_score, access_score, bp_score, lcp_ms, inp_ms, cls_score, ttfb_ms, raw_payload, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
        );

        $stmt->execute([
            $userId,
            (string)($results['url'] ?? ''),
            (string)($results['device'] ?? 'mobile'),
            (int)($scores['performance'] ?? 0),
            (int)($scores['seo'] ?? 0),
            (int)($scores['accessibility'] ?? 0),
            (int)($scores['best_practices'] ?? 0),
            (int)($vitals['lcp'] ?? 0),
            (int)($vitals['inp'] ?? 0),
            (float)($vitals['cls'] ?? 0),
            (int)($vitals['ttfb'] ?? 0),
            json_encode($results['raw'] ?? [], JSON_UNESCAPED_UNICODE),
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function compareAudits(int $id1, int $id2): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM seo_performance_audits WHERE id IN (?, ?) AND user_id = ?');
        $stmt->execute([$id1, $id2, $this->userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        if (count($rows) !== 2) {
            return [];
        }

        [$a, $b] = $rows;
        return [
            'perf_diff' => (int)$b['perf_score'] - (int)$a['perf_score'],
            'seo_diff' => (int)$b['seo_score'] - (int)$a['seo_score'],
            'lcp_diff' => (int)$b['lcp_ms'] - (int)$a['lcp_ms'],
            'inp_diff' => (int)$b['inp_ms'] - (int)$a['inp_ms'],
            'cls_diff' => (float)$b['cls_score'] - (float)$a['cls_score'],
        ];
    }
}

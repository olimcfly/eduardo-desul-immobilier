<?php

declare(strict_types=1);

final class SeoTechnicalPerformanceService
{
    public function __construct(private PDO $pdo, private int $userId)
    {
        $this->ensureSchema();
    }

    private function ensureSchema(): void
    {
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS seo_technical_audits (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                advisor_id BIGINT UNSIGNED NULL,
                website_id BIGINT UNSIGNED NULL,
                page_url VARCHAR(255) NOT NULL,
                page_type VARCHAR(50) NOT NULL,
                global_score TINYINT UNSIGNED NOT NULL DEFAULT 0,
                lcp DECIMAL(10,2) NULL,
                cls DECIMAL(8,4) NULL,
                inp DECIMAL(10,2) NULL,
                load_time_ms INT UNSIGNED NULL,
                page_weight_kb INT UNSIGNED NULL,
                seo_meta_ok TINYINT(1) NOT NULL DEFAULT 0,
                broken_links_count INT UNSIGNED NOT NULL DEFAULT 0,
                image_issues_count INT UNSIGNED NOT NULL DEFAULT 0,
                audited_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_audits_advisor_date (advisor_id, audited_at),
                KEY idx_audits_page (page_url)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS seo_audit_issues (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                audit_id BIGINT UNSIGNED NOT NULL,
                severity ENUM('critical','important','minor') NOT NULL DEFAULT 'minor',
                issue_code VARCHAR(80) NOT NULL,
                issue_label VARCHAR(190) NOT NULL,
                issue_description TEXT NOT NULL,
                recommended_action TEXT NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_issues_audit (audit_id),
                KEY idx_issues_severity (severity),
                CONSTRAINT fk_issues_audit FOREIGN KEY (audit_id)
                    REFERENCES seo_technical_audits(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    public function listTargetPagesForAudit(): array
    {
        $targets = [
            ['url' => '/', 'type' => 'home', 'label' => 'Accueil'],
            ['url' => '/estimation-gratuite', 'type' => 'estimation', 'label' => 'Estimation gratuite'],
        ];

        $targets = array_merge($targets, $this->fetchCityPages());
        $targets = array_merge($targets, $this->fetchBlogPages());
        $targets = array_merge($targets, $this->fetchSectorPages());

        $seen = [];
        $unique = [];
        foreach ($targets as $target) {
            if (isset($seen[$target['url']])) {
                continue;
            }
            $seen[$target['url']] = true;
            $unique[] = $target;
        }

        return $unique;
    }

    private function fetchCityPages(): array
    {
        if (!$this->tableExists('seo_city_pages')) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            "SELECT slug, city FROM seo_city_pages
             WHERE user_id = ? AND status = 'published'
             ORDER BY updated_at DESC
             LIMIT 5"
        );
        $stmt->execute([$this->userId]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return array_map(static fn(array $r): array => [
            'url' => '/ville/' . (string)$r['slug'],
            'type' => 'fiche_ville',
            'label' => (string)$r['city'],
        ], $rows);
    }

    private function fetchBlogPages(): array
    {
        if (!$this->tableExists('articles')) {
            return [];
        }

        $stmt = $this->pdo->query(
            "SELECT slug, titre FROM articles
             WHERE statut = 'publie'
             ORDER BY COALESCE(published_at, updated_at, created_at) DESC
             LIMIT 5"
        );

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return array_map(static fn(array $r): array => [
            'url' => '/blog/' . (string)$r['slug'],
            'type' => 'article',
            'label' => (string)$r['titre'],
        ], $rows);
    }

    private function fetchSectorPages(): array
    {
        if (!$this->tableExists('secteurs')) {
            return [];
        }

        try {
            $stmt = $this->pdo->query('SELECT slug, nom FROM secteurs ORDER BY id DESC LIMIT 3');
        } catch (Throwable) {
            return [];
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return array_map(static fn(array $r): array => [
            'url' => '/secteurs/' . (string)$r['slug'],
            'type' => 'secteur',
            'label' => (string)$r['nom'],
        ], $rows);
    }

    public function runBatchAudit(): array
    {
        $targets = $this->listTargetPagesForAudit();
        $results = [];

        foreach ($targets as $target) {
            $results[] = $this->runAuditForPath($target['url'], $target['type']);
        }

        return $results;
    }

    public function runAuditForPath(string $path, string $pageType): array
    {
        $auditResult = $this->analyzeUrl($path, $pageType);
        $auditId = $this->saveAudit($auditResult);
        $this->saveIssues($auditId, $auditResult['issues']);

        return ['audit_id' => $auditId] + $auditResult;
    }

    private function analyzeUrl(string $path, string $pageType): array
    {
        $fullUrl = rtrim((string)APP_URL, '/') . '/' . ltrim($path, '/');
        if ($path === '/') {
            $fullUrl = rtrim((string)APP_URL, '/') . '/';
        }

        $start = microtime(true);
        $html = $this->fetchUrl($fullUrl);
        $loadTimeMs = (int)round((microtime(true) - $start) * 1000);

        $pageWeightKb = (int)round(strlen($html) / 1024);

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        @$dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $title = trim((string)$xpath->evaluate('string(//title)'));
        $metaDesc = trim((string)$xpath->evaluate("string(//meta[@name='description']/@content)"));
        $h1Count = (int)$xpath->evaluate('count(//h1)');

        $imgWithoutAlt = (int)$xpath->evaluate('count(//img[not(@alt) or normalize-space(@alt)=""])');
        $brokenLinks = $this->countBrokenLinks($xpath, $fullUrl);

        $issues = $this->buildIssues($title, $metaDesc, $h1Count, $imgWithoutAlt, $brokenLinks, $loadTimeMs, $pageWeightKb);
        $score = $this->computeScore($issues, $title, $metaDesc, $h1Count);

        return [
            'advisor_id' => $this->userId,
            'page_url' => $path,
            'page_type' => $pageType,
            'global_score' => $score,
            'lcp' => null,
            'cls' => null,
            'inp' => null,
            'load_time_ms' => $loadTimeMs,
            'page_weight_kb' => $pageWeightKb,
            'seo_meta_ok' => (int)($title !== '' && $metaDesc !== '' && $h1Count > 0),
            'broken_links_count' => $brokenLinks,
            'image_issues_count' => $imgWithoutAlt,
            'issues' => $issues,
        ];
    }

    private function fetchUrl(string $url): string
    {
        $context = stream_context_create([
            'http' => ['timeout' => 8, 'ignore_errors' => true, 'header' => "User-Agent: IMMOLOCAL-SeoAudit/1.0\r\n"],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);

        $html = @file_get_contents($url, false, $context);
        if ($html === false || trim($html) === '') {
            throw new RuntimeException('Impossible de charger ' . $url);
        }

        return $html;
    }

    private function countBrokenLinks(DOMXPath $xpath, string $pageUrl): int
    {
        $nodes = $xpath->query('//a[@href]');
        if (!$nodes instanceof DOMNodeList) {
            return 0;
        }

        $checked = 0;
        $broken = 0;
        $base = parse_url($pageUrl);

        foreach ($nodes as $node) {
            if ($checked >= 12) {
                break;
            }
            $href = trim((string)$node->attributes?->getNamedItem('href')?->nodeValue);
            if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
                continue;
            }

            $absolute = $href;
            if (str_starts_with($href, '/')) {
                $absolute = ($base['scheme'] ?? 'https') . '://' . ($base['host'] ?? '') . $href;
            }

            if (!str_starts_with($absolute, ($base['scheme'] ?? 'https') . '://' . ($base['host'] ?? ''))) {
                continue;
            }

            $checked++;
            if (!$this->isHttpOk($absolute)) {
                $broken++;
            }
        }

        return $broken;
    }

    private function isHttpOk(string $url): bool
    {
        $headers = @get_headers($url);
        if (!is_array($headers) || empty($headers[0])) {
            return false;
        }

        return !preg_match('/\s(4\d\d|5\d\d)\s/', (string)$headers[0]);
    }

    private function buildIssues(string $title, string $metaDesc, int $h1Count, int $imgWithoutAlt, int $brokenLinks, int $loadTimeMs, int $pageWeightKb): array
    {
        $issues = [];

        if ($title === '') {
            $issues[] = $this->issue('critical', 'MISSING_TITLE', 'Titre SEO manquant', 'La page ne contient pas de balise title.', 'Définir un titre unique orienté requête locale.');
        } elseif (mb_strlen($title) < 30 || mb_strlen($title) > 65) {
            $issues[] = $this->issue('important', 'TITLE_LENGTH', 'Longueur du titre à optimiser', 'Le titre est trop court ou trop long pour Google.', 'Viser 45 à 60 caractères.');
        }

        if ($metaDesc === '') {
            $issues[] = $this->issue('important', 'MISSING_META_DESC', 'Meta description absente', 'Aucune meta description détectée.', 'Rédiger 1 description de 120 à 160 caractères orientée vendeur/acquéreur local.');
        } elseif (mb_strlen($metaDesc) < 90 || mb_strlen($metaDesc) > 170) {
            $issues[] = $this->issue('minor', 'META_DESC_LENGTH', 'Longueur meta description à ajuster', 'La meta description ne respecte pas la plage recommandée.', 'Viser 120 à 160 caractères.');
        }

        if ($h1Count === 0) {
            $issues[] = $this->issue('critical', 'MISSING_H1', 'H1 absent', 'La page ne contient pas de H1.', 'Ajouter un H1 clair lié au besoin local.');
        } elseif ($h1Count > 1) {
            $issues[] = $this->issue('important', 'MULTIPLE_H1', 'Plusieurs H1 détectés', 'Plusieurs titres principaux compliquent la lecture SEO.', 'Conserver un seul H1 principal par page.');
        }

        if ($imgWithoutAlt > 0) {
            $severity = $imgWithoutAlt >= 5 ? 'important' : 'minor';
            $issues[] = $this->issue($severity, 'IMAGES_ALT_MISSING', 'Images sans texte alternatif', $imgWithoutAlt . ' image(s) sans attribut alt.', 'Ajouter des textes alt descriptifs et locaux (quartier, type de bien…).');
        }

        if ($brokenLinks > 0) {
            $severity = $brokenLinks >= 3 ? 'critical' : 'important';
            $issues[] = $this->issue($severity, 'BROKEN_LINKS', 'Liens cassés détectés', $brokenLinks . ' lien(s) internes semblent en erreur.', 'Corriger ou rediriger les liens en erreur en priorité.');
        }

        if ($loadTimeMs > 2000) {
            $issues[] = $this->issue('important', 'SLOW_LOAD_TIME', 'Temps de chargement élevé', 'La page répond lentement pour un visiteur.', 'Compresser les médias et limiter les scripts non essentiels.');
        }

        if ($pageWeightKb > 1200) {
            $issues[] = $this->issue('minor', 'PAGE_WEIGHT_HIGH', 'Page lourde', 'Le poids de la page est élevé.', 'Alléger les images et limiter les bibliothèques CSS/JS.');
        }

        return $issues;
    }

    private function issue(string $severity, string $code, string $label, string $description, string $action): array
    {
        return [
            'severity' => $severity,
            'issue_code' => $code,
            'issue_label' => $label,
            'issue_description' => $description,
            'recommended_action' => $action,
        ];
    }

    private function computeScore(array $issues, string $title, string $metaDesc, int $h1Count): int
    {
        $score = 100;
        foreach ($issues as $issue) {
            $score -= match ($issue['severity']) {
                'critical' => 18,
                'important' => 10,
                default => 4,
            };
        }

        if ($title !== '' && $metaDesc !== '' && $h1Count > 0) {
            $score += 4;
        }

        return max(15, min(100, $score));
    }

    private function saveAudit(array $audit): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO seo_technical_audits
            (advisor_id, website_id, page_url, page_type, global_score, lcp, cls, inp, load_time_ms, page_weight_kb, seo_meta_ok, broken_links_count, image_issues_count, audited_at, created_at)
             VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );

        $stmt->execute([
            $audit['advisor_id'],
            $audit['page_url'],
            $audit['page_type'],
            $audit['global_score'],
            $audit['lcp'],
            $audit['cls'],
            $audit['inp'],
            $audit['load_time_ms'],
            $audit['page_weight_kb'],
            $audit['seo_meta_ok'],
            $audit['broken_links_count'],
            $audit['image_issues_count'],
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    private function saveIssues(int $auditId, array $issues): void
    {
        if (!$issues) {
            return;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO seo_audit_issues (audit_id, severity, issue_code, issue_label, issue_description, recommended_action, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())'
        );

        foreach ($issues as $issue) {
            $stmt->execute([
                $auditId,
                $issue['severity'],
                $issue['issue_code'],
                $issue['issue_label'],
                $issue['issue_description'],
                $issue['recommended_action'],
            ]);
        }
    }

    public function getDashboardData(): array
    {
        $latest = $this->pdo->prepare('SELECT * FROM seo_technical_audits WHERE advisor_id = ? ORDER BY audited_at DESC LIMIT 20');
        $latest->execute([$this->userId]);
        $audits = $latest->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $score = 0;
        if ($audits) {
            $score = (int)round(array_sum(array_map(static fn(array $a): int => (int)$a['global_score'], $audits)) / count($audits));
        }

        $issuesStmt = $this->pdo->prepare(
            "SELECT i.severity, COUNT(*) AS total
             FROM seo_audit_issues i
             JOIN seo_technical_audits a ON a.id = i.audit_id
             WHERE a.advisor_id = ? AND a.audited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY i.severity"
        );
        $issuesStmt->execute([$this->userId]);
        $issuesRaw = $issuesStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $issueCounts = ['critical' => 0, 'important' => 0, 'minor' => 0];
        foreach ($issuesRaw as $row) {
            $issueCounts[(string)$row['severity']] = (int)$row['total'];
        }

        return [
            'global_score' => $score,
            'status' => $this->scoreStatus($score),
            'latest_audits' => $audits,
            'issue_counts' => $issueCounts,
            'target_pages' => $this->listTargetPagesForAudit(),
        ];
    }

    public function getAuditsList(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT a.*, COALESCE(i.total_issues, 0) AS total_issues
             FROM seo_technical_audits a
             LEFT JOIN (
                 SELECT audit_id, COUNT(*) AS total_issues
                 FROM seo_audit_issues
                 GROUP BY audit_id
             ) i ON i.audit_id = a.id
             WHERE a.advisor_id = ?
             ORDER BY a.audited_at DESC
             LIMIT 100"
        );
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getAuditDetail(int $auditId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM seo_technical_audits WHERE id = ? AND advisor_id = ? LIMIT 1');
        $stmt->execute([$auditId, $this->userId]);
        $audit = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$audit) {
            return null;
        }

        $issues = $this->pdo->prepare('SELECT * FROM seo_audit_issues WHERE audit_id = ? ORDER BY FIELD(severity, "critical", "important", "minor"), id ASC');
        $issues->execute([$auditId]);

        return [
            'audit' => $audit,
            'issues' => $issues->fetchAll(PDO::FETCH_ASSOC) ?: [],
        ];
    }

    private function scoreStatus(int $score): string
    {
        return match (true) {
            $score >= 80 => 'bon',
            $score >= 60 => 'moyen',
            default => 'a_corriger',
        };
    }

    private function tableExists(string $table): bool
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            return false;
        }
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?'
        );
        $stmt->execute([$table]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function getHubPerformanceSummary(): array
    {
        $stmt = $this->pdo->prepare('SELECT global_score FROM seo_technical_audits WHERE advisor_id = ? ORDER BY audited_at DESC LIMIT 10');
        $stmt->execute([$this->userId]);
        $scores = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

        if (!$scores) {
            return ['score' => null, 'status' => 'non_audite'];
        }

        $avg = (int)round(array_sum(array_map('intval', $scores)) / count($scores));
        return ['score' => $avg, 'status' => $this->scoreStatus($avg)];
    }
}

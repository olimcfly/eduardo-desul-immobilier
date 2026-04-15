<?php

declare(strict_types=1);

final class MonthlyReportService
{
    public function __construct(private PDO $pdo)
    {
        $this->ensureReportsTable();
    }

    public function generate(int $userId, DateTimeImmutable $monthDate): array
    {
        $periodStart = $monthDate->modify('first day of this month')->setTime(0, 0, 0);
        $periodEnd = $monthDate->modify('last day of this month')->setTime(23, 59, 59);

        $leadsTotal = $this->countLeads($periodStart, $periodEnd);
        $leadsBySource = $this->countLeadsBySource($periodStart, $periodEnd);
        $conversions = $this->countLeadConversions($periodStart, $periodEnd);
        $articlesPublished = $this->countPublishedArticles($userId, $periodStart, $periodEnd);
        $socialPostsPublished = $this->countPublishedSocialPosts($userId, $periodStart, $periodEnd);

        $conversionRate = $leadsTotal > 0 ? round(($conversions / $leadsTotal) * 100, 1) : 0.0;

        return [
            'user_id' => $userId,
            'month' => $periodStart->format('Y-m'),
            'month_label' => strftime('%B %Y', $periodStart->getTimestamp()),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'leads_total' => $leadsTotal,
            'leads_by_source' => $leadsBySource,
            'conversions' => $conversions,
            'conversion_rate' => $conversionRate,
            'articles_published' => $articlesPublished,
            'social_posts_published' => $socialPostsPublished,
        ];
    }

    public function generateAndPersist(int $userId, DateTimeImmutable $monthDate): array
    {
        $report = $this->generate($userId, $monthDate);

        $html = $this->renderHtml($report);
        $baseDir = ROOT_PATH . '/logs/monthly-reports/user-' . $userId;
        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0775, true);
        }

        $htmlPath = $baseDir . '/report-' . $report['month'] . '.html';
        file_put_contents($htmlPath, $html);

        $pdfPath = null;
        if (class_exists('Dompdf\\Dompdf')) {
            try {
                $dompdf = new Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $pdfPath = $baseDir . '/report-' . $report['month'] . '.pdf';
                file_put_contents($pdfPath, $dompdf->output());
            } catch (Throwable $e) {
                error_log('MonthlyReportService PDF generation error: ' . $e->getMessage());
                $pdfPath = null;
            }
        }

        $report['html'] = $html;
        $report['html_path'] = $htmlPath;
        $report['pdf_path'] = $pdfPath;
        $report['format'] = $pdfPath ? 'pdf' : 'html';

        return $report;
    }

    public function sendMonthlyReport(int $userId, DateTimeImmutable $monthDate, string $recipientEmail): array
    {
        $report = $this->generateAndPersist($userId, $monthDate);

        $subject = sprintf('Rapport mensuel Optimiser — %s', ucfirst((string) $report['month_label']));
        $textBody = $this->renderText($report);

        $emailSent = MailService::send($recipientEmail, $subject, $textBody, $report['html']);

        $this->saveReportLog(
            $userId,
            (string) $report['month'],
            (string) $report['format'],
            (string) ($report['pdf_path'] ?? $report['html_path'] ?? ''),
            $recipientEmail,
            $emailSent
        );

        $report['email_sent'] = $emailSent;
        $report['recipient_email'] = $recipientEmail;

        return $report;
    }

    public function sendCurrentMonthIfDue(int $userId, string $recipientEmail): ?array
    {
        $today = new DateTimeImmutable('now');
        $lastDay = $today->modify('last day of this month')->format('Y-m-d');
        if ($today->format('Y-m-d') !== $lastDay) {
            return null;
        }

        $month = $today->format('Y-m');
        if ($this->isAlreadySent($userId, $month)) {
            return null;
        }

        return $this->sendMonthlyReport($userId, $today, $recipientEmail);
    }

    private function countLeads(DateTimeImmutable $start, DateTimeImmutable $end): int
    {
        if (!$this->tableExists('crm_leads')) {
            return 0;
        }

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM crm_leads WHERE created_at BETWEEN :start AND :end');
        $stmt->execute([
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s'),
        ]);

        return (int) $stmt->fetchColumn();
    }

    private function countLeadsBySource(DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        if (!$this->tableExists('crm_leads')) {
            return [];
        }

        $stmt = $this->pdo->prepare('SELECT source_type, COUNT(*) AS total FROM crm_leads WHERE created_at BETWEEN :start AND :end GROUP BY source_type ORDER BY total DESC');
        $stmt->execute([
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s'),
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $result = [];
        foreach ($rows as $row) {
            $source = (string) ($row['source_type'] ?? 'autre');
            $result[$source] = (int) ($row['total'] ?? 0);
        }

        return $result;
    }

    private function countLeadConversions(DateTimeImmutable $start, DateTimeImmutable $end): int
    {
        if (!$this->tableExists('crm_leads')) {
            return 0;
        }

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM crm_leads WHERE stage = "converti" AND updated_at BETWEEN :start AND :end');
        $stmt->execute([
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s'),
        ]);

        return (int) $stmt->fetchColumn();
    }

    private function countPublishedArticles(int $userId, DateTimeImmutable $start, DateTimeImmutable $end): int
    {
        if ($this->tableExists('blog_articles')) {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM blog_articles WHERE website_id = :user_id AND statut = "publié" AND date_publication BETWEEN :start AND :end');
            $stmt->execute([
                ':user_id' => $userId,
                ':start' => $start->format('Y-m-d H:i:s'),
                ':end' => $end->format('Y-m-d H:i:s'),
            ]);

            return (int) $stmt->fetchColumn();
        }

        if ($this->tableExists('articles')) {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM articles WHERE statut = "publie" AND published_at BETWEEN :start AND :end');
            $stmt->execute([
                ':start' => $start->format('Y-m-d H:i:s'),
                ':end' => $end->format('Y-m-d H:i:s'),
            ]);

            return (int) $stmt->fetchColumn();
        }

        return 0;
    }

    private function countPublishedSocialPosts(int $userId, DateTimeImmutable $start, DateTimeImmutable $end): int
    {
        if (!$this->tableExists('social_posts')) {
            return 0;
        }

        try {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM social_posts WHERE user_id = :user_id AND statut = "publie" AND COALESCE(publie_at, updated_at, created_at) BETWEEN :start AND :end');
            $stmt->execute([
                ':user_id' => $userId,
                ':start' => $start->format('Y-m-d H:i:s'),
                ':end' => $end->format('Y-m-d H:i:s'),
            ]);

            return (int) $stmt->fetchColumn();
        } catch (Throwable) {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM social_posts WHERE statut = "publie" AND COALESCE(date_publie, created_at) BETWEEN :start AND :end');
            $stmt->execute([
                ':start' => $start->format('Y-m-d H:i:s'),
                ':end' => $end->format('Y-m-d H:i:s'),
            ]);

            return (int) $stmt->fetchColumn();
        }
    }

    private function renderHtml(array $report): string
    {
        $sourceRows = '';
        foreach ($report['leads_by_source'] as $source => $count) {
            $sourceRows .= sprintf('<tr><td>%s</td><td style="text-align:right;">%d</td></tr>', htmlspecialchars(LeadService::sourceLabel((string) $source), ENT_QUOTES, 'UTF-8'), (int) $count);
        }

        if ($sourceRows === '') {
            $sourceRows = '<tr><td colspan="2">Aucune donnée de source.</td></tr>';
        }

        return sprintf(
            '<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>Rapport %s</title><style>body{font-family:Arial,sans-serif;color:#1f2937;margin:20px}h1{margin:0 0 6px}p{color:#6b7280}table{border-collapse:collapse;width:100%%;margin-top:12px}th,td{border:1px solid #e5e7eb;padding:8px}th{background:#f3f4f6;text-align:left}.kpi{display:inline-block;min-width:200px;margin:8px 12px 8px 0;padding:12px;border:1px solid #e5e7eb;border-radius:8px;background:#fff}.v{font-size:24px;font-weight:700}</style></head><body><h1>📊 Rapport mensuel Optimiser — %s</h1><p>Période : du %s au %s</p><div class="kpi"><div>Leads reçus</div><div class="v">%d</div></div><div class="kpi"><div>Conversions</div><div class="v">%d</div></div><div class="kpi"><div>Taux de conversion</div><div class="v">%s%%</div></div><div class="kpi"><div>Articles publiés</div><div class="v">%d</div></div><div class="kpi"><div>Posts sociaux publiés</div><div class="v">%d</div></div><h2>Sources de leads</h2><table><thead><tr><th>Source</th><th>Nombre</th></tr></thead><tbody>%s</tbody></table></body></html>',
            htmlspecialchars((string) $report['month'], ENT_QUOTES, 'UTF-8'),
            htmlspecialchars((string) $report['month_label'], ENT_QUOTES, 'UTF-8'),
            $report['period_start']->format('d/m/Y'),
            $report['period_end']->format('d/m/Y'),
            (int) $report['leads_total'],
            (int) $report['conversions'],
            number_format((float) $report['conversion_rate'], 1, ',', ' '),
            (int) $report['articles_published'],
            (int) $report['social_posts_published'],
            $sourceRows
        );
    }

    private function renderText(array $report): string
    {
        $lines = [
            'Rapport mensuel Optimiser — ' . $report['month_label'],
            'Période : ' . $report['period_start']->format('d/m/Y') . ' au ' . $report['period_end']->format('d/m/Y'),
            'Leads reçus : ' . $report['leads_total'],
            'Conversions : ' . $report['conversions'],
            'Taux de conversion : ' . number_format((float) $report['conversion_rate'], 1, ',', ' ') . '%',
            'Articles publiés : ' . $report['articles_published'],
            'Posts sociaux publiés : ' . $report['social_posts_published'],
            'Fichier : ' . ($report['pdf_path'] ?? $report['html_path'] ?? 'n/a'),
            '',
            'Sources de leads :',
        ];

        foreach ($report['leads_by_source'] as $source => $count) {
            $lines[] = '- ' . LeadService::sourceLabel((string) $source) . ' : ' . $count;
        }

        return implode("\n", $lines);
    }

    private function saveReportLog(int $userId, string $month, string $format, string $filePath, string $email, bool $sent): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO optimiser_monthly_reports (user_id, report_month, report_format, report_file_path, email_to, sent_at, created_at)
             VALUES (:user_id, :report_month, :report_format, :report_file_path, :email_to, :sent_at, NOW())
             ON DUPLICATE KEY UPDATE
                report_format = VALUES(report_format),
                report_file_path = VALUES(report_file_path),
                email_to = VALUES(email_to),
                sent_at = VALUES(sent_at)'
        );

        $stmt->execute([
            ':user_id' => $userId,
            ':report_month' => $month,
            ':report_format' => $format,
            ':report_file_path' => $filePath,
            ':email_to' => $email,
            ':sent_at' => $sent ? date('Y-m-d H:i:s') : null,
        ]);
    }

    private function isAlreadySent(int $userId, string $month): bool
    {
        $stmt = $this->pdo->prepare('SELECT sent_at FROM optimiser_monthly_reports WHERE user_id = :user_id AND report_month = :report_month LIMIT 1');
        $stmt->execute([
            ':user_id' => $userId,
            ':report_month' => $month,
        ]);

        $sentAt = $stmt->fetchColumn();
        return !empty($sentAt);
    }

    private function ensureReportsTable(): void
    {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS optimiser_monthly_reports (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            report_month CHAR(7) NOT NULL,
            report_format ENUM("html", "pdf") NOT NULL DEFAULT "html",
            report_file_path VARCHAR(500) NOT NULL,
            email_to VARCHAR(255) NOT NULL,
            sent_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            UNIQUE KEY uk_user_month (user_id, report_month),
            INDEX idx_sent_at (sent_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    private function tableExists(string $table): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table_name');
        $stmt->execute([':table_name' => $table]);
        return (int) $stmt->fetchColumn() > 0;
    }
}

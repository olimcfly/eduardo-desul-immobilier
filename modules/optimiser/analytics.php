<?php
$period = isset($_GET['period']) ? (int) $_GET['period'] : 30;
$period = in_array($period, [30, 90], true) ? $period : 30;

/** @return array<string,int> */
function optimiserDateAxis(int $days): array
{
    $axis = [];
    $start = new DateTimeImmutable('-' . ($days - 1) . ' days');
    for ($i = 0; $i < $days; $i++) {
        $date = $start->modify('+' . $i . ' days');
        $axis[$date->format('Y-m-d')] = 0;
    }

    return $axis;
}

/** @return array<string,mixed> */
function optimiserDetectPageViewsSource(PDO $pdo): array
{
    $tables = ['page_views', 'analytics_page_views', 'seo_page_views', 'cms_page_views'];
    $dateColumns = ['viewed_at', 'created_at', 'visited_at', 'date_view'];

    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table");
        $stmt->execute([':table' => $table]);
        if ((int) $stmt->fetchColumn() === 0) {
            continue;
        }

        foreach ($dateColumns as $column) {
            $colStmt = $pdo->prepare(
                "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column"
            );
            $colStmt->execute([':table' => $table, ':column' => $column]);
            if ((int) $colStmt->fetchColumn() > 0) {
                return ['table' => $table, 'date_column' => $column];
            }
        }
    }

    return [];
}

/** @return array<string,mixed> */
function optimiserLoadAnalyticsData(int $days): array
{
    $pdo = db();
    $labels = array_keys(optimiserDateAxis($days));

    $leadSourceSeries = [];
    $leadTotalSeries = optimiserDateAxis($days);
    $estimationSeries = optimiserDateAxis($days);
    $pageViewsSeries = optimiserDateAxis($days);
    $hasPageViews = false;

    $fromDate = (new DateTimeImmutable('-' . ($days - 1) . ' days'))->format('Y-m-d 00:00:00');

    $leadStmt = $pdo->prepare(
        'SELECT DATE(created_at) AS day, source_type, COUNT(*) AS total
         FROM crm_leads
         WHERE created_at >= :from_date
         GROUP BY DATE(created_at), source_type
         ORDER BY day ASC'
    );
    $leadStmt->execute([':from_date' => $fromDate]);

    while ($row = $leadStmt->fetch(PDO::FETCH_ASSOC)) {
        $day = (string) ($row['day'] ?? '');
        $source = (string) ($row['source_type'] ?? 'autre');
        $value = (int) ($row['total'] ?? 0);

        if ($day === '' || !array_key_exists($day, $leadTotalSeries)) {
            continue;
        }

        if (!isset($leadSourceSeries[$source])) {
            $leadSourceSeries[$source] = optimiserDateAxis($days);
        }

        $leadSourceSeries[$source][$day] = $value;
        $leadTotalSeries[$day] += $value;
    }

    $estimationStmt = $pdo->prepare(
        'SELECT DATE(created_at) AS day, COUNT(*) AS total
         FROM estimations
         WHERE created_at >= :from_date
         GROUP BY DATE(created_at)
         ORDER BY day ASC'
    );
    $estimationStmt->execute([':from_date' => $fromDate]);

    while ($row = $estimationStmt->fetch(PDO::FETCH_ASSOC)) {
        $day = (string) ($row['day'] ?? '');
        if ($day !== '' && array_key_exists($day, $estimationSeries)) {
            $estimationSeries[$day] = (int) ($row['total'] ?? 0);
        }
    }

    $pageViewsSource = optimiserDetectPageViewsSource($pdo);
    if ($pageViewsSource !== []) {
        $hasPageViews = true;
        $table = (string) $pageViewsSource['table'];
        $dateColumn = (string) $pageViewsSource['date_column'];

        $sql = sprintf(
            'SELECT DATE(%s) AS day, COUNT(*) AS total FROM %s WHERE %s >= :from_date GROUP BY DATE(%s) ORDER BY day ASC',
            $dateColumn,
            $table,
            $dateColumn,
            $dateColumn
        );
        $viewsStmt = $pdo->prepare($sql);
        $viewsStmt->execute([':from_date' => $fromDate]);

        while ($row = $viewsStmt->fetch(PDO::FETCH_ASSOC)) {
            $day = (string) ($row['day'] ?? '');
            if ($day !== '' && array_key_exists($day, $pageViewsSeries)) {
                $pageViewsSeries[$day] = (int) ($row['total'] ?? 0);
            }
        }
    }

    return [
        'labels' => array_map(
            static fn(string $date): string => (new DateTimeImmutable($date))->format('d/m'),
            $labels
        ),
        'lead_totals' => array_values($leadTotalSeries),
        'lead_sources' => array_map('array_values', $leadSourceSeries),
        'lead_source_labels' => array_keys($leadSourceSeries),
        'estimations' => array_values($estimationSeries),
        'page_views' => array_values($pageViewsSeries),
        'has_page_views' => $hasPageViews,
        'kpi_total_leads' => array_sum($leadTotalSeries),
        'kpi_total_estimations' => array_sum($estimationSeries),
        'kpi_total_views' => array_sum($pageViewsSeries),
        'kpi_avg_leads_per_day' => round(array_sum($leadTotalSeries) / max($days, 1), 1),
    ];
}

try {
    $analytics = optimiserLoadAnalyticsData($period);
} catch (Throwable $e) {
    $analytics = [
        'labels' => [],
        'lead_totals' => [],
        'lead_sources' => [],
        'lead_source_labels' => [],
        'estimations' => [],
        'page_views' => [],
        'has_page_views' => false,
        'kpi_total_leads' => 0,
        'kpi_total_estimations' => 0,
        'kpi_total_views' => 0,
        'kpi_avg_leads_per_day' => 0,
        'error' => $e->getMessage(),
    ];
}
?>

<style>
    .opt-toolbar{display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem}
    .opt-periods{display:flex;gap:.5rem;flex-wrap:wrap}
    .opt-pill{display:inline-flex;align-items:center;gap:.4rem;padding:.55rem .9rem;border-radius:999px;border:1px solid #cbd5e1;text-decoration:none;color:#334155;font-weight:600;background:#fff}
    .opt-pill.active{background:#0f172a;color:#fff;border-color:#0f172a}
    .opt-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:1rem;margin:1rem 0 1.25rem}
    .opt-kpi{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1rem;box-shadow:0 10px 25px rgba(15,23,42,.05)}
    .opt-kpi strong{font-size:1.45rem;display:block}
    .opt-panels{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:1rem}
    .opt-panel{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1rem}
    .opt-panel h3{margin:0 0 .5rem}
    .opt-hint{margin-top:.8rem;color:#64748b;font-size:.88rem}
    .opt-empty{padding:1rem;border-radius:10px;background:#fff7ed;border:1px solid #fed7aa;color:#9a3412}
</style>

<div class="page-header">
    <h1><i class="fas fa-chart-bar page-icon"></i> Tableau de bord <span class="page-title-accent">Analytics</span></h1>
    <p>Agrégation des performances sur <?= (int) $period ?> jours (leads, estimations, trafic pages).</p>
</div>

<div class="opt-toolbar">
    <a class="opt-pill" href="?module=optimiser"><i class="fas fa-arrow-left"></i> Retour au module</a>
    <div class="opt-periods">
        <a class="opt-pill <?= $period === 30 ? 'active' : '' ?>" href="?module=optimiser&view=analytics&period=30">30 jours</a>
        <a class="opt-pill <?= $period === 90 ? 'active' : '' ?>" href="?module=optimiser&view=analytics&period=90">90 jours</a>
    </div>
</div>

<?php if (!empty($analytics['error'])): ?>
    <div class="opt-empty">
        Impossible de charger les données analytics pour le moment. Vérifiez la connexion base de données et le schéma.
    </div>
<?php else: ?>
    <div class="opt-grid">
        <div class="opt-kpi"><strong><?= (int) $analytics['kpi_total_leads'] ?></strong><span>Leads cumulés</span></div>
        <div class="opt-kpi"><strong><?= (int) $analytics['kpi_total_estimations'] ?></strong><span>Estimations reçues</span></div>
        <div class="opt-kpi"><strong><?= (float) $analytics['kpi_avg_leads_per_day'] ?></strong><span>Leads moyens / jour</span></div>
        <div class="opt-kpi"><strong><?= (int) $analytics['kpi_total_views'] ?></strong><span>Vues de pages<?= !empty($analytics['has_page_views']) ? '' : ' (non disponible)' ?></span></div>
    </div>

    <div class="opt-panels">
        <section class="opt-panel">
            <h3>Leads & estimations (<?= (int) $period ?> jours)</h3>
            <canvas id="optPerformanceChart" height="140"></canvas>
            <p class="opt-hint">Comparaison quotidienne des leads capturés (CRM) et des formulaires d'estimation.</p>
        </section>

        <section class="opt-panel">
            <h3>Leads par source</h3>
            <canvas id="optSourcesChart" height="140"></canvas>
            <p class="opt-hint">Répartition par source CRM (contact, estimation, téléchargement, etc.).</p>
        </section>

        <section class="opt-panel" style="grid-column:1/-1;">
            <h3>Vues pages<?= !empty($analytics['has_page_views']) ? '' : ' (source non détectée)' ?></h3>
            <?php if (!empty($analytics['has_page_views'])): ?>
                <canvas id="optViewsChart" height="80"></canvas>
            <?php else: ?>
                <div class="opt-empty">Aucune table de vues de pages trouvée (ex. <code>page_views</code>). Le graphique sera activé automatiquement dès disponibilité.</div>
            <?php endif; ?>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (() => {
            const data = <?= json_encode($analytics, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
            if (!data.labels || !data.labels.length || typeof Chart === 'undefined') {
                return;
            }

            const perfCtx = document.getElementById('optPerformanceChart');
            if (perfCtx) {
                new Chart(perfCtx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [
                            {
                                label: 'Leads',
                                data: data.lead_totals,
                                borderColor: '#2563eb',
                                backgroundColor: 'rgba(37,99,235,.15)',
                                tension: .3,
                                fill: true
                            },
                            {
                                label: 'Estimations',
                                data: data.estimations,
                                borderColor: '#16a34a',
                                backgroundColor: 'rgba(22,163,74,.12)',
                                tension: .3,
                                fill: true
                            }
                        ]
                    },
                    options: {responsive: true, maintainAspectRatio: false}
                });
            }

            const sourceCtx = document.getElementById('optSourcesChart');
            if (sourceCtx && data.lead_source_labels.length) {
                const colors = ['#1d4ed8', '#0ea5e9', '#14b8a6', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6'];
                const datasets = data.lead_source_labels.map((label, idx) => ({
                    label,
                    data: data.lead_sources[idx],
                    backgroundColor: colors[idx % colors.length],
                    borderRadius: 4
                }));

                new Chart(sourceCtx, {
                    type: 'bar',
                    data: {labels: data.labels, datasets},
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {x: {stacked: true}, y: {stacked: true, beginAtZero: true}}
                    }
                });
            }

            const viewsCtx = document.getElementById('optViewsChart');
            if (viewsCtx && data.has_page_views) {
                new Chart(viewsCtx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Vues pages',
                            data: data.page_views,
                            backgroundColor: 'rgba(249,115,22,.65)',
                            borderColor: '#ea580c',
                            borderWidth: 1
                        }]
                    },
                    options: {responsive: true, maintainAspectRatio: false}
                });
            }
        })();
    </script>
<?php endif; ?>

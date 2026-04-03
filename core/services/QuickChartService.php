<?php
// ============================================================
// SERVICE — QuickChartService
// Wrapper pour QuickChart.io (génération de graphiques PNG/SVG).
// Doc : https://quickchart.io/documentation/
// Gratuit jusqu'à 500k requêtes/mois — aucune clé API requise.
// ============================================================

class QuickChartService
{
    private const BASE_URL = 'https://quickchart.io/chart';

    // ── Graphiques NeuroPersona ───────────────────────────────

    /**
     * Génère l'URL d'un graphique radar pour les 4 familles NeuroPersona.
     *
     * @param array $scores  ['Rationnel' => 85, 'Émotionnel' => 60, 'Social' => 75, 'Innovateur' => 90]
     */
    public function neuroPersonaRadar(array $scores, string $title = 'Profil NeuroPersona'): string
    {
        $labels = array_keys($scores);
        $values = array_values($scores);

        $config = [
            'type' => 'radar',
            'data' => [
                'labels'   => $labels,
                'datasets' => [[
                    'label'           => $title,
                    'data'            => $values,
                    'backgroundColor' => 'rgba(52, 152, 219, 0.2)',
                    'borderColor'     => 'rgba(52, 152, 219, 1)',
                    'borderWidth'     => 2,
                    'pointBackgroundColor' => 'rgba(52, 152, 219, 1)',
                ]],
            ],
            'options' => [
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text'    => $title,
                        'font'    => ['size' => 18, 'weight' => 'bold'],
                        'color'   => '#2c3e50',
                    ],
                    'legend' => ['display' => false],
                ],
                'scales' => [
                    'r' => [
                        'min'       => 0,
                        'max'       => 100,
                        'ticks'     => ['stepSize' => 20, 'color' => '#666'],
                        'pointLabels' => ['font' => ['size' => 14], 'color' => '#2c3e50'],
                    ],
                ],
            ],
        ];

        return $this->buildUrl($config, width: 600, height: 500);
    }

    /**
     * Graphique en barres horizontales — répartition des 30 personas NeuroPersona.
     *
     * @param array $families  ['Rationnel' => 8, 'Émotionnel' => 9, 'Social' => 7, 'Innovateur' => 6]
     */
    public function neuroPersonaFamilyBar(array $families): string
    {
        $colors = ['#3498db', '#e74c3c', '#27ae60', '#8e44ad'];

        $config = [
            'type' => 'horizontalBar',
            'data' => [
                'labels'   => array_keys($families),
                'datasets' => [[
                    'label'           => 'Nombre de personas',
                    'data'            => array_values($families),
                    'backgroundColor' => $colors,
                    'borderRadius'    => 6,
                ]],
            ],
            'options' => [
                'indexAxis' => 'y',
                'plugins'   => [
                    'title'  => [
                        'display' => true,
                        'text'    => 'Répartition des 30 NeuroPersonas',
                        'font'    => ['size' => 16, 'weight' => 'bold'],
                        'color'   => '#2c3e50',
                    ],
                    'legend' => ['display' => false],
                ],
                'scales' => [
                    'x' => ['beginAtZero' => true, 'max' => 15, 'grid' => ['color' => '#f0f0f0']],
                    'y' => ['grid' => ['display' => false]],
                ],
            ],
        ];

        return $this->buildUrl($config, width: 700, height: 350);
    }

    // ── Graphiques génériques ─────────────────────────────────

    /**
     * Graphique en ligne — évolution des leads dans le temps.
     *
     * @param array $labels  ['Jan','Fév','Mar',…]
     * @param array $data    [12, 19, 15, …]
     */
    public function leadsEvolutionLine(array $labels, array $data, string $title = 'Évolution des leads'): string
    {
        $config = [
            'type' => 'line',
            'data' => [
                'labels'   => $labels,
                'datasets' => [[
                    'label'           => 'Leads',
                    'data'            => $data,
                    'borderColor'     => '#3498db',
                    'backgroundColor' => 'rgba(52,152,219,0.1)',
                    'fill'            => true,
                    'tension'         => 0.4,
                    'borderWidth'     => 3,
                    'pointRadius'     => 5,
                    'pointBackgroundColor' => '#3498db',
                ]],
            ],
            'options' => [
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text'    => $title,
                        'font'    => ['size' => 16, 'weight' => 'bold'],
                        'color'   => '#2c3e50',
                    ],
                ],
                'scales' => [
                    'y' => ['beginAtZero' => true, 'grid' => ['color' => '#f0f0f0']],
                    'x' => ['grid' => ['display' => false]],
                ],
            ],
        ];

        return $this->buildUrl($config, width: 800, height: 400);
    }

    /**
     * Graphique en secteurs — répartition des sources de leads.
     */
    public function leadSourcePie(array $data): string
    {
        $config = [
            'type' => 'doughnut',
            'data' => [
                'labels'   => array_keys($data),
                'datasets' => [[
                    'data'            => array_values($data),
                    'backgroundColor' => ['#3498db','#e74c3c','#27ae60','#f39c12','#8e44ad','#1abc9c'],
                    'borderWidth'     => 2,
                    'borderColor'     => '#fff',
                ]],
            ],
            'options' => [
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text'    => 'Sources de leads',
                        'font'    => ['size' => 16, 'weight' => 'bold'],
                        'color'   => '#2c3e50',
                    ],
                    'legend' => ['position' => 'bottom'],
                ],
                'cutout' => '60%',
            ],
        ];

        return $this->buildUrl($config, width: 500, height: 450);
    }

    // ── Constructeur d'URL ────────────────────────────────────

    /**
     * Construit l'URL QuickChart à partir d'un objet Chart.js.
     */
    public function buildUrl(array $chartConfig, int $width = 600, int $height = 400, string $format = 'png'): string
    {
        // backgroundColor blanc pour un rendu propre
        $params = [
            'c'               => json_encode($chartConfig, JSON_UNESCAPED_UNICODE),
            'w'               => $width,
            'h'               => $height,
            'f'               => $format,
            'bkg'             => '#ffffff',
            'devicePixelRatio'=> 2,  // Retina
        ];

        return self::BASE_URL . '?' . http_build_query($params);
    }

    /**
     * Télécharge le graphique et retourne les octets PNG.
     * Utile pour le cache local ou l'upload Cloudinary.
     */
    public function downloadChart(string $url): ?string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $data     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($httpCode === 200 && $data) ? $data : null;
    }

    /** QuickChart ne requiert pas de configuration (pas de clé API) */
    public function isConfigured(): bool
    {
        return true;
    }
}

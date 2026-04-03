<?php
// ============================================================
// SERVICE — GoogleMapsService
// Wrapper pour Google Maps Static API.
// Doc : https://developers.google.com/maps/documentation/maps-static
// ============================================================

class GoogleMapsService
{
    private const BASE_URL = 'https://maps.googleapis.com/maps/api/staticmap';

    private string $apiKey;

    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'] ?? '';
    }

    // ── Carte Zone de Prospection ─────────────────────────────

    /**
     * Génère l'URL d'une carte statique pour la Zone de Prospection.
     *
     * @param string $center     Adresse ou lat,lng (ex: 'Nice, France' ou '43.7102,7.2620')
     * @param int    $zoom       Niveau de zoom 1-20
     * @param array  $markers    Marqueurs [['lat' => 43.7, 'lng' => 7.26, 'label' => 'A', 'color' => 'red'], …]
     * @param array  $polygons   Polygones [[['lat','lng'],…],…]  pour délimiter des zones
     * @param string $mapType    roadmap | satellite | terrain | hybrid
     */
    public function buildProspectionMapUrl(
        string $center,
        int    $zoom      = 14,
        array  $markers   = [],
        array  $polygons  = [],
        string $size      = '800x500',
        string $mapType   = 'roadmap'
    ): string {
        $params = [
            'center'  => $center,
            'zoom'    => $zoom,
            'size'    => $size,
            'maptype' => $mapType,
            'scale'   => 2,   // Haute résolution (retina)
            'key'     => $this->apiKey,
            'style'   => 'feature:poi|visibility:simplified',  // allège les POI
        ];

        $query = http_build_query($params);

        // Marqueurs
        foreach ($markers as $m) {
            $color    = urlencode($m['color'] ?? 'red');
            $label    = strtoupper(substr($m['label'] ?? 'A', 0, 1));
            $location = "{$m['lat']},{$m['lng']}";
            $query   .= "&markers=color:{$color}%7Clabel:{$label}%7C{$location}";
        }

        // Polygones (path fermé avec &path=)
        foreach ($polygons as $polygon) {
            $fillColor   = urlencode($polygon['fill']   ?? '0x3498db40');
            $strokeColor = urlencode($polygon['stroke'] ?? '0x3498dbFF');
            $weight      = $polygon['weight'] ?? 2;
            $pathStr     = "fillcolor:{$fillColor}%7Ccolor:{$strokeColor}%7Cweight:{$weight}";
            foreach ($polygon['points'] ?? [] as $pt) {
                $pathStr .= "%7C{$pt['lat']},{$pt['lng']}";
            }
            $query .= "&path={$pathStr}";
        }

        return self::BASE_URL . '?' . $query;
    }

    /**
     * Télécharge la carte et retourne les données binaires (PNG).
     * Utile pour sauvegarder localement ou envoyer à Cloudinary.
     */
    public function downloadMap(string $url): ?string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $data     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$data) {
            error_log("GoogleMapsService: HTTP {$httpCode} for {$url}");
            return null;
        }

        return $data;
    }

    /** Exemple prêt-à-l'emploi : carte Nice centre avec zone colorée */
    public function exampleProspectionNice(): string
    {
        return $this->buildProspectionMapUrl(
            center:   'Nice, Alpes-Maritimes, France',
            zoom:     13,
            markers:  [
                ['lat' => 43.7102, 'lng' => 7.2620, 'label' => 'E', 'color' => 'blue'],
            ],
            polygons: [
                [
                    'fill'   => '0x3498db30',
                    'stroke' => '0x3498dbFF',
                    'weight' => 3,
                    'points' => [
                        ['lat' => 43.720, 'lng' => 7.250],
                        ['lat' => 43.720, 'lng' => 7.280],
                        ['lat' => 43.700, 'lng' => 7.280],
                        ['lat' => 43.700, 'lng' => 7.250],
                        ['lat' => 43.720, 'lng' => 7.250], // ferme le polygone
                    ],
                ],
            ],
            size:    '800x500',
            mapType: 'roadmap'
        );
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }
}

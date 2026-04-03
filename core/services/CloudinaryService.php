<?php
// ============================================================
// SERVICE — CloudinaryService
// Wrapper pour l'API Cloudinary (upload + transformations).
// Doc : https://cloudinary.com/documentation/image_upload_api_reference
// ============================================================

class CloudinaryService
{
    private string $cloudName;
    private string $apiKey;
    private string $apiSecret;
    private string $uploadPreset;

    public function __construct(array $config)
    {
        $this->cloudName    = $config['cloud_name'] ?? '';
        $this->apiKey       = $config['api_key']    ?? '';
        $this->apiSecret    = $config['api_secret'] ?? '';
        $this->uploadPreset = $config['extra']['upload_preset'] ?? 'ml_default';
    }

    // ── Upload ─────────────────────────────────────────────────

    /**
     * Upload une image depuis une URL distante ou un chemin local.
     * Retourne l'URL Cloudinary ou null en cas d'erreur.
     */
    public function uploadFromUrl(string $sourceUrl, string $publicId = '', string $folder = 'dashboard'): ?string
    {
        $params = [
            'file'           => $sourceUrl,
            'upload_preset'  => $this->uploadPreset,
            'folder'         => $folder,
            'resource_type'  => 'image',
        ];
        if ($publicId !== '') {
            $params['public_id'] = $publicId;
        }

        // Paramètres signés (obligatoire si upload_preset non configuré en unsigned)
        $timestamp            = time();
        $params['timestamp']  = $timestamp;
        $params['api_key']    = $this->apiKey;
        $params['signature']  = $this->sign($params);

        $endpoint = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";
        $response = $this->httpPost($endpoint, $params);

        if (!$response || !isset($response['secure_url'])) {
            error_log('Cloudinary upload failed: ' . json_encode($response));
            return null;
        }

        return $response['secure_url'];
    }

    // ── Génération de bannière ANCRE+ ─────────────────────────

    /**
     * Génère une bannière promotionnelle pour la Méthode ANCRE+.
     * Utilise les transformations URL Cloudinary (text overlay, crop, etc.).
     *
     * @param string $baseImagePublicId  Public ID de l'image de fond (ex: 'dashboard/background')
     * @param string $title              Texte principal
     * @param string $subtitle           Sous-titre
     * @param string $color              Couleur de fond de l'overlay (ex: '#e74c3c')
     */
    public function generateAncreBanner(
        string $baseImagePublicId,
        string $title    = 'Méthode ANCRE+',
        string $subtitle = 'Votre stratégie immobilière gagnante',
        string $color    = 'e74c3c'
    ): string {
        $color = ltrim($color, '#');

        // Encodage sécurisé des textes pour l'URL Cloudinary
        $titleEncoded    = urlencode(str_replace(',', '%2C', $title));
        $subtitleEncoded = urlencode(str_replace(',', '%2C', $subtitle));

        // Chaîne de transformations :
        //  1. Redimensionner à 1200×630 (format OpenGraph)
        //  2. Overlay semi-transparent
        //  3. Titre en blanc (bold, 72px)
        //  4. Sous-titre en blanc (normal, 40px)
        $transformations = implode('/', [
            'w_1200,h_630,c_fill,g_auto',
            "l_text:Arial_72_bold:{$titleEncoded},co_white,g_south_west,x_60,y_160",
            "l_text:Arial_40:{$subtitleEncoded},co_white,g_south_west,x_60,y_100",
            "e_colorize,co_rgb:{$color},o_50",
        ]);

        return "https://res.cloudinary.com/{$this->cloudName}/image/upload/{$transformations}/{$baseImagePublicId}";
    }

    // ── URL de transformation générique ──────────────────────

    /**
     * Construit une URL Cloudinary avec des transformations arbitraires.
     * Ex: ['w_800', 'h_600', 'c_fill', 'q_auto', 'f_auto']
     */
    public function buildUrl(string $publicId, array $transformations = []): string
    {
        $t = implode(',', $transformations);
        $t = $t ? $t . '/' : '';
        return "https://res.cloudinary.com/{$this->cloudName}/image/upload/{$t}{$publicId}";
    }

    // ── Utilitaires ───────────────────────────────────────────

    /** Valide que la configuration est complète */
    public function isConfigured(): bool
    {
        return $this->cloudName !== '' && $this->apiKey !== '' && $this->apiSecret !== '';
    }

    /** Signature HMAC-SHA1 pour les requêtes signées */
    private function sign(array $params): string
    {
        // Exclure les paramètres non signés
        $exclude = ['file', 'api_key', 'resource_type'];
        $filtered = array_diff_key($params, array_flip($exclude));
        ksort($filtered);

        $str = implode('&', array_map(
            fn($k, $v) => "{$k}={$v}",
            array_keys($filtered),
            $filtered
        ));

        return hash_hmac('sha1', $str, $this->apiSecret);
    }

    /** POST JSON/form vers l'API Cloudinary */
    private function httpPost(string $url, array $params): ?array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $params,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $body = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($err) {
            error_log("CloudinaryService cURL error: {$err}");
            return null;
        }

        return json_decode($body, true);
    }
}

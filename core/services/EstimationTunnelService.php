<?php
// ============================================================
// ESTIMATION TUNNEL SERVICE
// Orchestre : calcul DVF + biens live + actions CRM + emails
// ============================================================

class EstimationTunnelService
{
    // ── Tables ────────────────────────────────────────────────
    private static bool $tablesReady = false;

    public static function ensureTables(): void
    {
        if (self::$tablesReady) return;

        // Extension de estimation_requests : colonnes tunnel
        $cols = db()->query("SHOW COLUMNS FROM estimation_requests")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('valuation_mode', $cols, true)) {
            db()->exec("ALTER TABLE estimation_requests
                ADD COLUMN valuation_mode VARCHAR(10) NOT NULL DEFAULT 'sold' AFTER surface,
                ADD COLUMN rooms TINYINT UNSIGNED NULL AFTER valuation_mode,
                ADD COLUMN property_condition VARCHAR(40) NULL AFTER rooms,
                ADD COLUMN city VARCHAR(120) NULL AFTER property_condition,
                ADD COLUMN postal_code VARCHAR(10) NULL AFTER city,
                ADD COLUMN converted_at DATETIME NULL AFTER updated_at,
                ADD COLUMN crm_lead_id INT UNSIGNED NULL AFTER converted_at
            ");
        }

        // Table des actions de conversion
        db()->exec('CREATE TABLE IF NOT EXISTS estimation_actions (
            id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            request_id      INT UNSIGNED NOT NULL,
            action_type     VARCHAR(40) NOT NULL DEFAULT "view",
            first_name      VARCHAR(80) NOT NULL DEFAULT "",
            last_name       VARCHAR(80) NOT NULL DEFAULT "",
            email           VARCHAR(190) NOT NULL DEFAULT "",
            phone           VARCHAR(40) NULL,
            message         TEXT NULL,
            crm_lead_id     INT UNSIGNED NULL,
            ip_address      VARCHAR(45) NULL,
            user_agent      VARCHAR(255) NULL,
            created_at      DATETIME NOT NULL,
            INDEX idx_request  (request_id),
            INDEX idx_action   (action_type),
            INDEX idx_created  (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        self::$tablesReady = true;
    }

    // ── Calcul principal ──────────────────────────────────────
    /**
     * Calcule une estimation selon le mode choisi.
     *
     * @param array $input {
     *   property_type, surface, valuation_mode (sold|live|both),
     *   ville, postal_code, lat, lng, rooms
     * }
     */
    public static function calculate(array $input): array
    {
        $type    = strtolower(trim((string)($input['property_type'] ?? '')));
        $surface = (float)($input['surface'] ?? 0);
        $mode    = in_array($input['valuation_mode'] ?? '', ['sold','live','both'], true)
                   ? $input['valuation_mode']
                   : 'sold';
        $ville   = trim((string)($input['ville'] ?? ''));
        $postal  = trim((string)($input['postal_code'] ?? ''));
        $lat     = isset($input['lat']) ? (float)$input['lat'] : 0.0;
        $lng     = isset($input['lng']) ? (float)$input['lng'] : 0.0;
        $rooms   = isset($input['rooms']) ? (int)$input['rooms'] : null;

        if ($type === '' || $surface < 10) {
            return ['ok' => false, 'status' => 'invalid_input',
                    'message' => 'Veuillez renseigner un type de bien et une surface valide.'];
        }

        $dvfResult  = null;
        $liveResult = null;

        // ── DVF (biens vendus) ────────────────────────────────
        if (in_array($mode, ['sold', 'both'], true)) {
            if ($lat !== 0.0 && $lng !== 0.0) {
                $dvfResult = InstantEstimationService::estimate([
                    'lat'           => $lat,
                    'lng'           => $lng,
                    'surface'       => $surface,
                    'property_type' => $type,
                ]);
            } else {
                // Tentative geocoding si GoogleMapsService disponible
                $coords = self::tryGeocode($ville, $postal);
                if ($coords) {
                    $dvfResult = InstantEstimationService::estimate([
                        'lat'           => $coords['lat'],
                        'lng'           => $coords['lng'],
                        'surface'       => $surface,
                        'property_type' => $type,
                    ]);
                }
            }
        }

        // ── Biens en vente (catalogue interne) ────────────────
        if (in_array($mode, ['live', 'both'], true)) {
            $liveResult = self::estimateFromLiveBiens($type, $surface, $ville, $postal, $rooms);
        }

        // ── Fusion selon mode ─────────────────────────────────
        return self::mergeResults($dvfResult, $liveResult, $mode, $surface, $ville, $type);
    }

    /** Recherche dans le catalogue interne des biens disponibles */
    private static function estimateFromLiveBiens(
        string $type, float $surface, string $ville, string $postal, ?int $rooms
    ): array {
        try {
            $where  = ['statut IN ("Disponible", "Sous offre")', 'surface > 0', 'prix > 0'];
            $params = [];

            // Type de bien
            $typeMap = [
                'maison'      => 'Maison',
                'appartement' => 'Appartement',
                'terrain'     => 'Terrain',
            ];
            if (isset($typeMap[$type])) {
                $where[]           = 'type_bien = :type';
                $params[':type']   = $typeMap[$type];
            }

            // Localisation
            if ($postal !== '') {
                $where[]             = 'code_postal = :postal';
                $params[':postal']   = $postal;
            } elseif ($ville !== '') {
                $where[]             = 'ville LIKE :ville';
                $params[':ville']    = '%' . $ville . '%';
            }

            // Surface ±30%
            $where[]              = 'surface BETWEEN :min_s AND :max_s';
            $params[':min_s']     = $surface * 0.70;
            $params[':max_s']     = $surface * 1.30;

            // Pièces ±1 si fourni
            if ($rooms) {
                $where[]          = 'pieces BETWEEN :rmin AND :rmax';
                $params[':rmin']  = max(1, $rooms - 1);
                $params[':rmax']  = $rooms + 1;
            }

            $sql = 'SELECT (prix / surface) AS price_m2 FROM biens WHERE '
                   . implode(' AND ', $where)
                   . ' LIMIT 100';

            $stmt = db()->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) < 3) {
                return ['ok' => false, 'status' => 'insufficient_data',
                        'message' => 'Pas assez de biens en vente comparables dans le secteur.'];
            }

            $prices = array_map(fn($r) => (float)$r['price_m2'], $rows);
            sort($prices);
            $prices = array_values(array_filter($prices, fn($v) => $v > 300 && $v < 30000));

            $n   = count($prices);
            $p25 = self::percentile($prices, 0.25);
            $p50 = self::percentile($prices, 0.50);
            $p75 = self::percentile($prices, 0.75);

            return [
                'ok'                => true,
                'status'            => 'ok',
                'low'               => (int)round($p25 * $surface),
                'median'            => (int)round($p50 * $surface),
                'high'              => (int)round($p75 * $surface),
                'comparables_count' => $n,
                'reliability_score' => min(100, 40 + $n * 3),
                'source'            => 'live',
            ];
        } catch (PDOException $e) {
            error_log('EstimationTunnel live biens error: ' . $e->getMessage());
            return ['ok' => false, 'status' => 'error', 'message' => 'Erreur lors de la recherche de biens.'];
        }
    }

    /** Fusionne les résultats DVF et live selon le mode */
    private static function mergeResults(
        ?array $dvf, ?array $live, string $mode, float $surface, string $ville, string $type
    ): array {
        $base = ['surface' => $surface, 'ville' => $ville, 'type' => $type, 'mode' => $mode];

        if ($mode === 'sold') {
            if ($dvf && $dvf['ok']) return array_merge($base, $dvf, ['source' => 'dvf']);
            return array_merge($base, ['ok' => false, 'status' => 'insufficient_data',
                'message' => 'Données de ventes DVF insuffisantes pour ce secteur. Demandez un avis de valeur personnalisé.']);
        }

        if ($mode === 'live') {
            if ($live && $live['ok']) return array_merge($base, $live, ['source' => 'live']);
            return array_merge($base, ['ok' => false, 'status' => 'insufficient_data',
                'message' => 'Pas assez de biens en vente comparables. Essayez le mode "Biens vendus" ou demandez un avis personnalisé.']);
        }

        // Mode "both" — moyenne des deux si disponibles
        if ($dvf && $dvf['ok'] && $live && $live['ok']) {
            return array_merge($base, [
                'ok'                => true,
                'status'            => 'ok',
                'low'               => (int)(($dvf['low'] + $live['low']) / 2),
                'median'            => (int)(($dvf['median'] + $live['median']) / 2),
                'high'              => (int)(($dvf['high'] + $live['high']) / 2),
                'comparables_count' => ($dvf['comparables_count'] ?? 0) + ($live['comparables_count'] ?? 0),
                'reliability_score' => (int)((($dvf['reliability_score'] ?? 0) + ($live['reliability_score'] ?? 0)) / 2),
                'source'            => 'both',
                'dvf'               => $dvf,
                'live'              => $live,
            ]);
        }

        // Fallback : l'un ou l'autre
        if ($dvf && $dvf['ok'])  return array_merge($base, $dvf,  ['source' => 'dvf']);
        if ($live && $live['ok']) return array_merge($base, $live, ['source' => 'live']);

        return array_merge($base, ['ok' => false, 'status' => 'insufficient_data',
            'message' => 'Données insuffisantes pour ce secteur. Demandez un avis de valeur personnalisé.']);
    }

    // ── Geocoding ─────────────────────────────────────────────
    private static function tryGeocode(string $ville, string $postal): ?array
    {
        if ($ville === '' && $postal === '') return null;

        try {
            if (!class_exists('GoogleMapsService')) return null;
            $address = ($postal !== '' ? $postal . ' ' : '') . $ville . ', France';
            $result  = GoogleMapsService::geocode($address);
            if (isset($result['lat'], $result['lng'])) {
                return ['lat' => (float)$result['lat'], 'lng' => (float)$result['lng']];
            }
        } catch (Throwable $e) {
            error_log('EstimationTunnel geocode error: ' . $e->getMessage());
        }

        return null;
    }

    // ── Sauvegarde ────────────────────────────────────────────
    public static function saveRequest(array $input, array $result): int
    {
        self::ensureTables();

        $stmt = db()->prepare('INSERT INTO estimation_requests
            (address_input, lat, lng, property_type, surface, valuation_mode, rooms, city, postal_code,
             result_low, result_med, result_high, comparables_count, reliability_score,
             status, source, metadata_json, created_at, updated_at)
            VALUES
            (:address_input, :lat, :lng, :property_type, :surface, :valuation_mode, :rooms, :city, :postal_code,
             :result_low, :result_med, :result_high, :comparables_count, :reliability_score,
             :status, :source, :metadata_json, NOW(), NOW())');

        $stmt->execute([
            ':address_input'      => trim((string)($input['ville'] ?? '')) . ' ' . trim((string)($input['postal_code'] ?? '')),
            ':lat'                => isset($input['lat'])  ? (float)$input['lat']  : null,
            ':lng'                => isset($input['lng'])  ? (float)$input['lng']  : null,
            ':property_type'      => trim((string)($input['property_type'] ?? '')),
            ':surface'            => (float)($input['surface'] ?? 0),
            ':valuation_mode'     => $input['valuation_mode'] ?? 'sold',
            ':rooms'              => isset($input['rooms']) ? (int)$input['rooms'] : null,
            ':city'               => trim((string)($input['ville'] ?? '')),
            ':postal_code'        => trim((string)($input['postal_code'] ?? '')),
            ':result_low'         => $result['ok'] ? (int)$result['low']    : null,
            ':result_med'         => $result['ok'] ? (int)$result['median'] : null,
            ':result_high'        => $result['ok'] ? (int)$result['high']   : null,
            ':comparables_count'  => (int)($result['comparables_count'] ?? 0),
            ':reliability_score'  => (int)($result['reliability_score'] ?? 0),
            ':status'             => $result['ok'] ? 'ok' : ($result['status'] ?? 'insufficient_data'),
            ':source'             => 'public',
            ':metadata_json'      => json_encode([
                'valuation_source' => $result['source'] ?? 'none',
                'ip'               => $_SERVER['REMOTE_ADDR'] ?? '',
                'ua'               => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 200),
            ], JSON_UNESCAPED_UNICODE),
        ]);

        return (int)db()->lastInsertId();
    }

    public static function saveAction(array $data): int
    {
        self::ensureTables();

        $stmt = db()->prepare('INSERT INTO estimation_actions
            (request_id, action_type, first_name, last_name, email, phone, message,
             crm_lead_id, ip_address, user_agent, created_at)
            VALUES
            (:request_id, :action_type, :first_name, :last_name, :email, :phone, :message,
             :crm_lead_id, :ip, :ua, NOW())');

        $stmt->execute([
            ':request_id'  => (int)($data['request_id'] ?? 0),
            ':action_type' => trim((string)($data['action_type'] ?? 'view')),
            ':first_name'  => trim((string)($data['first_name'] ?? '')),
            ':last_name'   => trim((string)($data['last_name'] ?? '')),
            ':email'       => strtolower(trim((string)($data['email'] ?? ''))),
            ':phone'       => trim((string)($data['phone'] ?? '')),
            ':message'     => trim((string)($data['message'] ?? '')),
            ':crm_lead_id' => isset($data['crm_lead_id']) ? (int)$data['crm_lead_id'] : null,
            ':ip'          => substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45),
            ':ua'          => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);

        return (int)db()->lastInsertId();
    }

    /** Met à jour le statut d'une estimation_request */
    public static function markConverted(int $requestId, int $leadId, string $status = 'rdv_requested'): void
    {
        self::ensureTables();
        db()->prepare('UPDATE estimation_requests SET status = :s, crm_lead_id = :l, converted_at = NOW(), updated_at = NOW() WHERE id = :id')
             ->execute([':s' => $status, ':l' => $leadId, ':id' => $requestId]);
    }

    /** Charge une request par ID (sécurité : source public seulement) */
    public static function findRequest(int $id): ?array
    {
        self::ensureTables();
        $stmt = db()->prepare('SELECT * FROM estimation_requests WHERE id = :id AND source = "public" LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ── Rate limiting (session-based, simple) ─────────────────
    public static function checkRateLimit(string $key, int $maxPerHour = 8): bool
    {
        $sKey = 'rl_' . $key;
        $now  = time();
        $data = $_SESSION[$sKey] ?? ['count' => 0, 'window' => $now];

        if ($now - $data['window'] > 3600) {
            $data = ['count' => 0, 'window' => $now];
        }

        if ($data['count'] >= $maxPerHour) return false;

        $data['count']++;
        $_SESSION[$sKey] = $data;
        return true;
    }

    // ── Emails ────────────────────────────────────────────────
    public static function sendEmailReport(string $to, string $name, array $req, array $result): void
    {
        $appName   = defined('APP_NAME') ? APP_NAME : 'Pascal Hamm Immobilier';
        $appEmail  = defined('APP_EMAIL') ? APP_EMAIL : '';
        $type      = ucfirst((string)($req['property_type'] ?? ''));
        $surface   = (float)($req['surface'] ?? 0);
        $city      = (string)($req['city'] ?? '');
        $modeLabel = ['sold' => 'biens vendus', 'live' => 'biens en vente', 'both' => 'biens vendus + en vente'][$req['valuation_mode'] ?? 'sold'] ?? '';
        $low       = number_format((int)($req['result_low'] ?? 0),  0, ',', ' ') . ' €';
        $med       = number_format((int)($req['result_med'] ?? 0),  0, ',', ' ') . ' €';
        $high      = number_format((int)($req['result_high'] ?? 0), 0, ',', ' ') . ' €';
        $contact   = defined('APP_URL') ? APP_URL . '/contact' : '/contact';
        $rdv       = defined('APP_URL') ? APP_URL . '/prendre-rendez-vous' : '/prendre-rendez-vous';

        $subject = "Votre rapport d'estimation — {$type} à {$city}";

        $text = "Bonjour {$name},\n\n"
              . "Voici votre rapport d'estimation indicatif.\n\n"
              . "BIEN ANALYSÉ\n"
              . "Type     : {$type}\n"
              . "Surface  : {$surface} m²\n"
              . "Secteur  : {$city}\n"
              . "Source   : {$modeLabel}\n\n"
              . "FOURCHETTE ESTIMATIVE\n"
              . "Bas      : {$low}\n"
              . "Médiane  : {$med} ← valeur centrale\n"
              . "Haut     : {$high}\n\n"
              . "MENTION LÉGALE IMPORTANTE\n"
              . "Cette fourchette est strictement indicative. Elle est calculée à partir de données de marché "
              . "disponibles (transactions enregistrées et/ou biens actuellement en vente) et ne constitue "
              . "en aucun cas une estimation immobilière officielle, un avis de valeur professionnel ni une "
              . "expertise immobilière au sens légal. Seule une visite terrain réalisée par un professionnel "
              . "habilité peut établir une valeur vénale fiable et défendable.\n\n"
              . "Pour aller plus loin :\n"
              . "→ Contact : {$contact}\n"
              . "→ RDV     : {$rdv}\n\n"
              . "Bien cordialement,\n{$appName}";

        $html = self::htmlWrap($subject, "
            <p>Bonjour <strong>" . htmlspecialchars($name) . "</strong>,</p>
            <p>Voici votre rapport d'estimation indicatif pour votre bien à <strong>" . htmlspecialchars($city) . "</strong>.</p>

            <table style='width:100%;border-collapse:collapse;margin:24px 0'>
                <tr style='background:#f8f7f4'>
                    <td style='padding:10px 14px;font-weight:600;color:#1a3c5e'>Type de bien</td>
                    <td style='padding:10px 14px'>" . htmlspecialchars($type) . "</td>
                </tr>
                <tr>
                    <td style='padding:10px 14px;font-weight:600;color:#1a3c5e'>Surface</td>
                    <td style='padding:10px 14px'>{$surface} m²</td>
                </tr>
                <tr style='background:#f8f7f4'>
                    <td style='padding:10px 14px;font-weight:600;color:#1a3c5e'>Secteur</td>
                    <td style='padding:10px 14px'>" . htmlspecialchars($city) . "</td>
                </tr>
                <tr>
                    <td style='padding:10px 14px;font-weight:600;color:#1a3c5e'>Source d'analyse</td>
                    <td style='padding:10px 14px'>" . htmlspecialchars($modeLabel) . "</td>
                </tr>
            </table>

            <h2 style='font-size:18px;color:#1a3c5e;margin:32px 0 16px'>Fourchette estimative</h2>
            <table style='width:100%;border-collapse:collapse;margin-bottom:24px'>
                <tr>
                    <td style='padding:16px;text-align:center;background:#f8f7f4;border-radius:8px;width:33%'>
                        <div style='font-size:12px;color:#6b7280;margin-bottom:6px'>Fourchette basse</div>
                        <div style='font-size:22px;font-weight:600;color:#1a3c5e'>{$low}</div>
                    </td>
                    <td style='padding:8px'></td>
                    <td style='padding:16px;text-align:center;background:#1a3c5e;border-radius:8px;width:33%'>
                        <div style='font-size:12px;color:#c9a84c;margin-bottom:6px'>Valeur estimée ★</div>
                        <div style='font-size:28px;font-weight:700;color:#fff'>{$med}</div>
                    </td>
                    <td style='padding:8px'></td>
                    <td style='padding:16px;text-align:center;background:#f8f7f4;border-radius:8px;width:33%'>
                        <div style='font-size:12px;color:#6b7280;margin-bottom:6px'>Fourchette haute</div>
                        <div style='font-size:22px;font-weight:600;color:#1a3c5e'>{$high}</div>
                    </td>
                </tr>
            </table>

            <div style='background:#fff8e1;border-left:4px solid #c9a84c;padding:16px 20px;border-radius:4px;margin-bottom:24px'>
                <strong style='color:#92680a'>⚠️ Mention légale importante</strong><br>
                <span style='font-size:14px;color:#5c4a1e;line-height:1.6'>
                Cette fourchette est <strong>strictement indicative</strong>. Elle est calculée à partir de données de marché
                disponibles et ne constitue en aucun cas une estimation immobilière officielle, un avis de valeur
                professionnel ni une expertise au sens légal. Seule une visite terrain réalisée par un professionnel
                habilité peut établir une valeur vénale fiable et défendable.
                </span>
            </div>

            <p style='margin-bottom:24px'>Pour obtenir une évaluation précise et personnalisée :</p>
            <div style='text-align:center'>
                <a href='{$rdv}' style='display:inline-block;background:#1a3c5e;color:#fff;padding:14px 28px;border-radius:8px;text-decoration:none;font-weight:600;margin-right:12px'>Prendre rendez-vous</a>
                <a href='{$contact}' style='display:inline-block;border:2px solid #1a3c5e;color:#1a3c5e;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600'>Nous contacter</a>
            </div>
        ");

        MailService::send($to, $subject, $text, $html);
    }

    public static function sendConfirmationProspect(string $to, string $name, string $actionType): void
    {
        $appName = defined('APP_NAME') ? APP_NAME : 'Pascal Hamm Immobilier';
        $rdv     = defined('APP_URL') ? APP_URL . '/prendre-rendez-vous' : '/prendre-rendez-vous';

        $messages = [
            'contact_request' => [
                'subject' => 'Votre demande de contact a bien été reçue',
                'body'    => "Nous avons bien reçu votre demande. Pascal Hamm vous contactera dans les plus brefs délais (généralement sous 24h).",
            ],
            'rdv_request' => [
                'subject' => 'Votre demande de rendez-vous a bien été reçue',
                'body'    => "Nous avons bien reçu votre demande de rendez-vous. Pascal Hamm reviendra vers vous pour confirmer un créneau.",
            ],
        ];

        $msg = $messages[$actionType] ?? [
            'subject' => 'Votre demande a bien été reçue',
            'body'    => "Nous avons bien reçu votre demande et reviendrons vers vous rapidement.",
        ];

        $text = "Bonjour {$name},\n\n{$msg['body']}\n\nBien cordialement,\n{$appName}";

        $html = self::htmlWrap($msg['subject'], "
            <p>Bonjour <strong>" . htmlspecialchars($name) . "</strong>,</p>
            <p>" . htmlspecialchars($msg['body']) . "</p>
            <div style='text-align:center;margin-top:32px'>
                <a href='{$rdv}' style='display:inline-block;background:#1a3c5e;color:#fff;padding:14px 28px;border-radius:8px;text-decoration:none;font-weight:600'>Prendre rendez-vous</a>
            </div>
        ");

        MailService::send($to, $msg['subject'], $text, $html);
    }

    public static function notifyAdvisor(string $actionType, array $data, array $req): void
    {
        $adminEmail = defined('APP_EMAIL') ? APP_EMAIL : '';
        if ($adminEmail === '') return;

        $labels = [
            'email_report'    => '📧 Rapport détaillé demandé',
            'contact_request' => '📞 Demande de contact',
            'rdv_request'     => '📅 Demande de RDV',
        ];

        $label   = $labels[$actionType] ?? $actionType;
        $name    = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        $email   = $data['email'] ?? '';
        $phone   = $data['phone'] ?? 'non fourni';
        $message = $data['message'] ?? '';
        $city    = $req['city'] ?? '';
        $type    = ucfirst((string)($req['property_type'] ?? ''));
        $surface = (float)($req['surface'] ?? 0);
        $low     = number_format((int)($req['result_low'] ?? 0),  0, ',', ' ') . ' €';
        $med     = number_format((int)($req['result_med'] ?? 0),  0, ',', ' ') . ' €';
        $high    = number_format((int)($req['result_high'] ?? 0), 0, ',', ' ') . ' €';

        $subject = "[CRM] {$label} — {$name}";

        $text = "{$label}\n\n"
              . "Nom    : {$name}\n"
              . "Email  : {$email}\n"
              . "Tél    : {$phone}\n"
              . ($message ? "Message: {$message}\n" : '')
              . "\nBien estimé\n"
              . "Type   : {$type}\n"
              . "Surface: {$surface} m²\n"
              . "Ville  : {$city}\n"
              . "Fourchette: {$low} / {$med} / {$high}\n";

        $html = self::htmlWrap($subject, "
            <div style='background:#1a3c5e;color:#fff;padding:12px 20px;border-radius:6px;margin-bottom:20px'>
                <strong style='font-size:18px'>{$label}</strong>
            </div>
            <table style='width:100%;border-collapse:collapse'>
                <tr><td style='padding:8px 0;font-weight:600;color:#1a3c5e;width:140px'>Nom</td><td>" . htmlspecialchars($name) . "</td></tr>
                <tr><td style='padding:8px 0;font-weight:600;color:#1a3c5e'>Email</td><td><a href='mailto:{$email}'>{$email}</a></td></tr>
                <tr><td style='padding:8px 0;font-weight:600;color:#1a3c5e'>Téléphone</td><td>" . htmlspecialchars($phone) . "</td></tr>
                " . ($message ? "<tr><td style='padding:8px 0;font-weight:600;color:#1a3c5e'>Message</td><td>" . nl2br(htmlspecialchars($message)) . "</td></tr>" : '') . "
            </table>
            <hr style='margin:20px 0;border:none;border-top:1px solid #e5e0d8'>
            <h3 style='font-size:15px;color:#1a3c5e;margin-bottom:12px'>Bien estimé</h3>
            <table style='width:100%;border-collapse:collapse'>
                <tr><td style='padding:6px 0;font-weight:600;color:#1a3c5e;width:140px'>Type</td><td>" . htmlspecialchars($type) . "</td></tr>
                <tr><td style='padding:6px 0;font-weight:600;color:#1a3c5e'>Surface</td><td>{$surface} m²</td></tr>
                <tr><td style='padding:6px 0;font-weight:600;color:#1a3c5e'>Ville</td><td>" . htmlspecialchars($city) . "</td></tr>
                <tr><td style='padding:6px 0;font-weight:600;color:#1a3c5e'>Fourchette</td><td>{$low} → <strong>{$med}</strong> → {$high}</td></tr>
            </table>
        ");

        MailService::send($adminEmail, $subject, $text, $html);
    }

    // ── Helpers internes ──────────────────────────────────────
    private static function percentile(array $sorted, float $p): float
    {
        $n = count($sorted);
        if ($n === 0) return 0.0;
        $i = ($n - 1) * $p;
        $f = (int)floor($i);
        $c = (int)ceil($i);
        if ($f === $c) return (float)$sorted[$f];
        return ((1 - ($i - $f)) * (float)$sorted[$f]) + (($i - $f) * (float)$sorted[$c]);
    }

    private static function htmlWrap(string $title, string $body): string
    {
        $appName = defined('APP_NAME') ? APP_NAME : 'Pascal Hamm Immobilier';
        $appUrl  = defined('APP_URL')  ? APP_URL  : '#';
        return "<!DOCTYPE html>
<html lang='fr'>
<head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'><title>" . htmlspecialchars($title) . "</title></head>
<body style='margin:0;padding:0;background:#f8f7f4;font-family:Inter,Arial,sans-serif;color:#1a1a2e'>
<table width='100%' cellpadding='0' cellspacing='0' style='background:#f8f7f4;padding:32px 0'>
<tr><td align='center'>
<table width='600' cellpadding='0' cellspacing='0' style='background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)'>
<tr><td style='background:#1a3c5e;padding:24px 32px'>
    <a href='{$appUrl}' style='color:#c9a84c;font-size:18px;font-weight:700;text-decoration:none'>{$appName}</a>
</td></tr>
<tr><td style='padding:32px'>{$body}</td></tr>
<tr><td style='background:#f8f7f4;padding:20px 32px;font-size:12px;color:#6b7280;text-align:center'>
    © " . date('Y') . " {$appName} · Tous droits réservés
</td></tr>
</table>
</td></tr>
</table>
</body></html>";
    }
}

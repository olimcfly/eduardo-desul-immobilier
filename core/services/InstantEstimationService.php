<?php

class InstantEstimationService
{
    private const MIN_COMPARABLES = 8;

    public static function estimate(array $input): array
    {
        self::ensureTables();

        $lat = isset($input['lat']) ? (float) $input['lat'] : 0.0;
        $lng = isset($input['lng']) ? (float) $input['lng'] : 0.0;
        $surface = isset($input['surface']) ? (float) $input['surface'] : 0.0;
        $propertyType = strtolower(trim((string) ($input['property_type'] ?? '')));

        if ($lat === 0.0 || $lng === 0.0 || $surface <= 9 || $propertyType === '') {
            return [
                'ok' => false,
                'status' => 'invalid_input',
                'message' => 'Paramètres insuffisants pour calculer une estimation.',
            ];
        }

        $expansions = [
            ['radius_km' => 0.7, 'months' => 24, 'surface_ratio' => 0.15],
            ['radius_km' => 1.2, 'months' => 36, 'surface_ratio' => 0.20],
            ['radius_km' => 2.0, 'months' => 48, 'surface_ratio' => 0.25],
        ];

        foreach ($expansions as $stepIndex => $step) {
            $comparables = self::fetchComparables($lat, $lng, $propertyType, $surface, $step);
            $countBefore = count($comparables);
            $comparables = self::removeOutliers($comparables);
            $countAfter = count($comparables);

            if ($countAfter < self::MIN_COMPARABLES) {
                continue;
            }

            $pricesM2 = array_map(static fn(array $row): float => (float) $row['price_m2'], $comparables);
            sort($pricesM2);

            $p25 = self::percentile($pricesM2, 0.25);
            $p50 = self::percentile($pricesM2, 0.50);
            $p75 = self::percentile($pricesM2, 0.75);

            $low = round($p25 * $surface);
            $med = round($p50 * $surface);
            $high = round($p75 * $surface);

            $iqr = max(1.0, $p75 - $p25);
            $dispersion = $iqr / max(1.0, $p50);
            $reliabilityScore = max(0, min(100, (int) round(100 - ($dispersion * 120) + min(18, $countAfter))));

            $status = $reliabilityScore >= 55 ? 'ok' : 'low_reliability';
            if ($status !== 'ok') {
                continue;
            }

            return [
                'ok' => true,
                'status' => 'ok',
                'low' => $low,
                'median' => $med,
                'high' => $high,
                'comparables_count' => $countAfter,
                'comparables_count_raw' => $countBefore,
                'reliability_score' => $reliabilityScore,
                'step' => $stepIndex + 1,
                'step_params' => $step,
                'message' => 'Estimation indicative calculée à partir des transactions DVF comparables.',
            ];
        }

        return [
            'ok' => false,
            'status' => 'insufficient_data',
            'message' => 'Données DVF insuffisantes pour une estimation fiable. Un conseiller doit affiner votre demande.',
        ];
    }

    public static function saveRequest(array $payload): int
    {
        self::ensureTables();

        $stmt = db()->prepare('INSERT INTO estimation_requests
            (first_name, last_name, email, phone, place_id, address_input, address_normalized, lat, lng, property_type, surface,
             result_low, result_med, result_high, comparables_count, reliability_score, status, source, metadata_json, created_at, updated_at)
             VALUES
            (:first_name, :last_name, :email, :phone, :place_id, :address_input, :address_normalized, :lat, :lng, :property_type, :surface,
             :result_low, :result_med, :result_high, :comparables_count, :reliability_score, :status, :source, :metadata_json, NOW(), NOW())');

        $stmt->execute([
            ':first_name' => trim((string) ($payload['first_name'] ?? '')),
            ':last_name' => trim((string) ($payload['last_name'] ?? '')),
            ':email' => trim((string) ($payload['email'] ?? '')),
            ':phone' => trim((string) ($payload['phone'] ?? '')),
            ':place_id' => trim((string) ($payload['place_id'] ?? '')),
            ':address_input' => trim((string) ($payload['address_input'] ?? '')),
            ':address_normalized' => trim((string) ($payload['address_normalized'] ?? '')),
            ':lat' => isset($payload['lat']) ? (float) $payload['lat'] : null,
            ':lng' => isset($payload['lng']) ? (float) $payload['lng'] : null,
            ':property_type' => trim((string) ($payload['property_type'] ?? '')),
            ':surface' => isset($payload['surface']) ? (float) $payload['surface'] : null,
            ':result_low' => isset($payload['result_low']) ? (int) $payload['result_low'] : null,
            ':result_med' => isset($payload['result_med']) ? (int) $payload['result_med'] : null,
            ':result_high' => isset($payload['result_high']) ? (int) $payload['result_high'] : null,
            ':comparables_count' => isset($payload['comparables_count']) ? (int) $payload['comparables_count'] : 0,
            ':reliability_score' => isset($payload['reliability_score']) ? (int) $payload['reliability_score'] : 0,
            ':status' => trim((string) ($payload['status'] ?? 'new')),
            ':source' => trim((string) ($payload['source'] ?? 'public')),
            ':metadata_json' => json_encode($payload['metadata'] ?? [], JSON_UNESCAPED_UNICODE),
        ]);

        return (int) db()->lastInsertId();
    }

    private static function fetchComparables(float $lat, float $lng, string $propertyType, float $surface, array $step): array
    {
        $hasDvf = self::tableExists('dvf_biens');
        if (!$hasDvf) {
            return [];
        }

        $minSurface = $surface * (1 - (float) $step['surface_ratio']);
        $maxSurface = $surface * (1 + (float) $step['surface_ratio']);

        $sql = 'SELECT
                    valeur_fonciere / NULLIF(surface_reelle_bati, 0) AS price_m2,
                    surface_reelle_bati,
                    mutation_date,
                    latitude,
                    longitude
                FROM dvf_biens
                WHERE type_local = :property_type
                  AND surface_reelle_bati BETWEEN :min_surface AND :max_surface
                  AND valeur_fonciere > 10000
                  AND mutation_date >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
                  AND latitude IS NOT NULL
                  AND longitude IS NOT NULL
                  AND (6371 * ACOS(
                        COS(RADIANS(:lat)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(:lng))
                        + SIN(RADIANS(:lat)) * SIN(RADIANS(latitude))
                  )) <= :radius_km';

        $stmt = db()->prepare($sql);
        $stmt->bindValue(':property_type', ucfirst($propertyType));
        $stmt->bindValue(':min_surface', $minSurface);
        $stmt->bindValue(':max_surface', $maxSurface);
        $stmt->bindValue(':months', (int) $step['months'], PDO::PARAM_INT);
        $stmt->bindValue(':lat', $lat);
        $stmt->bindValue(':lng', $lng);
        $stmt->bindValue(':radius_km', (float) $step['radius_km']);
        $stmt->execute();

        $rows = $stmt->fetchAll() ?: [];

        return array_values(array_filter($rows, static function (array $row): bool {
            $v = (float) ($row['price_m2'] ?? 0);
            return $v > 300 && $v < 30000;
        }));
    }

    private static function removeOutliers(array $rows): array
    {
        if (count($rows) < 8) {
            return $rows;
        }

        $values = array_map(static fn(array $r): float => (float) $r['price_m2'], $rows);
        sort($values);

        $q1 = self::percentile($values, 0.25);
        $q3 = self::percentile($values, 0.75);
        $iqr = max(1.0, $q3 - $q1);
        $low = $q1 - (1.5 * $iqr);
        $high = $q3 + (1.5 * $iqr);

        return array_values(array_filter($rows, static function (array $row) use ($low, $high): bool {
            $v = (float) $row['price_m2'];
            return $v >= $low && $v <= $high;
        }));
    }

    private static function percentile(array $sortedValues, float $p): float
    {
        $n = count($sortedValues);
        if ($n === 0) {
            return 0.0;
        }

        $index = ($n - 1) * $p;
        $floor = (int) floor($index);
        $ceil = (int) ceil($index);

        if ($floor === $ceil) {
            return (float) $sortedValues[$floor];
        }

        $weight = $index - $floor;
        return ((1 - $weight) * (float) $sortedValues[$floor]) + ($weight * (float) $sortedValues[$ceil]);
    }

    private static function tableExists(string $table): bool
    {
        $stmt = db()->prepare('SHOW TABLES LIKE :table');
        $stmt->execute([':table' => $table]);
        return (bool) $stmt->fetchColumn();
    }

    private static function ensureTables(): void
    {
        db()->exec('CREATE TABLE IF NOT EXISTS estimation_requests (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(80) NOT NULL DEFAULT "",
            last_name VARCHAR(80) NOT NULL DEFAULT "",
            email VARCHAR(190) NOT NULL DEFAULT "",
            phone VARCHAR(40) NULL,
            place_id VARCHAR(128) NULL,
            address_input VARCHAR(255) NOT NULL,
            address_normalized VARCHAR(255) NULL,
            lat DECIMAL(10,7) NULL,
            lng DECIMAL(10,7) NULL,
            property_type VARCHAR(60) NOT NULL,
            surface DECIMAL(10,2) NULL,
            result_low INT NULL,
            result_med INT NULL,
            result_high INT NULL,
            comparables_count INT NOT NULL DEFAULT 0,
            reliability_score INT NOT NULL DEFAULT 0,
            status VARCHAR(40) NOT NULL DEFAULT "new",
            source VARCHAR(40) NOT NULL DEFAULT "public",
            metadata_json JSON NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX idx_created_at (created_at),
            INDEX idx_status (status),
            INDEX idx_city_type (property_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        db()->exec('CREATE TABLE IF NOT EXISTS dvf_import_jobs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            source_file VARCHAR(255) NOT NULL,
            period_start DATE NULL,
            period_end DATE NULL,
            rows_total INT NOT NULL DEFAULT 0,
            rows_valid INT NOT NULL DEFAULT 0,
            rows_rejected INT NOT NULL DEFAULT 0,
            status VARCHAR(30) NOT NULL DEFAULT "pending",
            error_log TEXT NULL,
            started_at DATETIME NULL,
            finished_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX idx_status (status),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }
}

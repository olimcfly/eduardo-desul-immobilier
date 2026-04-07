<?php

class DvfEstimatorService
{
    private static bool $tableReady = false;

    public static function ensureTables(): void
    {
        if (self::$tableReady) {
            return;
        }

        db()->exec('CREATE TABLE IF NOT EXISTS dvf_transactions (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            mutation_id VARCHAR(64) NOT NULL,
            mutation_date DATE NOT NULL,
            property_type VARCHAR(40) NOT NULL,
            surface DECIMAL(10,2) NOT NULL,
            rooms TINYINT UNSIGNED NULL,
            land_surface DECIMAL(10,2) NULL,
            value_amount DECIMAL(14,2) NOT NULL,
            price_m2 DECIMAL(12,2) NOT NULL,
            city VARCHAR(120) NULL,
            postal_code VARCHAR(12) NULL,
            latitude DECIMAL(10,7) NULL,
            longitude DECIMAL(10,7) NULL,
            address_label VARCHAR(255) NULL,
            source_file VARCHAR(255) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_mutation_line (mutation_id, property_type, surface, value_amount),
            INDEX idx_type_date (property_type, mutation_date),
            INDEX idx_geo (latitude, longitude),
            INDEX idx_city (city),
            INDEX idx_postal_code (postal_code)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        db()->exec('CREATE TABLE IF NOT EXISTS dvf_import_runs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            source_file VARCHAR(255) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT "running",
            rows_read INT UNSIGNED NOT NULL DEFAULT 0,
            rows_inserted INT UNSIGNED NOT NULL DEFAULT 0,
            rows_updated INT UNSIGNED NOT NULL DEFAULT 0,
            rows_rejected INT UNSIGNED NOT NULL DEFAULT 0,
            started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            finished_at DATETIME NULL,
            error_log TEXT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        db()->exec('CREATE TABLE IF NOT EXISTS estimation_requests (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            request_type VARCHAR(20) NOT NULL,
            full_name VARCHAR(160) NOT NULL DEFAULT "",
            email VARCHAR(190) NOT NULL DEFAULT "",
            phone VARCHAR(40) NULL,
            property_type VARCHAR(40) NOT NULL,
            surface DECIMAL(10,2) NOT NULL,
            rooms TINYINT UNSIGNED NULL,
            address_raw VARCHAR(255) NOT NULL,
            address_norm VARCHAR(255) NULL,
            city VARCHAR(120) NULL,
            postal_code VARCHAR(12) NULL,
            latitude DECIMAL(10,7) NULL,
            longitude DECIMAL(10,7) NULL,
            estimated_low DECIMAL(14,2) NULL,
            estimated_median DECIMAL(14,2) NULL,
            estimated_high DECIMAL(14,2) NULL,
            comparables_count INT UNSIGNED NOT NULL DEFAULT 0,
            confidence_score DECIMAL(5,2) NULL,
            confidence_level VARCHAR(20) NULL,
            status VARCHAR(20) NOT NULL DEFAULT "new",
            metadata_json JSON NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_created (created_at),
            INDEX idx_status (status),
            INDEX idx_city_date (city, created_at),
            INDEX idx_property_type (property_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        self::$tableReady = true;
    }

    public static function estimate(array $input): array
    {
        self::ensureTables();

        $type = self::normalizeType((string) ($input['property_type'] ?? ''));
        $surface = (float) ($input['surface'] ?? 0);
        $lat = isset($input['lat']) ? (float) $input['lat'] : null;
        $lng = isset($input['lng']) ? (float) $input['lng'] : null;
        $city = trim((string) ($input['city'] ?? ''));

        if ($type === '' || $surface <= 0) {
            return ['ok' => false, 'reason' => 'invalid_input', 'message' => 'Entrées insuffisantes.'];
        }

        $surfaceBands = [0.15, 0.25, 0.35];
        $dateBandsMonths = [24, 36, 48];
        $radiusKmBands = [1.0, 2.0, 5.0];
        $minComparables = 8;

        $best = [];
        foreach ($surfaceBands as $sBand) {
            foreach ($dateBandsMonths as $months) {
                foreach ($radiusKmBands as $radiusKm) {
                    $rows = self::findComparables($type, $surface, $sBand, $months, $radiusKm, $lat, $lng, $city);
                    if (count($rows) >= $minComparables) {
                        $best = $rows;
                        break 3;
                    }
                    if (count($rows) > count($best)) {
                        $best = $rows;
                    }
                }
            }
        }

        if (count($best) < $minComparables) {
            return [
                'ok' => false,
                'reason' => 'not_enough_comparables',
                'comparables_count' => count($best),
                'message' => 'Pas assez de ventes comparables récentes pour une estimation fiable.',
            ];
        }

        $prices = array_map(static fn($r) => (float) $r['price_m2'], $best);
        sort($prices);
        $q1 = self::percentile($prices, 25);
        $q2 = self::percentile($prices, 50);
        $q3 = self::percentile($prices, 75);
        $iqr = max(0.0, $q3 - $q1);

        $lowFence = max(100.0, $q1 - (1.5 * $iqr));
        $highFence = $q3 + (1.5 * $iqr);
        $clean = array_values(array_filter($prices, static fn($p) => $p >= $lowFence && $p <= $highFence));

        if (count($clean) < $minComparables) {
            return [
                'ok' => false,
                'reason' => 'dispersion_too_high',
                'comparables_count' => count($clean),
                'message' => 'Dispersion trop élevée, estimation instantanée bloquée.',
            ];
        }

        sort($clean);
        $p35 = self::percentile($clean, 35);
        $p50 = self::percentile($clean, 50);
        $p65 = self::percentile($clean, 65);

        $confidenceScore = self::computeConfidenceScore(count($clean), $iqr, $p50);
        $confidenceLevel = $confidenceScore >= 80 ? 'élevée' : ($confidenceScore >= 60 ? 'moyenne' : 'faible');

        return [
            'ok' => true,
            'estimate_low' => round($p35 * $surface),
            'estimate_median' => round($p50 * $surface),
            'estimate_high' => round($p65 * $surface),
            'price_m2_median' => round($p50, 2),
            'comparables_count' => count($clean),
            'confidence_score' => $confidenceScore,
            'confidence_level' => $confidenceLevel,
            'message' => 'Estimation indicative, à confirmer lors d’un rendez-vous conseiller.',
        ];
    }

    public static function saveRequest(array $request, array $estimate): int
    {
        self::ensureTables();

        $stmt = db()->prepare('INSERT INTO estimation_requests
            (request_type, full_name, email, phone, property_type, surface, rooms, address_raw, address_norm, city, postal_code, latitude, longitude,
             estimated_low, estimated_median, estimated_high, comparables_count, confidence_score, confidence_level, status, metadata_json, created_at, updated_at)
            VALUES
            (:request_type, :full_name, :email, :phone, :property_type, :surface, :rooms, :address_raw, :address_norm, :city, :postal_code, :latitude, :longitude,
             :estimated_low, :estimated_median, :estimated_high, :comparables_count, :confidence_score, :confidence_level, :status, :metadata_json, NOW(), NOW())');

        $stmt->execute([
            ':request_type' => (string) ($request['request_type'] ?? 'instant'),
            ':full_name' => trim((string) ($request['full_name'] ?? '')),
            ':email' => trim((string) ($request['email'] ?? '')),
            ':phone' => trim((string) ($request['phone'] ?? '')),
            ':property_type' => self::normalizeType((string) ($request['property_type'] ?? '')),
            ':surface' => (float) ($request['surface'] ?? 0),
            ':rooms' => isset($request['rooms']) && $request['rooms'] !== '' ? (int) $request['rooms'] : null,
            ':address_raw' => trim((string) ($request['address_raw'] ?? '')),
            ':address_norm' => trim((string) ($request['address_norm'] ?? '')),
            ':city' => trim((string) ($request['city'] ?? '')),
            ':postal_code' => trim((string) ($request['postal_code'] ?? '')),
            ':latitude' => isset($request['lat']) ? (float) $request['lat'] : null,
            ':longitude' => isset($request['lng']) ? (float) $request['lng'] : null,
            ':estimated_low' => $estimate['estimate_low'] ?? null,
            ':estimated_median' => $estimate['estimate_median'] ?? null,
            ':estimated_high' => $estimate['estimate_high'] ?? null,
            ':comparables_count' => (int) ($estimate['comparables_count'] ?? 0),
            ':confidence_score' => $estimate['confidence_score'] ?? null,
            ':confidence_level' => $estimate['confidence_level'] ?? null,
            ':status' => 'new',
            ':metadata_json' => json_encode($request['metadata'] ?? [], JSON_UNESCAPED_UNICODE),
        ]);

        return (int) db()->lastInsertId();
    }

    public static function recentRequests(array $filters = []): array
    {
        self::ensureTables();
        $where = [];
        $params = [];

        if (!empty($filters['city'])) {
            $where[] = 'city = :city';
            $params[':city'] = $filters['city'];
        }
        if (!empty($filters['property_type'])) {
            $where[] = 'property_type = :property_type';
            $params[':property_type'] = self::normalizeType((string) $filters['property_type']);
        }
        if (!empty($filters['status'])) {
            $where[] = 'status = :status';
            $params[':status'] = $filters['status'];
        }

        $sql = 'SELECT * FROM estimation_requests';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY created_at DESC LIMIT 500';

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    public static function importStats(): array
    {
        self::ensureTables();
        $runs = db()->query('SELECT * FROM dvf_import_runs ORDER BY started_at DESC LIMIT 20')->fetchAll() ?: [];
        $totalRows = (int) (db()->query('SELECT COUNT(*) FROM dvf_transactions')->fetchColumn() ?: 0);

        return [
            'runs' => $runs,
            'total_rows' => $totalRows,
        ];
    }

    private static function findComparables(string $type, float $surface, float $surfaceBand, int $months, float $radiusKm, ?float $lat, ?float $lng, string $city): array
    {
        $minSurface = max(9.0, $surface * (1 - $surfaceBand));
        $maxSurface = $surface * (1 + $surfaceBand);

        $distanceExpr = '9999';
        if ($lat !== null && $lng !== null) {
            $distanceExpr = '(6371 * ACOS(COS(RADIANS(:lat)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(:lng)) + SIN(RADIANS(:lat)) * SIN(RADIANS(latitude))))';
        }

        $sql = "SELECT price_m2, mutation_date, city, {$distanceExpr} AS distance_km
                FROM dvf_transactions
                WHERE property_type = :type
                  AND surface BETWEEN :min_surface AND :max_surface
                  AND mutation_date >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
                  AND price_m2 > 100";

        if ($lat !== null && $lng !== null) {
            $sql .= ' AND latitude IS NOT NULL AND longitude IS NOT NULL HAVING distance_km <= :radius_km ORDER BY mutation_date DESC LIMIT 250';
        } elseif ($city !== '') {
            $sql .= ' AND city = :city ORDER BY mutation_date DESC LIMIT 250';
        } else {
            $sql .= ' ORDER BY mutation_date DESC LIMIT 250';
        }

        $stmt = db()->prepare($sql);
        $stmt->bindValue(':type', $type);
        $stmt->bindValue(':min_surface', $minSurface);
        $stmt->bindValue(':max_surface', $maxSurface);
        $stmt->bindValue(':months', $months, PDO::PARAM_INT);

        if ($lat !== null && $lng !== null) {
            $stmt->bindValue(':lat', $lat);
            $stmt->bindValue(':lng', $lng);
            $stmt->bindValue(':radius_km', $radiusKm);
        } elseif ($city !== '') {
            $stmt->bindValue(':city', $city);
        }

        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    private static function normalizeType(string $type): string
    {
        $map = [
            'appartement' => 'appartement',
            'maison' => 'maison',
            'local' => 'local',
            'terrain' => 'terrain',
            'immeuble' => 'immeuble',
        ];
        $type = strtolower(trim($type));
        return $map[$type] ?? $type;
    }

    private static function percentile(array $sortedValues, float $percent): float
    {
        $n = count($sortedValues);
        if ($n === 0) {
            return 0.0;
        }
        if ($n === 1) {
            return (float) $sortedValues[0];
        }
        $rank = ($percent / 100) * ($n - 1);
        $low = (int) floor($rank);
        $high = (int) ceil($rank);
        if ($low === $high) {
            return (float) $sortedValues[$low];
        }
        $weight = $rank - $low;
        return ((1 - $weight) * $sortedValues[$low]) + ($weight * $sortedValues[$high]);
    }

    private static function computeConfidenceScore(int $count, float $iqr, float $p50): float
    {
        $countScore = min(100, $count * 6);
        $dispersionRatio = $p50 > 0 ? $iqr / $p50 : 1;
        $dispersionScore = max(0, 100 - ($dispersionRatio * 180));
        return round(($countScore * 0.6) + ($dispersionScore * 0.4), 1);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class EstimatorConfigRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getActiveConfig(?string $citySlug = null): ?array
    {
        try {
            if ($citySlug) {
                $stmt = $this->pdo->prepare("SELECT * FROM estimator_configs WHERE city_slug = :city_slug AND is_active = 1 ORDER BY id DESC LIMIT 1");
                $stmt->execute(['city_slug' => $citySlug]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    return $row;
                }
            }

            $stmt = $this->pdo->query("SELECT * FROM estimator_configs WHERE is_default = 1 OR is_active = 1 ORDER BY is_default DESC, id DESC LIMIT 1");
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            return null;
        }
    }

    public function getZones(int $configId): array
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM estimator_zones WHERE config_id = :config_id AND is_active = 1 ORDER BY sort_order ASC, name ASC');
            $stmt->execute(['config_id' => $configId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function getRules(int $configId, ?string $propertyType = null, ?string $zoneCode = null): array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM estimation_rules
                 WHERE config_id = :config_id
                   AND (:property_type IS NULL OR property_type = :property_type)
                   AND (:zone_code IS NULL OR zone_code = :zone_code)
                   AND is_active = 1
                 ORDER BY priority DESC, id ASC'
            );

            $stmt->execute([
                'config_id' => $configId,
                'property_type' => $propertyType,
                'zone_code' => $zoneCode,
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function getDashboardStats(int $configId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'new') AS new_requests,
                SUM(status = 'qualified') AS qualified,
                SUM(status = 'appointment_booked') AS appointment_booked,
                SUM(status = 'converted') AS converted
             FROM estimation_requests
             WHERE config_id = :config_id"
        );
        $stmt->execute(['config_id' => $configId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}

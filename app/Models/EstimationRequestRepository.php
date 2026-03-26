<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class EstimationRequestRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $payload): int
    {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO estimation_requests (
                    config_id, mode, city_slug, zone_code, property_type, property_address, surface_m2, rooms,
                    estimate_min, estimate_target, estimate_max, currency,
                    bant_budget, bant_authority, bant_need, bant_timeline,
                    contact_first_name, contact_last_name, contact_email, contact_phone,
                    advisor_name, advisor_network, appointment_enabled, appointment_slot,
                    crm_status, status, source_page, notes, history_json, created_at, updated_at
                ) VALUES (
                    :config_id, :mode, :city_slug, :zone_code, :property_type, :property_address, :surface_m2, :rooms,
                    :estimate_min, :estimate_target, :estimate_max, :currency,
                    :bant_budget, :bant_authority, :bant_need, :bant_timeline,
                    :contact_first_name, :contact_last_name, :contact_email, :contact_phone,
                    :advisor_name, :advisor_network, :appointment_enabled, :appointment_slot,
                    :crm_status, :status, :source_page, :notes, :history_json, NOW(), NOW()
                )"
            );
            $stmt->execute($payload);

            return (int) $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            return 0;
        }
    }

    public function listByConfig(int $configId, int $limit = 50): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM estimation_requests WHERE config_id = :config_id ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue(':config_id', $configId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM estimation_requests WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }
}

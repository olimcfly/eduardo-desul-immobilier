<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class RgpdRequest
{
    private const ALLOWED_TYPES = ['access', 'delete', 'update'];
    private const ALLOWED_STATUSES = ['new', 'in_progress', 'done', 'rejected'];

    public function __construct(private PDO $db)
    {
    }

    public function create(array $payload): int
    {
        $type = in_array($payload['request_type'], self::ALLOWED_TYPES, true) ? $payload['request_type'] : 'access';
        $status = in_array($payload['status'], self::ALLOWED_STATUSES, true) ? $payload['status'] : 'new';

        $sql = 'INSERT INTO rgpd_requests (
                    site_id, email, request_type, status, requester_ip,
                    payload_json, created_at, updated_at
                ) VALUES (
                    :site_id, :email, :request_type, :status, :requester_ip,
                    :payload_json, :created_at, :updated_at
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':site_id' => $payload['site_id'],
            ':email' => $payload['email'],
            ':request_type' => $type,
            ':status' => $status,
            ':requester_ip' => $payload['requester_ip'],
            ':payload_json' => $payload['payload_json'],
            ':created_at' => $payload['created_at'],
            ':updated_at' => $payload['updated_at'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findById(int $siteId, int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM rgpd_requests WHERE site_id = :site_id AND id = :id LIMIT 1');
        $stmt->execute([':site_id' => $siteId, ':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function listBySite(int $siteId, ?string $status = null): array
    {
        $params = [':site_id' => $siteId];
        $where = 'site_id = :site_id';

        if ($status !== null && in_array($status, self::ALLOWED_STATUSES, true)) {
            $where .= ' AND status = :status';
            $params[':status'] = $status;
        }

        $stmt = $this->db->prepare("SELECT * FROM rgpd_requests WHERE {$where} ORDER BY created_at DESC");
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $siteId, int $id, string $status): bool
    {
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            return false;
        }

        $stmt = $this->db->prepare('UPDATE rgpd_requests SET status = :status, updated_at = :updated_at WHERE id = :id AND site_id = :site_id');
        return $stmt->execute([
            ':status' => $status,
            ':updated_at' => gmdate('Y-m-d H:i:s'),
            ':id' => $id,
            ':site_id' => $siteId,
        ]);
    }

    public function delete(int $siteId, int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM rgpd_requests WHERE id = :id AND site_id = :site_id');
        return $stmt->execute([
            ':id' => $id,
            ':site_id' => $siteId,
        ]);
    }
}

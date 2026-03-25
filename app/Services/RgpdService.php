<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RgpdRequest;

class RgpdService
{
    private const ALLOWED_TYPES = ['access', 'delete', 'update'];

    public function __construct(private RgpdRequest $requestModel)
    {
    }

    public function createRequest(int $siteId, string $email, string $type, array $payload, string $ip): int
    {
        $cleanEmail = filter_var(trim(mb_strtolower($email)), FILTER_VALIDATE_EMAIL);
        if ($cleanEmail === false) {
            throw new \InvalidArgumentException('Invalid email.');
        }

        $cleanType = strtolower(trim($type));
        if (!in_array($cleanType, self::ALLOWED_TYPES, true)) {
            throw new \InvalidArgumentException('Invalid RGPD request type.');
        }

        return $this->requestModel->create([
            'site_id' => $siteId,
            'email' => $cleanEmail,
            'request_type' => $cleanType,
            'status' => 'new',
            'requester_ip' => filter_var($ip, FILTER_VALIDATE_IP) ?: '0.0.0.0',
            'payload_json' => json_encode($payload, JSON_THROW_ON_ERROR),
            'created_at' => gmdate('Y-m-d H:i:s'),
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    public function listRequests(int $siteId, ?string $status = null): array
    {
        return $this->requestModel->listBySite($siteId, $status);
    }

    public function updateRequestStatus(int $siteId, int $requestId, string $status): bool
    {
        return $this->requestModel->updateStatus($siteId, $requestId, $status);
    }

    public function deleteRequest(int $siteId, int $requestId): bool
    {
        return $this->requestModel->delete($siteId, $requestId);
    }
}

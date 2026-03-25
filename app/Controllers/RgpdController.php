<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ConsentService;
use App\Services\PolicyGeneratorService;
use App\Services\RgpdService;

class RgpdController
{
    public function __construct(
        private ConsentService $consentService,
        private PolicyGeneratorService $policyGeneratorService,
        private RgpdService $rgpdService
    ) {
    }

    public function saveCookieConsent(int $siteId, array $body): array
    {
        $fingerprint = substr(trim((string) ($body['fingerprint'] ?? '')), 0, 120);
        if ($fingerprint === '') {
            return $this->error('Missing fingerprint', 422);
        }

        $consentId = $this->consentService->saveCookieConsent(
            $siteId,
            $fingerprint,
            (array) ($body['categories'] ?? []),
            (string) ($body['ip'] ?? ''),
            (string) ($body['user_agent'] ?? ''),
            (string) ($body['version'] ?? 'v1')
        );

        return $this->ok(['consent_id' => $consentId]);
    }

    public function generatePolicy(int $siteId, array $body): array
    {
        $result = $this->policyGeneratorService->generate(
            $siteId,
            [
                'site_name' => (string) ($body['site_name'] ?? ''),
                'email' => (string) ($body['email'] ?? ''),
                'tools_used' => (array) ($body['tools_used'] ?? []),
            ],
            (string) ($body['version'] ?? 'v1')
        );

        return $this->ok($result);
    }

    public function privacyPolicy(int $siteId): array
    {
        $policy = $this->policyGeneratorService->getLatest($siteId);
        if ($policy === null) {
            return $this->error('No privacy policy found', 404);
        }

        return [
            'status' => 200,
            'headers' => ['Content-Type' => 'text/html; charset=UTF-8'],
            'body' => $policy['html_content'],
        ];
    }

    public function createRequest(int $siteId, array $body): array
    {
        $id = $this->rgpdService->createRequest(
            $siteId,
            (string) ($body['email'] ?? ''),
            (string) ($body['type'] ?? ''),
            (array) ($body['payload'] ?? []),
            (string) ($body['ip'] ?? '')
        );

        return $this->ok(['request_id' => $id], 201);
    }

    public function listRequests(int $siteId, array $query): array
    {
        $status = isset($query['status']) ? (string) $query['status'] : null;
        return $this->ok($this->rgpdService->listRequests($siteId, $status));
    }

    public function updateRequestStatus(int $siteId, int $requestId, array $body): array
    {
        $updated = $this->rgpdService->updateRequestStatus($siteId, $requestId, (string) ($body['status'] ?? ''));
        if (!$updated) {
            return $this->error('Unable to update status', 422);
        }

        return $this->ok(['updated' => true]);
    }

    public function deleteRequest(int $siteId, int $requestId): array
    {
        $deleted = $this->rgpdService->deleteRequest($siteId, $requestId);
        if (!$deleted) {
            return $this->error('Unable to delete request', 404);
        }

        return $this->ok(['deleted' => true]);
    }

    private function ok(array $data, int $status = 200): array
    {
        return [
            'status' => $status,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode(['success' => true, 'data' => $data], JSON_THROW_ON_ERROR),
        ];
    }

    private function error(string $message, int $status): array
    {
        return [
            'status' => $status,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode(['success' => false, 'error' => $message], JSON_THROW_ON_ERROR),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RgpdConsent;

class ConsentService
{
    private const CONSENT_CATEGORIES = ['necessary', 'analytics', 'marketing'];

    public function __construct(private RgpdConsent $consentModel)
    {
    }

    public function saveCookieConsent(int $siteId, string $fingerprint, array $categories, string $ip, string $userAgent, string $version): int
    {
        $sanitizedCategories = $this->sanitizeCategories($categories);

        $payload = [
            'site_id' => $siteId,
            'email' => $fingerprint,
            'consent_type' => 'cookie',
            'categories_json' => json_encode($sanitizedCategories, JSON_THROW_ON_ERROR),
            'consent_version' => $this->sanitizeVersion($version),
            'consented_at' => gmdate('Y-m-d H:i:s'),
            'ip_address' => $this->sanitizeIp($ip),
            'user_agent' => substr(trim($userAgent), 0, 500),
            'proof_hash' => $this->buildProofHash($siteId, $fingerprint, 'cookie', $sanitizedCategories, $version),
        ];

        return $this->consentModel->create($payload);
    }

    public function saveFormConsent(int $siteId, string $email, string $type, array $categories, string $ip, string $userAgent, string $version): int
    {
        $cleanEmail = filter_var(trim(mb_strtolower($email)), FILTER_VALIDATE_EMAIL);
        if ($cleanEmail === false) {
            throw new \InvalidArgumentException('Invalid email provided for consent.');
        }

        $payload = [
            'site_id' => $siteId,
            'email' => $cleanEmail,
            'consent_type' => $this->sanitizeConsentType($type),
            'categories_json' => json_encode($this->sanitizeCategories($categories), JSON_THROW_ON_ERROR),
            'consent_version' => $this->sanitizeVersion($version),
            'consented_at' => gmdate('Y-m-d H:i:s'),
            'ip_address' => $this->sanitizeIp($ip),
            'user_agent' => substr(trim($userAgent), 0, 500),
            'proof_hash' => $this->buildProofHash($siteId, $cleanEmail, $type, $categories, $version),
        ];

        return $this->consentModel->create($payload);
    }

    public function getLatestCookieConsent(int $siteId, string $fingerprint): ?array
    {
        return $this->consentModel->findLatestCookieConsent($siteId, trim($fingerprint));
    }

    private function sanitizeCategories(array $categories): array
    {
        $safe = ['necessary' => true, 'analytics' => false, 'marketing' => false];

        foreach (self::CONSENT_CATEGORIES as $category) {
            if (array_key_exists($category, $categories)) {
                $safe[$category] = (bool) $categories[$category];
            }
        }

        return $safe;
    }

    private function sanitizeConsentType(string $type): string
    {
        $clean = preg_replace('/[^a-z_]/i', '', strtolower(trim($type)));
        return $clean !== '' ? substr($clean, 0, 50) : 'form';
    }

    private function sanitizeVersion(string $version): string
    {
        $clean = preg_replace('/[^a-z0-9._-]/i', '', trim($version));
        return $clean !== '' ? substr($clean, 0, 30) : 'v1';
    }

    private function sanitizeIp(string $ip): string
    {
        $validated = filter_var(trim($ip), FILTER_VALIDATE_IP);
        return $validated !== false ? $validated : '0.0.0.0';
    }

    private function buildProofHash(int $siteId, string $identity, string $type, array $categories, string $version): string
    {
        $proofInput = implode('|', [
            $siteId,
            mb_strtolower(trim($identity)),
            strtolower(trim($type)),
            json_encode($this->sanitizeCategories($categories)),
            $this->sanitizeVersion($version),
        ]);

        return hash('sha256', $proofInput);
    }
}

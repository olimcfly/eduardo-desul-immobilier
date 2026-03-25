<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\EstimatorConfigService;
use App\Services\EstimatorService;

class EstimatorController
{
    public function __construct(
        private EstimatorConfigService $configService,
        private EstimatorService $estimatorService
    ) {
    }

    public function show(string $citySlug = ''): array
    {
        return $this->configService->buildPublicContext($citySlug ?: null);
    }

    public function submit(array $post): array
    {
        $required = ['config_id', 'property_type', 'surface_m2', 'contact_email'];
        foreach ($required as $field) {
            if (empty($post[$field])) {
                return ['success' => false, 'error' => sprintf('Champ requis: %s', $field)];
            }
        }

        $payload = [
            'config_id' => (int) $post['config_id'],
            'mode' => in_array(($post['mode'] ?? 'quick'), ['quick', 'advanced'], true) ? $post['mode'] : 'quick',
            'city_slug' => (string) ($post['city_slug'] ?? ''),
            'zone_code' => (string) ($post['zone_code'] ?? ''),
            'property_type' => (string) $post['property_type'],
            'property_address' => (string) ($post['property_address'] ?? ''),
            'surface_m2' => (float) $post['surface_m2'],
            'rooms' => (int) ($post['rooms'] ?? 0),
            'base_price_m2' => (float) ($post['base_price_m2'] ?? 0),
            'bant_budget' => (string) ($post['bant_budget'] ?? ''),
            'bant_authority' => (string) ($post['bant_authority'] ?? ''),
            'bant_need' => (string) ($post['bant_need'] ?? ''),
            'bant_timeline' => (string) ($post['bant_timeline'] ?? ''),
            'contact_first_name' => (string) ($post['contact_first_name'] ?? ''),
            'contact_last_name' => (string) ($post['contact_last_name'] ?? ''),
            'contact_email' => (string) $post['contact_email'],
            'contact_phone' => (string) ($post['contact_phone'] ?? ''),
            'advisor_name' => (string) ($post['advisor_name'] ?? ''),
            'advisor_network' => (string) ($post['advisor_network'] ?? ''),
            'appointment_slot' => (string) ($post['appointment_slot'] ?? ''),
            'source_page' => (string) ($post['source_page'] ?? '/estimation'),
        ];

        return ['success' => true, 'data' => $this->estimatorService->estimateAndStore($payload)];
    }
}

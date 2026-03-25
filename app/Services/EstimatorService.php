<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EstimationRequestRepository;
use App\Models\EstimatorConfigRepository;

class EstimatorService
{
    public function __construct(
        private EstimatorConfigRepository $configRepository,
        private EstimationRequestRepository $requestRepository,
        private EstimationEngine $engine,
        private LeadQualificationService $qualificationService
    ) {
    }

    public function estimateAndStore(array $payload): array
    {
        $configId = (int) ($payload['config_id'] ?? 0);
        $rules = $this->configRepository->getRules($configId, $payload['property_type'] ?? null, $payload['zone_code'] ?? null);

        $result = $this->engine->compute($payload, $rules);
        $qualification = $this->qualificationService->qualify($payload);

        $history = [
            ['event' => 'created', 'at' => date('c')],
            ['event' => 'estimated', 'at' => date('c'), 'result' => $result],
            ['event' => 'qualified', 'at' => date('c'), 'status' => $qualification['status'], 'score' => $qualification['score']],
        ];

        $requestId = $this->requestRepository->create([
            'config_id' => $configId,
            'mode' => (string) ($payload['mode'] ?? 'quick'),
            'city_slug' => (string) ($payload['city_slug'] ?? ''),
            'zone_code' => (string) ($payload['zone_code'] ?? ''),
            'property_type' => (string) ($payload['property_type'] ?? ''),
            'property_address' => (string) ($payload['property_address'] ?? ''),
            'surface_m2' => (float) ($payload['surface_m2'] ?? 0),
            'rooms' => (int) ($payload['rooms'] ?? 0),
            'estimate_min' => $result['estimate_min'],
            'estimate_target' => $result['estimate_target'],
            'estimate_max' => $result['estimate_max'],
            'currency' => $result['currency'],
            'bant_budget' => (string) ($payload['bant_budget'] ?? ''),
            'bant_authority' => (string) ($payload['bant_authority'] ?? ''),
            'bant_need' => (string) ($payload['bant_need'] ?? ''),
            'bant_timeline' => (string) ($payload['bant_timeline'] ?? ''),
            'contact_first_name' => (string) ($payload['contact_first_name'] ?? ''),
            'contact_last_name' => (string) ($payload['contact_last_name'] ?? ''),
            'contact_email' => (string) ($payload['contact_email'] ?? ''),
            'contact_phone' => (string) ($payload['contact_phone'] ?? ''),
            'advisor_name' => (string) ($payload['advisor_name'] ?? ''),
            'advisor_network' => (string) ($payload['advisor_network'] ?? ''),
            'appointment_enabled' => (int) (!empty($payload['appointment_slot'])),
            'appointment_slot' => (string) ($payload['appointment_slot'] ?? ''),
            'crm_status' => $qualification['crm_status'],
            'status' => $qualification['status'],
            'source_page' => (string) ($payload['source_page'] ?? '/estimation'),
            'notes' => '',
            'history_json' => json_encode($history, JSON_UNESCAPED_UNICODE),
        ]);

        return ['request_id' => $requestId, 'result' => $result, 'qualification' => $qualification];
    }
}

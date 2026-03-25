<?php

declare(strict_types=1);

namespace App\Services;

class LeadQualificationService
{
    public function qualify(array $payload): array
    {
        $score = 0;

        if (!empty($payload['bant_need'])) {
            $score += 30;
        }
        if (!empty($payload['bant_timeline']) && in_array($payload['bant_timeline'], ['0-3m', '3-6m'], true)) {
            $score += 30;
        }
        if (!empty($payload['contact_phone'])) {
            $score += 20;
        }
        if (!empty($payload['appointment_slot'])) {
            $score += 20;
        }

        $status = $score >= 70 ? 'qualified' : 'new';

        return [
            'score' => $score,
            'status' => $status,
            'crm_status' => $score >= 70 ? 'ready_for_crm' : 'to_nurture',
        ];
    }
}

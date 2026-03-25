<?php

declare(strict_types=1);

namespace App\Services;

class EstimationEngine
{
    public function compute(array $input, array $rules): array
    {
        $surface = max(1, (float) ($input['surface_m2'] ?? 0));
        $base = (float) ($input['base_price_m2'] ?? 0);

        if ($base <= 0 && !empty($rules)) {
            $base = (float) ($rules[0]['price_per_m2'] ?? 0);
        }

        $multiplier = 1.0;
        foreach ($rules as $rule) {
            $multiplier += (float) ($rule['adjustment_factor'] ?? 0);
        }

        $target = $surface * max(500, $base) * max(0.7, $multiplier);

        return [
            'estimate_min' => round($target * 0.92),
            'estimate_target' => round($target),
            'estimate_max' => round($target * 1.08),
            'currency' => 'EUR',
            'explain' => 'Estimation basée sur la surface, la zone configurée et les règles actives.',
        ];
    }
}

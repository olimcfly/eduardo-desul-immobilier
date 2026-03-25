<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EstimatorConfigRepository;

class EstimatorConfigService
{
    public function __construct(private EstimatorConfigRepository $repository)
    {
    }

    public function buildPublicContext(?string $citySlug): array
    {
        $config = $this->repository->getActiveConfig($citySlug);
        if (!$config) {
            return ['config' => null, 'zones' => [], 'rules' => []];
        }

        return [
            'config' => $config,
            'zones' => $this->repository->getZones((int) $config['id']),
            'rules' => $this->repository->getRules((int) $config['id']),
        ];
    }
}

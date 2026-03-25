<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\EstimationRequestRepository;
use App\Models\EstimatorConfigRepository;

class EstimatorAdminController
{
    public function __construct(
        private EstimatorConfigRepository $configRepository,
        private EstimationRequestRepository $requestRepository
    ) {
    }

    public function dashboard(string $citySlug = ''): array
    {
        $config = $this->configRepository->getActiveConfig($citySlug ?: null);
        if (!$config) {
            return ['config' => null, 'stats' => []];
        }

        return [
            'config' => $config,
            'stats' => $this->configRepository->getDashboardStats((int) $config['id']),
            'requests' => $this->requestRepository->listByConfig((int) $config['id'], 20),
            'zones' => $this->configRepository->getZones((int) $config['id']),
            'rules' => $this->configRepository->getRules((int) $config['id']),
        ];
    }

    public function requestDetail(int $id): ?array
    {
        return $this->requestRepository->find($id);
    }
}

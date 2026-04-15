<?php

declare(strict_types=1);

require_once __DIR__ . '/../repositories/CampaignRepository.php';
require_once __DIR__ . '/../repositories/ArticleRepository.php';

class CampaignService
{
    private CampaignRepository $campaignRepo;
    private ArticleRepository $articleRepo;

    public const NIVEAUX_CONSCIENCE = [
        1 => 'Inconscient',
        2 => 'Douleur',
        3 => 'Solution',
        4 => 'Produit',
        5 => 'Plus conscient',
    ];

    public function __construct(CampaignRepository $campaignRepo, ArticleRepository $articleRepo)
    {
        $this->campaignRepo = $campaignRepo;
        $this->articleRepo  = $articleRepo;
    }

    public function save(array $data, array $articlesSlots, int $userId, int $websiteId): int
    {
        $data['user_id']    = $userId;
        $data['website_id'] = $websiteId;
        $campaignId = $this->campaignRepo->save($data);
        $this->campaignRepo->saveArticles($campaignId, $articlesSlots);
        return $campaignId;
    }

    public function getWithArticles(int $campaignId): ?array
    {
        $campaign = $this->campaignRepo->findById($campaignId);
        if (!$campaign) {
            return null;
        }
        $campaign['articles'] = $this->campaignRepo->getArticles($campaignId);
        return $campaign;
    }

    public function buildSlots(array $campaign): array
    {
        $existing = array_column($campaign['articles'] ?? [], null, 'role');
        $slots = [];

        // Pillar slot
        $pilier = null;
        foreach ($campaign['articles'] ?? [] as $a) {
            if ($a['role'] === 'pilier') {
                $pilier = $a;
                break;
            }
        }
        $slots[] = ['role' => 'pilier', 'niveau_conscience' => null, 'label' => 'Article Pilier', 'data' => $pilier];

        // Consciousness slots
        foreach (self::NIVEAUX_CONSCIENCE as $niveau => $label) {
            $found = null;
            foreach ($campaign['articles'] ?? [] as $a) {
                if ($a['role'] === 'conscience' && (int)$a['niveau_conscience'] === $niveau) {
                    $found = $a;
                    break;
                }
            }
            $slots[] = ['role' => 'conscience', 'niveau_conscience' => $niveau, 'label' => $label, 'data' => $found];
        }
        return $slots;
    }
}

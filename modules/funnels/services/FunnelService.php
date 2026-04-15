<?php

require_once MODULES_PATH . '/funnels/repositories/FunnelRepository.php';
require_once MODULES_PATH . '/funnels/services/SlugService.php';

class FunnelService
{
    // Templates disponibles par canal
    const TEMPLATES = [
        'google_ads' => [
            'guide_vendeur_v1'  => ['label' => 'Guide Vendeur Local',      'form' => 'guide',      'indexable' => false],
            'estimation_cta_v1' => ['label' => 'Estimation Immédiate',     'form' => 'estimation', 'indexable' => false],
            'rdv_direct_v1'     => ['label' => 'Prise de RDV Direct',      'form' => 'rdv',        'indexable' => false],
        ],
        'facebook_ads' => [
            'guide_vendeur_v1'  => ['label' => 'Guide Vendeur Local',      'form' => 'guide',      'indexable' => false],
            'guide_acheteur_v1' => ['label' => 'Guide Acheteur Local',     'form' => 'guide',      'indexable' => false],
        ],
        'seo' => [
            'guide_local_v1'    => ['label' => 'Guide Local Indexable',    'form' => 'guide',      'indexable' => true],
            'fiche_ville_v1'    => ['label' => 'Fiche Ville',              'form' => 'contact',    'indexable' => true],
        ],
        'estimateur' => [
            'tunnel_estimation_v1' => ['label' => 'Tunnel Estimation',     'form' => 'estimation', 'indexable' => false],
        ],
        'rdv' => [
            'prise_rdv_v1'      => ['label' => 'Page Prise de RDV',        'form' => 'rdv',        'indexable' => false],
        ],
        'social' => [
            'guide_vendeur_v1'  => ['label' => 'Guide Vendeur Local',      'form' => 'guide',      'indexable' => false],
        ],
    ];

    const CANAUX = [
        'google_ads'   => ['label' => 'Google Ads',   'icon' => 'fa-google',    'color' => '#4285F4'],
        'facebook_ads' => ['label' => 'Facebook Ads', 'icon' => 'fa-facebook',  'color' => '#1877F2'],
        'social'       => ['label' => 'Social',       'icon' => 'fa-share-alt', 'color' => '#E91E63'],
        'seo'          => ['label' => 'SEO',           'icon' => 'fa-leaf',      'color' => '#34A853'],
        'rdv'          => ['label' => 'Prise de RDV',  'icon' => 'fa-calendar',  'color' => '#9C27B0'],
        'estimateur'   => ['label' => 'Estimateur',   'icon' => 'fa-calculator','color' => '#FF6D00'],
    ];

    private FunnelRepository $repo;

    public function __construct(PDO $db)
    {
        $this->repo = new FunnelRepository($db);
    }

    public function getAll(array $filters = []): array
    {
        return $this->repo->findAll($filters);
    }

    public function getById(int $id): ?array
    {
        return $this->repo->findById($id);
    }

    public function getBySlug(string $slug): ?array
    {
        return $this->repo->findBySlug($slug);
    }

    public function create(array $input): array
    {
        $errors = $this->validate($input);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $data = $this->prepare($input);

        // Génération slug unique
        $baseSlug = SlugService::generateSlug($data);
        $data['slug'] = SlugService::uniqueSlug($baseSlug, fn($s) => $this->repo->slugExists($s));

        // Auto-génération SEO si vide
        if (empty($data['seo_title'])) {
            $data['seo_title'] = SlugService::generateSeoTitle($data);
        }
        if (empty($data['meta_description'])) {
            $data['meta_description'] = SlugService::generateMetaDescription($data);
        }
        if (empty($data['h1'])) {
            $data['h1'] = SlugService::generateH1($data);
        }

        // Indexable selon template
        $tpl = self::TEMPLATES[$data['canal']][$data['template_id']] ?? [];
        $data['indexable'] = (int) ($tpl['indexable'] ?? 0);
        $data['form_type'] = $tpl['form'] ?? 'guide';

        $id = $this->repo->create($data);
        return ['success' => true, 'id' => $id, 'slug' => $data['slug']];
    }

    public function update(int $id, array $input): array
    {
        $errors = $this->validate($input, $id);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $data = $this->prepare($input);

        // Regénérer SEO si les champs clés ont changé
        if (empty($data['seo_title'])) {
            $data['seo_title'] = SlugService::generateSeoTitle($data);
        }
        if (empty($data['meta_description'])) {
            $data['meta_description'] = SlugService::generateMetaDescription($data);
        }
        if (empty($data['h1'])) {
            $data['h1'] = SlugService::generateH1($data);
        }

        $this->repo->update($id, $data);
        return ['success' => true, 'id' => $id];
    }

    public function publish(int $id): bool
    {
        return $this->repo->update($id, [
            'status'       => 'published',
            'published_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function unpublish(int $id): bool
    {
        return $this->repo->update($id, ['status' => 'draft']);
    }

    public function duplicate(int $id): ?int
    {
        $original = $this->repo->findById($id);
        if (!$original) return null;

        unset($original['id'], $original['created_at'], $original['updated_at'], $original['published_at']);
        $original['status'] = 'draft';
        $original['name']   = $original['name'] . ' (copie)';

        $baseSlug = $original['slug'] . '-copie';
        $original['slug'] = SlugService::uniqueSlug($baseSlug, fn($s) => $this->repo->slugExists($s));

        return $this->repo->create($original);
    }

    public function delete(int $id): bool
    {
        return $this->repo->delete($id);
    }

    public function getStats(int $id): array
    {
        return $this->repo->getStats($id);
    }

    public function getTemplatesForCanal(string $canal): array
    {
        return self::TEMPLATES[$canal] ?? [];
    }

    // ---- Privé ----

    private function validate(array $input, int $excludeId = 0): array
    {
        $errors = [];

        if (empty($input['canal'])) {
            $errors[] = 'Le canal est requis.';
        } elseif (!array_key_exists($input['canal'], self::CANAUX)) {
            $errors[] = 'Canal invalide.';
        }

        if (empty($input['template_id'])) {
            $errors[] = 'Le template est requis.';
        }

        if (empty($input['name'])) {
            $errors[] = 'Le nom interne est requis.';
        }

        if (empty($input['ville'])) {
            $errors[] = 'La ville est requise.';
        }

        // Cohérence Quality Score Google Ads
        if (($input['canal'] ?? '') === 'google_ads' && !empty($input['keyword'])) {
            $h1 = strtolower($input['h1'] ?? '');
            $kw = strtolower($input['keyword']);
            if ($h1 && strpos($h1, $kw) === false) {
                $errors[] = 'Google Ads : le mot-clé doit apparaître dans le H1 (Quality Score).';
            }
        }

        return $errors;
    }

    private function prepare(array $input): array
    {
        return array_filter([
            'canal'           => $input['canal'] ?? null,
            'template_id'     => $input['template_id'] ?? null,
            'name'            => trim($input['name'] ?? ''),
            'ville'           => trim($input['ville'] ?? ''),
            'quartier'        => trim($input['quartier'] ?? ''),
            'keyword'         => trim($input['keyword'] ?? ''),
            'persona'         => $input['persona'] ?? 'vendeur',
            'awareness_level' => $input['awareness_level'] ?? 'problem_aware',
            'campaign_name'   => trim($input['campaign_name'] ?? ''),
            'ad_group'        => trim($input['ad_group'] ?? ''),
            'utm_source'      => trim($input['utm_source'] ?? ''),
            'utm_medium'      => trim($input['utm_medium'] ?? ''),
            'utm_campaign'    => trim($input['utm_campaign'] ?? ''),
            'utm_content'     => trim($input['utm_content'] ?? ''),
            'seo_title'       => trim($input['seo_title'] ?? ''),
            'meta_description'=> trim($input['meta_description'] ?? ''),
            'h1'              => trim($input['h1'] ?? ''),
            'promise'         => trim($input['promise'] ?? ''),
            'cta_label'       => trim($input['cta_label'] ?? ''),
            'ressource_id'    => ($input['ressource_id'] ?? 0) ?: null,
            'sequence_id'     => ($input['sequence_id'] ?? 0) ?: null,
            'thankyou_type'   => $input['thankyou_type'] ?? 'telechargement',
            'thankyou_config' => json_encode([
                'message'   => $input['ty_message'] ?? '',
                'cta_label' => $input['ty_cta_label'] ?? '',
                'cta_url'   => $input['ty_cta_url'] ?? '',
            ]),
        ], fn($v) => $v !== null && $v !== '');
    }
}

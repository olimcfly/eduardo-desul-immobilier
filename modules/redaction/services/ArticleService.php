<?php

declare(strict_types=1);

require_once __DIR__ . '/../repositories/ArticleRepository.php';

class ArticleService
{
    private ArticleRepository $repo;

    public function __construct(ArticleRepository $repo)
    {
        $this->repo = $repo;
    }

    public function generateSlug(string $titre, int $userId, ?int $excludeId = null): string
    {
        $slug = mb_strtolower(trim($titre));
        $slug = str_replace(
            ['à','â','ä','é','è','ê','ë','î','ï','ô','ö','ù','û','ü','ç','œ','æ'],
            ['a','a','a','e','e','e','e','i','i','o','o','u','u','u','c','oe','ae'],
            $slug
        );
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        // Ensure uniqueness
        $base = $slug;
        $i = 1;
        while ($this->slugExists($slug, $userId, $excludeId)) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    private function slugExists(string $slug, int $userId, ?int $excludeId): bool
    {
        // We check via the articles list
        $all = $this->repo->findAll($userId, []);
        foreach ($all as $a) {
            if ($a['slug'] === $slug && ($excludeId === null || (int)$a['id'] !== $excludeId)) {
                return true;
            }
        }
        return false;
    }

    public function calculateSeoScore(array $article, array $keywords = []): int
    {
        $score = 0;
        $checks = [
            // Titre présent et longueur correcte
            !empty($article['titre']) && mb_strlen($article['titre']) >= 30,
            // SEO title optimisé
            !empty($article['seo_title']) && mb_strlen($article['seo_title']) >= 30 && mb_strlen($article['seo_title']) <= 70,
            // Meta description
            !empty($article['meta_desc']) && mb_strlen($article['meta_desc']) >= 100 && mb_strlen($article['meta_desc']) <= 160,
            // H1 défini
            !empty($article['h1']),
            // Mot-clé principal défini
            !empty($article['mot_cle_principal']),
            // Contenu suffisant (>300 mots)
            !empty($article['contenu']) && str_word_count(strip_tags($article['contenu'])) >= 300,
            // Introduction présente
            !empty($article['intro']),
            // Conclusion présente
            !empty($article['conclusion']),
            // Maillage interne présent
            !empty($article['maillage_interne']),
            // Mots-clés LSI
            !empty($article['mots_cles_lsi']),
        ];

        foreach ($checks as $check) {
            if ($check) {
                $score += 10;
            }
        }
        return min(100, $score);
    }

    public function generateSocialPost(array $article, string $reseau): string
    {
        $titre = $article['titre'] ?? '';
        $intro = strip_tags($article['intro'] ?? $article['contenu'] ?? '');
        $intro = mb_substr($intro, 0, 300);
        // Trim to last complete sentence
        if (mb_strlen($intro) === 300) {
            $pos = mb_strrpos($intro, '.');
            if ($pos !== false) {
                $intro = mb_substr($intro, 0, $pos + 1);
            }
        }

        $ville = 'Bordeaux';
        $hashtags = match ($reseau) {
            'gmb'       => '',
            'facebook'  => "\n\n#immobilier #$ville #vendremaison",
            'linkedin'  => "\n\n#immobilier #conseilimmobilier #$ville",
            'instagram' => "\n\n#immobilier #maison #$ville #appartement",
            default     => '',
        };

        return match ($reseau) {
            'gmb' => "📰 $titre\n\n$intro\n\nEn savoir plus sur notre blog →",
            'facebook' => "💡 $titre\n\n$intro\n\nRetrouvez l'article complet sur notre site !$hashtags",
            'linkedin' => "📊 $titre\n\n$intro\n\nLisez l'article complet sur notre blog et partagez votre avis.$hashtags",
            'instagram' => "✨ $titre\n\n$intro$hashtags",
            default     => "$titre\n\n$intro",
        };
    }

    public function save(array $data, int $userId, int $websiteId): int
    {
        $keywords = [];
        if (!empty($data['keywords_raw'])) {
            $keywords = array_filter(array_map('trim', explode(',', $data['keywords_raw'])));
        }
        unset($data['keywords_raw']);

        // Encode JSON fields
        foreach (['maillage_interne', 'maillage_externe', 'hn_structure'] as $f) {
            if (isset($data[$f]) && is_array($data[$f])) {
                $data[$f] = json_encode($data[$f], JSON_UNESCAPED_UNICODE);
            }
        }

        if (empty($data['user_id'])) {
            $data['user_id'] = $userId;
        }
        if (empty($data['website_id'])) {
            $data['website_id'] = $websiteId;
        }

        // Auto-calculate word count
        if (!empty($data['contenu'])) {
            $data['mots'] = str_word_count(strip_tags($data['contenu']));
        }

        // Generate slug if missing
        if (empty($data['slug']) && !empty($data['titre'])) {
            $excludeId = !empty($data['id']) ? (int)$data['id'] : null;
            $data['slug'] = $this->generateSlug($data['titre'], $userId, $excludeId);
        }

        $id = $this->repo->save($data);

        if (!empty($keywords)) {
            $this->repo->saveKeywords($id, $websiteId, $keywords);
        }

        // Update SEO score
        $saved = $this->repo->findById($id);
        if ($saved) {
            $kws = $this->repo->getKeywords($id);
            $score = $this->calculateSeoScore($saved, $kws);
            $this->repo->save(['id' => $id, 'score_seo' => $score]);
        }

        return $id;
    }
}

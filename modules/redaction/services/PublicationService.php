<?php

declare(strict_types=1);

require_once __DIR__ . '/../repositories/PublicationRepository.php';
require_once __DIR__ . '/ArticleService.php';

class PublicationService
{
    private PublicationRepository $repo;
    private ArticleService $articleService;

    public function __construct(PublicationRepository $repo, ArticleService $articleService)
    {
        $this->repo           = $repo;
        $this->articleService = $articleService;
    }

    public function createFromArticle(array $article, array $reseaux, int $userId, ?string $planifieAt = null): array
    {
        $created = [];
        foreach ($reseaux as $reseau) {
            $contenu = $this->articleService->generateSocialPost($article, $reseau);
            $id = $this->repo->save([
                'article_id'  => $article['id'],
                'user_id'     => $userId,
                'reseau'      => $reseau,
                'titre'       => mb_substr($article['titre'] ?? '', 0, 255),
                'contenu'     => $contenu,
                'statut'      => $planifieAt ? 'planifié' : 'draft',
                'planifie_at' => $planifieAt,
            ]);
            $created[] = $id;
        }
        return $created;
    }

    public function findAll(int $userId, array $filters = []): array
    {
        return $this->repo->findAll($userId, $filters);
    }

    public function getJournal(int $userId): array
    {
        return $this->repo->getJournal($userId);
    }

    public function save(array $data): int
    {
        return $this->repo->save($data);
    }

    public function delete(int $id): void
    {
        $this->repo->delete($id);
    }
}

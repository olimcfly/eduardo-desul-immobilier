<?php

declare(strict_types=1);

final class SchedulerService
{
    public function __construct(
        private PostRepository $postRepository,
        private PublishService $publishService
    ) {
    }

    public function run(int $userId): array
    {
        $published = 0;
        foreach ($this->postRepository->findChronological($userId) as $post) {
            if (($post['statut'] ?? '') !== 'planifie') {
                continue;
            }

            $planAt = strtotime((string) ($post['planifie_at'] ?? ''));
            if ($planAt === false || $planAt > time()) {
                continue;
            }

            $this->publishService->publish($post);
            $this->postRepository->update((int) $post['id'], $userId, [
                'sequence_id' => (int) ($post['sequence_id'] ?? 0),
                'titre' => (string) ($post['titre'] ?? ''),
                'contenu' => (string) ($post['contenu'] ?? ''),
                'reseaux' => json_decode((string) ($post['reseaux'] ?? '[]'), true) ?: [],
                'statut' => 'publie',
                'planifie_at' => (string) ($post['planifie_at'] ?? ''),
            ]);
            $published++;
        }

        return ['published' => $published];
    }
}

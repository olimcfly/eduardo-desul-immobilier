<?php

declare(strict_types=1);

final class SocialController
{
    public function __construct(
        private SequenceRepository $sequenceRepository,
        private PostRepository $postRepository,
        private StrategyService $strategyService
    ) {
    }

    public function sequences(): void
    {
        $userId  = socialUserId();
        $filters = [
            'persona' => (string) ($_GET['persona'] ?? 'all'),
            'status'  => (string) ($_GET['status']  ?? 'all'),
        ];

        $sequences      = $this->sequenceRepository->findAllByUser($userId, $filters);
        $postBySequence = $this->postRepository->groupedBySequence($userId);

        include __DIR__ . '/../views/layout/_header.php';
        include __DIR__ . '/../views/sequences/index.php';
    }

    public function journal(): void
    {
        $userId     = socialUserId();
        $weekOffset = (int) ($_GET['week'] ?? 0);

        $posts = $this->postRepository->findChronological($userId);
        $stats = $this->postRepository->getStats($userId);
        $weekData = $this->postRepository->getWeekData($userId, $weekOffset);

        // Grouper les posts par date (décroissant)
        $postsByDate = [];
        foreach ($posts as $post) {
            $ref     = $post['planifie_at'] ?? $post['publie_at'] ?? $post['created_at'] ?? '';
            $dateKey = $ref ? date('Y-m-d', strtotime($ref)) : date('Y-m-d');
            $postsByDate[$dateKey][] = $post;
        }
        krsort($postsByDate);

        include __DIR__ . '/../views/layout/_header.php';
        include __DIR__ . '/../views/journal/index.php';
    }

    public function postDetail(int $postId): void
    {
        $userId   = socialUserId();
        $post     = $this->postRepository->findById($postId, $userId);
        $strategy = $post !== null ? $this->strategyService->buildFromPost($post) : null;

        include __DIR__ . '/../views/layout/_header.php';
        echo '<div>';
        include __DIR__ . '/../views/post/detail.php';
        echo '</div></div>';
    }

    public function postForm(int $postId = 0): void
    {
        $userId    = socialUserId();
        $post      = $postId > 0 ? $this->postRepository->findById($postId, $userId) : null;
        $sequences = $this->sequenceRepository->findAllByUser($userId, ['persona' => 'all', 'status' => 'all']);

        include __DIR__ . '/../views/layout/_header.php';
        echo '<div>';
        include __DIR__ . '/../views/post/form.php';
        echo '</div></div>';
    }
}

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
        $userId = socialUserId();
        $filters = [
            'persona' => (string) ($_GET['persona'] ?? 'all'),
            'status' => (string) ($_GET['status'] ?? 'all'),
        ];

        $sequences = $this->sequenceRepository->findAllByUser($userId, $filters);
        $postBySequence = $this->postRepository->groupedBySequence($userId);

        include __DIR__ . '/../views/layout/_header.php';
        include __DIR__ . '/../views/sequences/index.php';
    }

    public function journal(): void
    {
        $userId = socialUserId();
        $posts = $this->postRepository->findChronological($userId);
        include __DIR__ . '/../views/layout/_header.php';
        include __DIR__ . '/../views/journal/index.php';
    }

    public function postDetail(int $postId): void
    {
        $userId = socialUserId();
        $post = $this->postRepository->findById($postId, $userId);
        $strategy = $post !== null ? $this->strategyService->buildFromPost($post) : null;
        include __DIR__ . '/../views/post/detail.php';
    }

    public function postForm(int $postId = 0): void
    {
        $userId = socialUserId();
        $post = $postId > 0 ? $this->postRepository->findById($postId, $userId) : null;
        $sequences = $this->sequenceRepository->findAllByUser($userId, ['persona' => 'all', 'status' => 'all']);
        include __DIR__ . '/../views/post/form.php';
    }
}

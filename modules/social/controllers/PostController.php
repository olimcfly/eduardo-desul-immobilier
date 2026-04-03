<?php

declare(strict_types=1);

final class PostController
{
    public function __construct(
        private PostRepository $postRepository,
        private StrategyService $strategyService
    ) {
    }

    public function handle(string $action): void
    {
        verifyCsrf();
        $userId = socialUserId();

        if ($action === 'save-post') {
            $id = (int) ($_POST['id'] ?? 0);
            $payload = [
                'sequence_id' => (int) ($_POST['sequence_id'] ?? 0),
                'titre' => trim((string) ($_POST['titre'] ?? 'Publication')),
                'contenu' => trim((string) ($_POST['contenu'] ?? '')),
                'reseaux' => $_POST['reseaux'] ?? ['facebook'],
                'statut' => trim((string) ($_POST['statut'] ?? 'brouillon')),
                'planifie_at' => trim((string) ($_POST['planifie_at'] ?? '')),
            ];

            if ($payload['contenu'] === '') {
                flash('error', 'Le contenu du post est obligatoire.');
                redirect('/admin?module=social&action=post-form' . ($id > 0 ? '&id=' . $id : ''));
            }

            if ($id > 0) {
                $this->postRepository->update($id, $userId, $payload);
            } else {
                $id = $this->postRepository->create($userId, $payload);
            }

            $this->strategyService->storeSnapshot($id, $payload);
            redirect('/admin?module=social&action=post&id=' . $id);
        }

        if ($action === 'delete-post') {
            $id = (int) ($_POST['id'] ?? 0);
            $this->postRepository->delete($id, $userId);
            redirect('/admin?module=social&action=journal');
        }

        redirect('/admin?module=social&action=sequences');
    }
}

<?php

declare(strict_types=1);

final class PostRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function groupedBySequence(int $userId): array
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM social_posts WHERE user_id = :user_id ORDER BY COALESCE(planifie_at, created_at) ASC');
            $stmt->execute([':user_id' => $userId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            return [];
        }

        $grouped = [];
        foreach ($rows as $row) {
            $sequenceId = (int) ($row['sequence_id'] ?? 0);
            $grouped[$sequenceId][] = $row;
        }

        return $grouped;
    }

    public function findChronological(int $userId): array
    {
        try {
            $stmt = $this->pdo->prepare('SELECT p.*, s.nom AS sequence_nom FROM social_posts p LEFT JOIN social_sequences s ON s.id = p.sequence_id WHERE p.user_id = :user_id ORDER BY COALESCE(p.planifie_at, p.created_at) DESC');
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    public function findById(int $id, int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT p.*, s.nom AS sequence_nom FROM social_posts p LEFT JOIN social_sequences s ON s.id = p.sequence_id WHERE p.id = :id AND p.user_id = :user_id LIMIT 1');
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function create(int $userId, array $payload): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO social_posts (user_id, sequence_id, titre, contenu, reseaux, statut, planifie_at, created_at, updated_at) VALUES (:user_id, :sequence_id, :titre, :contenu, :reseaux, :statut, :planifie_at, NOW(), NOW())');
        $stmt->execute([
            ':user_id' => $userId,
            ':sequence_id' => $payload['sequence_id'] ?: null,
            ':titre' => $payload['titre'],
            ':contenu' => $payload['contenu'],
            ':reseaux' => json_encode(array_values((array) $payload['reseaux']), JSON_UNESCAPED_UNICODE),
            ':statut' => $payload['statut'],
            ':planifie_at' => $payload['planifie_at'] !== '' ? $payload['planifie_at'] : null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, int $userId, array $payload): void
    {
        $stmt = $this->pdo->prepare('UPDATE social_posts SET sequence_id = :sequence_id, titre = :titre, contenu = :contenu, reseaux = :reseaux, statut = :statut, planifie_at = :planifie_at, updated_at = NOW() WHERE id = :id AND user_id = :user_id');
        $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId,
            ':sequence_id' => $payload['sequence_id'] ?: null,
            ':titre' => $payload['titre'],
            ':contenu' => $payload['contenu'],
            ':reseaux' => json_encode(array_values((array) $payload['reseaux']), JSON_UNESCAPED_UNICODE),
            ':statut' => $payload['statut'],
            ':planifie_at' => $payload['planifie_at'] !== '' ? $payload['planifie_at'] : null,
        ]);
    }

    public function delete(int $id, int $userId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM social_posts WHERE id = :id AND user_id = :user_id');
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    public function duplicateForSequence(int $oldSequenceId, int $newSequenceId, int $userId): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO social_posts (user_id, sequence_id, titre, contenu, reseaux, statut, planifie_at, created_at, updated_at) SELECT user_id, :new_sequence_id, titre, contenu, reseaux, "brouillon", NULL, NOW(), NOW() FROM social_posts WHERE sequence_id = :old_sequence_id AND user_id = :user_id');
        $stmt->execute([
            ':old_sequence_id' => $oldSequenceId,
            ':new_sequence_id' => $newSequenceId,
            ':user_id' => $userId,
        ]);
    }
}

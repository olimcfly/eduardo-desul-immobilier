<?php

declare(strict_types=1);

final class SequenceRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findAllByUser(int $userId, array $filters): array
    {
        $sql = 'SELECT * FROM social_sequences WHERE user_id = :user_id';
        $params = [':user_id' => $userId];

        if (($filters['persona'] ?? 'all') !== 'all') {
            $sql .= ' AND persona = :persona';
            $params[':persona'] = $filters['persona'];
        }

        if (($filters['status'] ?? 'all') !== 'all') {
            $sql .= ' AND statut = :status';
            $params[':status'] = $filters['status'];
        }

        $sql .= ' ORDER BY updated_at DESC, id DESC';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    public function create(int $userId, array $payload): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO social_sequences (user_id, nom, persona, zone, statut, objectif, created_at, updated_at) VALUES (:user_id, :nom, :persona, :zone, :statut, :objectif, NOW(), NOW())');
        $stmt->execute([
            ':user_id' => $userId,
            ':nom' => $payload['nom'],
            ':persona' => $payload['persona'],
            ':zone' => $payload['zone'],
            ':statut' => $payload['statut'],
            ':objectif' => $payload['objectif'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, int $userId, array $payload): void
    {
        $stmt = $this->pdo->prepare('UPDATE social_sequences SET nom = :nom, persona = :persona, zone = :zone, statut = :statut, objectif = :objectif, updated_at = NOW() WHERE id = :id AND user_id = :user_id');
        $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId,
            ':nom' => $payload['nom'],
            ':persona' => $payload['persona'],
            ':zone' => $payload['zone'],
            ':statut' => $payload['statut'],
            ':objectif' => $payload['objectif'],
        ]);
    }

    public function togglePause(int $id, int $userId): void
    {
        $stmt = $this->pdo->prepare('UPDATE social_sequences SET statut = IF(statut = "pause", "active", "pause"), updated_at = NOW() WHERE id = :id AND user_id = :user_id');
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    public function duplicate(int $id, int $userId): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO social_sequences (user_id, nom, persona, zone, statut, objectif, created_at, updated_at) SELECT user_id, CONCAT(nom, " (copie)"), persona, zone, "brouillon", objectif, NOW(), NOW() FROM social_sequences WHERE id = :id AND user_id = :user_id');
        $stmt->execute([':id' => $id, ':user_id' => $userId]);

        return (int) $this->pdo->lastInsertId();
    }
}

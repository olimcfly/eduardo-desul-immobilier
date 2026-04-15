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
            $stmt = $this->pdo->prepare(
                'SELECT * FROM social_posts WHERE user_id = :user_id
                 ORDER BY COALESCE(ordre_sequence, 0) ASC, COALESCE(planifie_at, created_at) ASC'
            );
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
            $stmt = $this->pdo->prepare(
                'SELECT p.*, s.nom AS sequence_nom
                 FROM social_posts p
                 LEFT JOIN social_sequences s ON s.id = p.sequence_id
                 WHERE p.user_id = :user_id
                 ORDER BY COALESCE(p.planifie_at, p.publie_at, p.created_at) DESC'
            );
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    public function findById(int $id, int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, s.nom AS sequence_nom
             FROM social_posts p
             LEFT JOIN social_sequences s ON s.id = p.sequence_id
             WHERE p.id = :id AND p.user_id = :user_id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function create(int $userId, array $payload): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO social_posts
             (user_id, sequence_id, titre, contenu, reseaux, statut, niveau, ordre_sequence, planifie_at, created_at, updated_at)
             VALUES
             (:user_id, :sequence_id, :titre, :contenu, :reseaux, :statut, :niveau, :ordre, :planifie_at, NOW(), NOW())'
        );
        $stmt->execute([
            ':user_id'     => $userId,
            ':sequence_id' => $payload['sequence_id'] ?: null,
            ':titre'       => $payload['titre'],
            ':contenu'     => $payload['contenu'],
            ':reseaux'     => json_encode(array_values((array) $payload['reseaux']), JSON_UNESCAPED_UNICODE),
            ':statut'      => $payload['statut'],
            ':niveau'      => $payload['niveau'] ?? null,
            ':ordre'       => $payload['ordre_sequence'] ?? null,
            ':planifie_at' => ($payload['planifie_at'] ?? '') !== '' ? $payload['planifie_at'] : null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, int $userId, array $payload): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE social_posts
             SET sequence_id    = :sequence_id,
                 titre          = :titre,
                 contenu        = :contenu,
                 reseaux        = :reseaux,
                 statut         = :statut,
                 niveau         = :niveau,
                 ordre_sequence = :ordre,
                 planifie_at    = :planifie_at,
                 updated_at     = NOW()
             WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([
            ':id'          => $id,
            ':user_id'     => $userId,
            ':sequence_id' => $payload['sequence_id'] ?: null,
            ':titre'       => $payload['titre'],
            ':contenu'     => $payload['contenu'],
            ':reseaux'     => json_encode(array_values((array) $payload['reseaux']), JSON_UNESCAPED_UNICODE),
            ':statut'      => $payload['statut'],
            ':niveau'      => $payload['niveau'] ?? null,
            ':ordre'       => $payload['ordre_sequence'] ?? null,
            ':planifie_at' => ($payload['planifie_at'] ?? '') !== '' ? $payload['planifie_at'] : null,
        ]);
    }

    public function delete(int $id, int $userId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM social_posts WHERE id = :id AND user_id = :user_id');
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    public function duplicateForSequence(int $oldSequenceId, int $newSequenceId, int $userId): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO social_posts
             (user_id, sequence_id, titre, contenu, reseaux, statut, niveau, ordre_sequence, planifie_at, created_at, updated_at)
             SELECT user_id, :new_sequence_id, titre, contenu, reseaux, "brouillon", niveau, ordre_sequence, NULL, NOW(), NOW()
             FROM social_posts
             WHERE sequence_id = :old_sequence_id AND user_id = :user_id'
        );
        $stmt->execute([
            ':old_sequence_id' => $oldSequenceId,
            ':new_sequence_id' => $newSequenceId,
            ':user_id'         => $userId,
        ]);
    }

    /* ─────────── Stats globales par statut ─────────── */
    public function getStats(int $userId): array
    {
        $defaults = ['planifie' => 0, 'publie' => 0, 'brouillon' => 0, 'erreur' => 0];
        try {
            $stmt = $this->pdo->prepare(
                'SELECT statut, COUNT(*) AS cnt FROM social_posts WHERE user_id = :uid GROUP BY statut'
            );
            $stmt->execute([':uid' => $userId]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
                $key = (string) ($row['statut'] ?? '');
                if (array_key_exists($key, $defaults)) {
                    $defaults[$key] = (int) $row['cnt'];
                }
            }
        } catch (Throwable) {
        }

        return $defaults;
    }

    /* ─────────── Données semaine pour le calendrier ─────────── */
    public function getWeekData(int $userId, int $weekOffset = 0): array
    {
        $monday = new DateTimeImmutable('midnight');
        $dow    = (int) $monday->format('N') - 1; // 0=lun … 6=dim
        $monday = $monday->modify("-{$dow} days");

        if ($weekOffset !== 0) {
            $monday = $monday->modify("{$weekOffset} weeks");
        }

        $frDays   = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
        $frMonths = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $d      = $monday->modify("+{$i} days");
            $days[] = [
                'date'    => $d->format('Y-m-d'),
                'day'     => (int) $d->format('j'),
                'dayName' => $frDays[$i],
                'isToday' => $d->format('Y-m-d') === date('Y-m-d'),
                'posts'   => [],
            ];
        }

        $weekStart  = $monday->format('Y-m-d');
        $weekEnd    = $monday->modify('+6 days')->format('Y-m-d');
        $monthLabel = ucfirst($frMonths[(int) $monday->format('n') - 1]) . ' ' . $monday->format('Y');

        try {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM social_posts
                 WHERE user_id = :uid
                   AND DATE(COALESCE(planifie_at, publie_at, created_at)) BETWEEN :start AND :end
                 ORDER BY COALESCE(planifie_at, publie_at, created_at) ASC'
            );
            $stmt->execute([':uid' => $userId, ':start' => $weekStart, ':end' => $weekEnd]);
            $weekPosts = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($weekPosts as $post) {
                $ref     = $post['planifie_at'] ?? $post['publie_at'] ?? $post['created_at'] ?? '';
                $dateKey = $ref ? date('Y-m-d', strtotime($ref)) : '';
                foreach ($days as &$day) {
                    if ($day['date'] === $dateKey) {
                        $day['posts'][] = $post;
                        break;
                    }
                }
                unset($day);
            }
        } catch (Throwable) {
        }

        return [
            'days'       => $days,
            'monthLabel' => $monthLabel,
            'weekOffset' => $weekOffset,
        ];
    }
}

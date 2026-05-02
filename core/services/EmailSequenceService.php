<?php

declare(strict_types=1);

class EmailSequenceService
{
    private const EMAIL_TEMPLATES = [
        1 => [
            'subject_template' => 'Votre opportunité immobilière à {city}',
            'body_template' => 'Bonjour {first_name},\n\nNous avons identifié une opportunité {objective} à {city} qui pourrait vous intéresser.',
        ],
        2 => [
            'subject_template' => 'Comment maximiser votre {objective}',
            'body_template' => 'Cher(e) {first_name},\n\nVous êtes un(e) {persona} cherchant à {objective}. Voici nos conseils pour réussir.',
        ],
        3 => [
            'subject_template' => 'Les 3 erreurs à éviter pour votre {objective}',
            'body_template' => 'Bonjour {first_name},\n\nNous avons analysé 100+ transactions à {city}. Voici ce qui fonctionne et ce à éviter.',
        ],
        4 => [
            'subject_template' => 'Témoignage: Comment {persona} a réussi à {objective}',
            'body_template' => 'Cher(e) {first_name},\n\nDécouvrez comment l\'un de nos clients {persona} a atteint son objectif {objective}.',
        ],
        5 => [
            'subject_template' => 'Votre consultation gratuite à {city}',
            'body_template' => 'Bonjour {first_name},\n\nUn dernier message: reservez votre consultation gratuite pour discuter de votre {objective}.',
        ],
    ];

    public static function createSequence(
        string $name,
        string $objective,
        string $persona,
        string $city,
        string $description = '',
        string $triggerType = 'manual',
        ?string $formTrigger = null,
        ?string $destinationType = null,
        ?string $destinationUrl = null,
        ?string $destinationLabel = null,
        ?string $destinationContactType = null
    ): int {
        try {
            $db = db();

            $stmt = $db->prepare('
                INSERT INTO email_sequences
                (name, objective, persona, city, description, trigger_type, form_trigger, destination_type, destination_url, destination_label, destination_contact_type, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $status = $triggerType === 'automatic' ? 'draft' : 'draft';

            $stmt->execute([
                $name,
                $objective,
                $persona,
                $city,
                $description,
                $triggerType,
                $formTrigger,
                $destinationType,
                $destinationUrl,
                $destinationLabel,
                $destinationContactType,
                $status,
            ]);

            $sequenceId = (int) $db->lastInsertId();

            self::generateSequenceEmails($sequenceId, $objective, $persona, $city);

            return $sequenceId;
        } catch (Throwable $e) {
            error_log('Error creating sequence: ' . $e->getMessage());
            throw $e;
        }
    }

    private static function generateSequenceEmails(
        int $sequenceId,
        string $objective,
        string $persona,
        string $city
    ): void {
        try {
            $db = db();

            foreach (self::EMAIL_TEMPLATES as $emailNumber => $template) {
                $subject = self::replacePlaceholders($template['subject_template'], [
                    '{objective}' => $objective,
                    '{persona}' => $persona,
                    '{city}' => $city,
                ]);

                $body = self::replacePlaceholders($template['body_template'], [
                    '{objective}' => $objective,
                    '{persona}' => $persona,
                    '{city}' => $city,
                    '{first_name}' => '{first_name}',
                ]);

                $delay = ($emailNumber - 1) * 3;

                $stmt = $db->prepare('
                    INSERT INTO email_sequence_emails
                    (sequence_id, email_number, subject, body_html, delay_days)
                    VALUES (?, ?, ?, ?, ?)
                ');

                $stmt->execute([
                    $sequenceId,
                    $emailNumber,
                    $subject,
                    $body,
                    $delay,
                ]);
            }
        } catch (Throwable $e) {
            error_log('Error generating sequence emails: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function getSequence(int $sequenceId): ?array
    {
        try {
            $stmt = db()->prepare('SELECT * FROM email_sequences WHERE id = ?');
            $stmt->execute([$sequenceId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) {
            error_log('Error fetching sequence: ' . $e->getMessage());
            return null;
        }
    }

    public static function getSequenceEmails(int $sequenceId): array
    {
        try {
            $stmt = db()->prepare('
                SELECT * FROM email_sequence_emails
                WHERE sequence_id = ?
                ORDER BY email_number ASC
            ');
            $stmt->execute([$sequenceId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
        } catch (Throwable $e) {
            error_log('Error fetching sequence emails: ' . $e->getMessage());
            return [];
        }
    }

    public static function updateSequenceEmail(
        int $emailId,
        string $subject,
        string $body,
        string $preview = ''
    ): bool {
        try {
            $stmt = db()->prepare('
                UPDATE email_sequence_emails
                SET subject = ?, body_html = ?, preview_text = ?
                WHERE id = ?
            ');

            return $stmt->execute([
                $subject,
                $body,
                $preview,
                $emailId,
            ]);
        } catch (Throwable $e) {
            error_log('Error updating sequence email: ' . $e->getMessage());
            return false;
        }
    }

    public static function activateSequence(int $sequenceId): bool
    {
        try {
            $stmt = db()->prepare('
                UPDATE email_sequences
                SET status = ?
                WHERE id = ?
            ');

            return $stmt->execute(['active', $sequenceId]);
        } catch (Throwable $e) {
            error_log('Error activating sequence: ' . $e->getMessage());
            return false;
        }
    }

    public static function deactivateSequence(int $sequenceId): bool
    {
        try {
            $stmt = db()->prepare('
                UPDATE email_sequences
                SET status = ?
                WHERE id = ?
            ');

            return $stmt->execute(['inactive', $sequenceId]);
        } catch (Throwable $e) {
            error_log('Error deactivating sequence: ' . $e->getMessage());
            return false;
        }
    }

    public static function subscribeToSequence(
        int $sequenceId,
        string $email,
        string $firstName = '',
        string $lastName = ''
    ): int {
        try {
            $db = db();

            $stmt = $db->prepare('
                INSERT INTO email_sequence_subscriptions
                (sequence_id, email, first_name, last_name, status)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE status = VALUES(status)
            ');

            $stmt->execute([
                $sequenceId,
                $email,
                $firstName,
                $lastName,
                'pending',
            ]);

            return (int) $db->lastInsertId();
        } catch (Throwable $e) {
            error_log('Error subscribing to sequence: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function getSequenceStats(int $sequenceId): array
    {
        try {
            $db = db();

            $stats = [
                'total_subscribers' => 0,
                'active_subscribers' => 0,
                'completed_subscribers' => 0,
                'total_sent' => 0,
                'total_opened' => 0,
                'total_clicked' => 0,
            ];

            $stmt = $db->prepare('
                SELECT COUNT(*) as count FROM email_sequence_subscriptions
                WHERE sequence_id = ?
            ');
            $stmt->execute([$sequenceId]);
            $stats['total_subscribers'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            $stmt = $db->prepare('
                SELECT COUNT(*) as count FROM email_sequence_subscriptions
                WHERE sequence_id = ? AND status = ?
            ');
            $stmt->execute([$sequenceId, 'active']);
            $stats['active_subscribers'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            $stmt = $db->prepare('
                SELECT COUNT(*) as count FROM email_sequence_subscriptions
                WHERE sequence_id = ? AND status = ?
            ');
            $stmt->execute([$sequenceId, 'completed']);
            $stats['completed_subscribers'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            $stmt = $db->prepare('
                SELECT COUNT(*) as count FROM email_sequence_sends ess
                JOIN email_sequence_subscriptions esub ON ess.subscription_id = esub.id
                WHERE esub.sequence_id = ?
            ');
            $stmt->execute([$sequenceId]);
            $stats['total_sent'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            $stmt = $db->prepare('
                SELECT COUNT(*) as count FROM email_sequence_sends ess
                JOIN email_sequence_subscriptions esub ON ess.subscription_id = esub.id
                WHERE esub.sequence_id = ? AND ess.opened_at IS NOT NULL
            ');
            $stmt->execute([$sequenceId]);
            $stats['total_opened'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            $stmt = $db->prepare('
                SELECT COUNT(*) as count FROM email_sequence_sends ess
                JOIN email_sequence_subscriptions esub ON ess.subscription_id = esub.id
                WHERE esub.sequence_id = ? AND ess.clicked_at IS NOT NULL
            ');
            $stmt->execute([$sequenceId]);
            $stats['total_clicked'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            return $stats;
        } catch (Throwable $e) {
            error_log('Error getting sequence stats: ' . $e->getMessage());
            return [];
        }
    }

    private static function replacePlaceholders(string $text, array $replacements): string
    {
        foreach ($replacements as $placeholder => $value) {
            $text = str_replace($placeholder, (string) $value, $text);
        }

        return $text;
    }

    public static function deleteSequence(int $sequenceId): bool
    {
        try {
            $stmt = db()->prepare('DELETE FROM email_sequences WHERE id = ?');
            return $stmt->execute([$sequenceId]);
        } catch (Throwable $e) {
            error_log('Error deleting sequence: ' . $e->getMessage());
            return false;
        }
    }

    public static function getSequencesByCity(string $city): array
    {
        try {
            $stmt = db()->prepare('
                SELECT * FROM email_sequences
                WHERE city = ? AND status = ?
                ORDER BY created_at DESC
            ');
            $stmt->execute([$city, 'active']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
        } catch (Throwable $e) {
            error_log('Error fetching sequences by city: ' . $e->getMessage());
            return [];
        }
    }

    public static function triggerSequenceFromForm(string $formName, string $email, string $firstName = ''): bool
    {
        try {
            $stmt = db()->prepare('
                SELECT id FROM email_sequences
                WHERE form_trigger = ? AND trigger_type = ? AND status = ?
            ');
            $stmt->execute([$formName, 'automatic', 'active']);
            $sequences = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($sequences as $sequence) {
                self::subscribeToSequence(
                    (int) $sequence['id'],
                    $email,
                    $firstName
                );
            }

            return true;
        } catch (Throwable $e) {
            error_log('Error triggering sequence from form: ' . $e->getMessage());
            return false;
        }
    }

    public static function updateSequenceDestination(
        int $sequenceId,
        ?string $destinationType = null,
        ?string $destinationUrl = null,
        ?string $destinationLabel = null,
        ?string $destinationContactType = null
    ): bool {
        try {
            $stmt = db()->prepare('
                UPDATE email_sequences
                SET destination_type = ?, destination_url = ?, destination_label = ?, destination_contact_type = ?
                WHERE id = ?
            ');
            return $stmt->execute([
                $destinationType,
                $destinationUrl,
                $destinationLabel,
                $destinationContactType,
                $sequenceId,
            ]);
        } catch (Throwable $e) {
            error_log('Error updating sequence destination: ' . $e->getMessage());
            return false;
        }
    }
}

<?php

class SequenceCrmService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Inscrit un lead dans une séquence.
     * Déclenche immédiatement l'email J0 (step 1, delay_days = 0).
     */
    public function enroll(int $leadId, int $sequenceId, array $vars = []): bool
    {
        // Vérifier que la séquence existe et est active
        $seq = $this->getSequence($sequenceId);
        if (!$seq || $seq['status'] !== 'active') return false;

        // Ne pas ré-inscrire un lead déjà actif dans cette séquence
        $existing = $this->getEnrollment($leadId, $sequenceId);
        if ($existing && $existing['status'] === 'active') return false;

        // Créer l'enrollment
        $stmt = $this->db->prepare('
            INSERT INTO crm_sequence_enrollments
                (sequence_id, lead_id, current_step, status, next_send_at)
            VALUES (:seq_id, :lead_id, 0, "active", NOW())
            ON DUPLICATE KEY UPDATE status = "active", current_step = 0, next_send_at = NOW()
        ');
        $stmt->execute([':seq_id' => $sequenceId, ':lead_id' => $leadId]);

        // Envoyer l'email J0 immédiatement
        $this->processNextStep($leadId, $sequenceId, $vars);

        return true;
    }

    /**
     * Inscrit un lead sans envoyer le 1er mail tout de suite (ex. accusé manuel + IA le jour J).
     * Le 1er step part quand next_send_at est atteint (cron processDue).
     */
    public function enrollDeferred(int $leadId, int $sequenceId, int $firstEmailInDays, array $vars = []): bool
    {
        $seq = $this->getSequence($sequenceId);
        if (!$seq || $seq['status'] !== 'active') {
            return false;
        }

        $existing = $this->getEnrollment($leadId, $sequenceId);
        if ($existing && $existing['status'] === 'active') {
            return false;
        }

        $firstInDays = max(0, $firstEmailInDays);
        $nextAt = (new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')))
            ->modify('+' . $firstInDays . ' days')
            ->format('Y-m-d H:i:s');

        $stmt = $this->db->prepare('
            INSERT INTO crm_sequence_enrollments
                (sequence_id, lead_id, current_step, status, next_send_at)
            VALUES (:seq_id, :lead_id, 0, "active", :next_at)
        ');
        $stmt->execute([':seq_id' => $sequenceId, ':lead_id' => $leadId, ':next_at' => $nextAt]);

        return true;
    }

    /**
     * Traite le prochain step dû pour un enrollment.
     * À appeler depuis le cron.
     */
    public function processNextStep(int $leadId, int $sequenceId, array $vars = []): bool
    {
        $enrollment = $this->getEnrollment($leadId, $sequenceId);
        if (!$enrollment || $enrollment['status'] !== 'active') return false;

        $nextStepOrder = $enrollment['current_step'] + 1;

        $step = $this->getStep($sequenceId, $nextStepOrder);
        if (!$step) {
            // Plus d'étapes — séquence terminée
            $this->complete($leadId, $sequenceId);
            return false;
        }

        $lead = $this->getLead($leadId);
        if (!$lead || empty($lead['email'])) return false;

        // Fusionner les variables dans le template
        $subject = $this->interpolate($step['email_subject'], $lead, $vars);
        $body    = $this->interpolate($step['email_body_html'], $lead, $vars);

        // Envoyer l'email
        $sent = $this->sendEmail($lead['email'], $subject, $body);
        if (!$sent) return false;

        // Calculer la prochaine échéance
        $nextStep = $this->getStep($sequenceId, $nextStepOrder + 1);
        $nextSendAt = null;

        if ($nextStep) {
            $nextSendAt = date('Y-m-d H:i:s', strtotime("+{$nextStep['delay_days']} days"));
        }

        // Mettre à jour l'enrollment
        $this->db->prepare('
            UPDATE crm_sequence_enrollments
            SET current_step = :step, next_send_at = :next_at,
                status = IF(:has_next = 1, "active", "completed")
            WHERE lead_id = :lead_id AND sequence_id = :seq_id
        ')->execute([
            ':step'     => $nextStepOrder,
            ':next_at'  => $nextSendAt,
            ':has_next' => $nextStep ? 1 : 0,
            ':lead_id'  => $leadId,
            ':seq_id'   => $sequenceId,
        ]);

        return true;
    }

    /**
     * Traite tous les emails dus (appelé par le cron).
     */
    public function processDue(): int
    {
        $stmt = $this->db->prepare('
            SELECT e.lead_id, e.sequence_id
            FROM crm_sequence_enrollments e
            WHERE e.status = "active"
              AND e.next_send_at <= NOW()
            LIMIT 50
        ');
        $stmt->execute();
        $due = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count = 0;
        foreach ($due as $row) {
            if ($this->processNextStep((int)$row['lead_id'], (int)$row['sequence_id'])) {
                $count++;
            }
        }

        return $count;
    }

    public function unsubscribe(int $leadId, int $sequenceId): void
    {
        $this->db->prepare('
            UPDATE crm_sequence_enrollments
            SET status = "unsubscribed"
            WHERE lead_id = :lead_id AND sequence_id = :seq_id
        ')->execute([':lead_id' => $leadId, ':seq_id' => $sequenceId]);
    }

    public function getAllSequences(): array
    {
        $stmt = $this->db->query('SELECT * FROM crm_sequences ORDER BY name');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ---- Privé ----

    private function getSequence(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM crm_sequences WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function getEnrollment(int $leadId, int $sequenceId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT * FROM crm_sequence_enrollments
            WHERE lead_id = :lead_id AND sequence_id = :seq_id
        ');
        $stmt->execute([':lead_id' => $leadId, ':seq_id' => $sequenceId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function getStep(int $sequenceId, int $order): ?array
    {
        $stmt = $this->db->prepare('
            SELECT * FROM crm_sequence_steps
            WHERE sequence_id = :seq_id AND step_order = :order
        ');
        $stmt->execute([':seq_id' => $sequenceId, ':order' => $order]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function getLead(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM crm_leads WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function interpolate(string $template, array $lead, array $extra = []): string
    {
        $vars = array_merge([
            '[PRENOM]'       => $lead['first_name'] ?? 'vous',
            '[NOM]'          => $lead['last_name'] ?? '',
            '[EMAIL]'        => $lead['email'] ?? '',
            '[ADVISOR_NAME]' => defined('ADVISOR_NAME') ? ADVISOR_NAME : '',
            '[RDV_URL]'      => defined('APP_URL') ? APP_URL . '/contact' : '',
            '[RESSOURCE_URL]'=> $extra['ressource_url'] ?? '',
            '[CTA_LABEL]'    => $extra['cta_label'] ?? 'Télécharger',
        ], $extra);

        return str_replace(array_keys($vars), array_values($vars), $template);
    }

    private function sendEmail(string $to, string $subject, string $htmlBody): bool
    {
        if (!class_exists('MailService')) {
            require_once ROOT_PATH . '/core/services/MailService.php';
        }

        try {
            MailService::send($to, $subject, strip_tags($htmlBody), $htmlBody);
            return true;
        } catch (\Exception $e) {
            error_log('[SequenceCrmService] Email error: ' . $e->getMessage());
            return false;
        }
    }

    private function complete(int $leadId, int $sequenceId): void
    {
        $this->db->prepare('
            UPDATE crm_sequence_enrollments
            SET status = "completed", next_send_at = NULL
            WHERE lead_id = :lead_id AND sequence_id = :seq_id
        ')->execute([':lead_id' => $leadId, ':seq_id' => $sequenceId]);
    }
}

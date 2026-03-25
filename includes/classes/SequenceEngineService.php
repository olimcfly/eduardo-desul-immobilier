<?php
/**
 * SequenceEngineService
 * Moteur d'envoi des séquences email CRM (cron-safe).
 */
class SequenceEngineService
{
    private PDO $pdo;
    private EmailService $emailService;
    private string $logFile;

    public function __construct(PDO $pdo, ?EmailService $emailService = null)
    {
        $this->pdo = $pdo;
        $this->emailService = $emailService ?? new EmailService($pdo);
        $this->logFile = dirname(__DIR__, 2) . '/logs/sequence_engine.log';
    }

    /**
     * @return array{processed:int,sent:int,failed:int,completed:int,skipped:int,errors:array<int,string>}
     */
    public function run(int $limit = 100, bool $dryRun = false): array
    {
        $result = [
            'processed' => 0,
            'sent' => 0,
            'failed' => 0,
            'completed' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $rows = $this->loadDueEnrollments($limit);
        foreach ($rows as $enrollment) {
            $result['processed']++;
            try {
                $step = $this->loadCurrentStep((int)$enrollment['sequence_id'], (int)$enrollment['current_step']);
                if (!$step) {
                    $this->completeEnrollment((int)$enrollment['id']);
                    $result['completed']++;
                    $this->writeLog('info', 'Enrollment completed (no more steps)', [
                        'enrollment_id' => (int)$enrollment['id'],
                        'sequence_id' => (int)$enrollment['sequence_id'],
                    ]);
                    continue;
                }

                if (($step['step_type'] ?? 'email') !== 'email') {
                    $this->advanceEnrollment((int)$enrollment['id'], (int)$enrollment['sequence_id'], (int)$enrollment['current_step']);
                    $result['skipped']++;
                    $this->writeLog('info', 'Non-email step skipped and advanced', [
                        'enrollment_id' => (int)$enrollment['id'],
                        'step_type' => $step['step_type'] ?? 'unknown',
                        'step_order' => (int)$enrollment['current_step'],
                    ]);
                    continue;
                }

                $subject = $this->replaceVars((string)($step['subject'] ?? ''), $enrollment);
                $bodyHtml = $this->replaceVars((string)($step['body_html'] ?? ''), $enrollment);
                $trackingId = bin2hex(random_bytes(16));

                if ($dryRun) {
                    $this->registerSend((int)$enrollment['id'], (int)$step['id'], (int)$enrollment['lead_id'], (int)$enrollment['sequence_id'], $subject, 'scheduled', $trackingId, null);
                    $this->advanceEnrollment((int)$enrollment['id'], (int)$enrollment['sequence_id'], (int)$enrollment['current_step']);
                    $result['sent']++;
                    continue;
                }

                $sendResult = $this->emailService->sendEmail((string)$enrollment['lead_email'], $subject, $bodyHtml, [
                    'from_email' => $enrollment['from_email'] ?: null,
                    'from_name' => $enrollment['from_name'] ?: null,
                    'reply_to' => $enrollment['reply_to'] ?: null,
                    'lead_id' => (int)$enrollment['lead_id'],
                ]);

                if (!($sendResult['success'] ?? false)) {
                    $error = (string)($sendResult['error'] ?? 'Erreur SMTP inconnue');
                    $this->registerSend((int)$enrollment['id'], (int)$step['id'], (int)$enrollment['lead_id'], (int)$enrollment['sequence_id'], $subject, 'failed', $trackingId, $error);
                    $this->markEnrollmentFailed((int)$enrollment['id'], $error);
                    $result['failed']++;
                    $result['errors'][] = $error;
                    continue;
                }

                $this->registerSend((int)$enrollment['id'], (int)$step['id'], (int)$enrollment['lead_id'], (int)$enrollment['sequence_id'], $subject, 'sent', $trackingId, null);
                $this->advanceEnrollment((int)$enrollment['id'], (int)$enrollment['sequence_id'], (int)$enrollment['current_step']);
                $result['sent']++;
            } catch (Throwable $e) {
                $result['failed']++;
                $result['errors'][] = $e->getMessage();
                $this->writeLog('error', 'Sequence engine exception', [
                    'enrollment_id' => (int)$enrollment['id'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $result;
    }

    private function loadDueEnrollments(int $limit): array
    {
        $sql = "SELECT se.id, se.sequence_id, se.lead_id, se.current_step,
                       l.first_name AS lead_first_name, l.last_name AS lead_last_name,
                       l.email AS lead_email, l.phone AS lead_phone, l.source AS lead_source,
                       s.from_name, s.from_email, s.reply_to
                FROM crm_sequence_enrollments se
                INNER JOIN crm_sequences s ON s.id = se.sequence_id
                LEFT JOIN leads l ON l.id = se.lead_id
                WHERE se.status = 'active'
                  AND s.is_active = 1
                  AND se.next_action_at IS NOT NULL
                  AND se.next_action_at <= NOW()
                  AND l.email IS NOT NULL
                  AND l.email != ''
                ORDER BY se.next_action_at ASC
                LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function loadCurrentStep(int $sequenceId, int $currentStep): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM crm_sequence_steps WHERE sequence_id = ? AND step_order = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$sequenceId, $currentStep]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function advanceEnrollment(int $enrollmentId, int $sequenceId, int $currentStep): void
    {
        $nextStepOrder = $currentStep + 1;
        $nextStepStmt = $this->pdo->prepare("SELECT step_order, delay_days, delay_hours FROM crm_sequence_steps WHERE sequence_id = ? AND step_order = ? AND is_active = 1 LIMIT 1");
        $nextStepStmt->execute([$sequenceId, $nextStepOrder]);
        $nextStep = $nextStepStmt->fetch(PDO::FETCH_ASSOC);

        if (!$nextStep) {
            $this->completeEnrollment($enrollmentId);
            return;
        }

        $delayDays = (int)($nextStep['delay_days'] ?? 0);
        $delayHours = (int)($nextStep['delay_hours'] ?? 0);

        $sql = "UPDATE crm_sequence_enrollments
                SET current_step = ?, next_action_at = DATE_ADD(NOW(), INTERVAL ? DAY) + INTERVAL ? HOUR
                WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nextStepOrder, $delayDays, $delayHours, $enrollmentId]);
    }

    private function completeEnrollment(int $enrollmentId): void
    {
        $this->pdo->prepare("UPDATE crm_sequence_enrollments SET status='completed', completed_at=NOW(), next_action_at=NULL WHERE id=?")
            ->execute([$enrollmentId]);
    }

    private function markEnrollmentFailed(int $enrollmentId, string $error): void
    {
        $this->pdo->prepare("UPDATE crm_sequence_enrollments SET status='failed', metadata = JSON_SET(COALESCE(metadata, JSON_OBJECT()), '$.last_error', ?) WHERE id=?")
            ->execute([$error, $enrollmentId]);
    }

    private function registerSend(int $enrollmentId, int $stepId, int $leadId, int $sequenceId, string $subject, string $status, string $trackingId, ?string $error): void
    {
        $this->pdo->prepare("INSERT INTO crm_sequence_sends
            (enrollment_id, step_id, lead_id, sequence_id, subject, status, tracking_id, scheduled_at, sent_at, error_message)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), IF(?='sent', NOW(), NULL), ?)")
            ->execute([$enrollmentId, $stepId, $leadId, $sequenceId, $subject, $status, $trackingId, $status, $error]);
    }

    private function replaceVars(string $content, array $lead): string
    {
        $map = [
            '{{prenom}}' => trim((string)($lead['lead_first_name'] ?? '')), 
            '{{nom}}' => trim((string)($lead['lead_last_name'] ?? '')),
            '{{email}}' => trim((string)($lead['lead_email'] ?? '')),
            '{{telephone}}' => trim((string)($lead['lead_phone'] ?? '')),
            '{{source}}' => trim((string)($lead['lead_source'] ?? 'CRM')),
            '{{agent_nom}}' => trim((string)($lead['from_name'] ?? 'Votre conseiller')),
            '{{agent_tel}}' => trim((string)($lead['lead_phone'] ?? '')), // fallback
            '{{site_url}}' => 'https://eduardo-desul-immobilier.fr',
            '{{lien_desinscription}}' => '#',
        ];

        return strtr($content, $map);
    }

    private function writeLog(string $level, string $message, array $context = []): void
    {
        $line = sprintf(
            "%s | %s | %s | %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            json_encode($context, JSON_UNESCAPED_UNICODE)
        );
        $dir = dirname($this->logFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($this->logFile, $line, FILE_APPEND | LOCK_EX);
    }
}

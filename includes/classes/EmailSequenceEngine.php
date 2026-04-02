<?php
declare(strict_types=1);

/**
 * EmailSequenceEngine
 * Gestion complète des campagnes et séquences email
 */
class EmailSequenceEngine
{
    private PDO $db;
    private SmtpMailer $mailer;

    public function __construct(PDO $db, SmtpMailer $mailer)
    {
        $this->db = $db;
        $this->mailer = $mailer;
    }

    public function getAllCampaigns(): array
    {
        return $this->db->query(" 
            SELECT ec.*,
                   COUNT(ess.id) AS steps_count,
                   SUM(ess.sends_count) AS total_sends,
                   SUM(ess.opens_count) AS total_opens,
                   ROUND(
                       CASE WHEN SUM(ess.sends_count) > 0
                       THEN (SUM(ess.opens_count) / SUM(ess.sends_count)) * 100
                       ELSE 0 END, 1
                   ) AS avg_open_rate
            FROM `email_campaigns` ec
            LEFT JOIN `email_sequence_steps` ess ON ess.campaign_id = ec.id
            GROUP BY ec.id
            ORDER BY ec.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCampaign(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `email_campaigns` WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function createCampaign(array $data): int
    {
        $stmt = $this->db->prepare(" 
            INSERT INTO `email_campaigns` (
                name, type, status, from_name, from_email,
                reply_to, subject_prefix, description, `trigger`, trigger_data
            ) VALUES (
                :name, :type, 'draft', :from_name, :from_email,
                :reply_to, :subject_prefix, :description, :trigger, :trigger_data
            )
        ");
        $stmt->execute([
            ':name' => $data['name'],
            ':type' => $data['type'] ?? 'sequence',
            ':from_name' => $data['from_name'],
            ':from_email' => $data['from_email'],
            ':reply_to' => $data['reply_to'] ?? null,
            ':subject_prefix' => $data['subject_prefix'] ?? null,
            ':description' => $data['description'] ?? null,
            ':trigger' => $data['trigger'] ?? 'landing_optin',
            ':trigger_data' => json_encode($data['trigger_data'] ?? []),
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getSteps(int $campaignId): array
    {
        $stmt = $this->db->prepare(" 
            SELECT * FROM `email_sequence_steps`
            WHERE campaign_id = :cid
            ORDER BY step_number ASC
        ");
        $stmt->execute([':cid' => $campaignId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addStep(int $campaignId, array $data): int
    {
        $stmt = $this->db->prepare("SELECT COALESCE(MAX(step_number), 0) + 1 FROM `email_sequence_steps` WHERE campaign_id = :cid");
        $stmt->execute([':cid' => $campaignId]);
        $stepNumber = (int)$stmt->fetchColumn();

        $insert = $this->db->prepare(" 
            INSERT INTO `email_sequence_steps` (
                campaign_id, step_number, name, subject, preview_text,
                body_html, body_text, delay_days, delay_hours,
                condition, utm_campaign, utm_content, is_active
            ) VALUES (
                :campaign_id, :step_number, :name, :subject, :preview_text,
                :body_html, :body_text, :delay_days, :delay_hours,
                :condition, :utm_campaign, :utm_content, 1
            )
        ");
        $insert->execute([
            ':campaign_id' => $campaignId,
            ':step_number' => $stepNumber,
            ':name' => $data['name'],
            ':subject' => $data['subject'],
            ':preview_text' => $data['preview_text'] ?? null,
            ':body_html' => $data['body_html'],
            ':body_text' => $data['body_text'] ?? null,
            ':delay_days' => $data['delay_days'] ?? 0,
            ':delay_hours' => $data['delay_hours'] ?? 9,
            ':condition' => json_encode($data['condition'] ?? null),
            ':utm_campaign' => $data['utm_campaign'] ?? null,
            ':utm_content' => $data['utm_content'] ?? null,
        ]);

        $this->db->prepare("UPDATE `email_campaigns` SET total_steps = :s WHERE id = :id")
            ->execute([':s' => $stepNumber, ':id' => $campaignId]);

        return (int)$this->db->lastInsertId();
    }

    public function updateStep(int $stepId, array $data): bool
    {
        $stmt = $this->db->prepare(" 
            UPDATE `email_sequence_steps` SET
                name = :name,
                subject = :subject,
                preview_text = :preview_text,
                body_html = :body_html,
                body_text = :body_text,
                delay_days = :delay_days,
                delay_hours = :delay_hours,
                is_active = :is_active
            WHERE id = :id
        ");
        return $stmt->execute([
            ':name' => $data['name'],
            ':subject' => $data['subject'],
            ':preview_text' => $data['preview_text'] ?? null,
            ':body_html' => $data['body_html'],
            ':body_text' => $data['body_text'] ?? null,
            ':delay_days' => $data['delay_days'] ?? 0,
            ':delay_hours' => $data['delay_hours'] ?? 9,
            ':is_active' => $data['is_active'] ?? 1,
            ':id' => $stepId,
        ]);
    }

    public function processPendingSends(int $limit = 50): array
    {
        $stmt = $this->db->prepare(" 
            SELECT es.*,
                   ess.body_html,
                   ess.body_text,
                   ess.preview_text,
                   ess.utm_campaign,
                   ess.utm_content,
                   ess.step_number,
                   ess.campaign_id AS seq_id,
                   ec.from_name,
                   ec.from_email,
                   ec.reply_to,
                   ec.name AS campaign_name,
                   ll.first_name,
                   ll.last_name
            FROM `email_sends` es
            JOIN `email_sequence_steps` ess ON ess.id = es.step_id
            JOIN `email_campaigns` ec ON ec.id = es.campaign_id
            LEFT JOIN `landing_leads` ll ON ll.id = es.lead_id
            WHERE es.status = 'pending'
              AND es.scheduled_at <= NOW()
              AND ec.status = 'active'
            ORDER BY es.scheduled_at ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $sends = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = ['sent' => 0, 'failed' => 0, 'skipped' => 0];
        foreach ($sends as $send) {
            $result = $this->processSingleSend($send);
            $results[$result]++;
        }

        return $results;
    }

    private function processSingleSend(array $send): string
    {
        try {
            $html = $this->personalizeContent((string)$send['body_html'], $send);
            $text = $this->personalizeContent((string)($send['body_text'] ?? ''), $send);

            $html = $this->addTrackingPixel($html, (int)$send['id']);
            $html = $this->addUtmToLinks($html, $send);

            $sent = $this->mailer->send(
                to: $send['email'],
                toName: trim(($send['first_name'] ?? '') . ' ' . ($send['last_name'] ?? '')),
                subject: $send['subject'],
                html: $html,
                text: $text ?: null,
                fromEmail: $send['from_email'],
                fromName: $send['from_name'],
                replyTo: $send['reply_to'] ?? null,
                messageId: $this->generateMessageId()
            );

            if ($sent) {
                $this->db->prepare(" 
                    UPDATE `email_sends`
                    SET status = 'sent', sent_at = NOW(), message_id = :mid
                    WHERE id = :id
                ")->execute([':mid' => $sent, ':id' => $send['id']]);

                $this->db->prepare("UPDATE `email_sequence_steps` SET sends_count = sends_count + 1 WHERE id = :id")
                    ->execute([':id' => $send['step_id']]);

                $this->scheduleNextStep($send);
                return 'sent';
            }

            throw new RuntimeException('Mailer returned false');

        } catch (Throwable $e) {
            $this->db->prepare("UPDATE `email_sends` SET status = 'failed', error_message = :msg WHERE id = :id")
                ->execute([':msg' => $e->getMessage(), ':id' => $send['id']]);

            return 'failed';
        }
    }

    private function scheduleNextStep(array $currentSend): void
    {
        $nextStep = $this->db->prepare(" 
            SELECT * FROM `email_sequence_steps`
            WHERE campaign_id = :cid
              AND step_number = :next
              AND is_active = 1
            LIMIT 1
        ");
        $nextStep->execute([
            ':cid' => $currentSend['seq_id'],
            ':next' => ((int)$currentSend['step_number']) + 1,
        ]);
        $next = $nextStep->fetch(PDO::FETCH_ASSOC);

        if (!$next) {
            return;
        }

        $scheduledAt = (new DateTime())->modify('+' . $next['delay_days'] . ' days');
        $scheduledAt->setTime((int)$next['delay_hours'], 0, 0);

        $this->db->prepare(" 
            INSERT INTO `email_sends` (
                campaign_id, step_id, lead_id, contact_id,
                email, subject, status, scheduled_at
            ) VALUES (
                :campaign_id, :step_id, :lead_id, :contact_id,
                :email, :subject, 'pending', :scheduled_at
            )
        ")->execute([
            ':campaign_id' => $currentSend['seq_id'],
            ':step_id' => $next['id'],
            ':lead_id' => $currentSend['lead_id'],
            ':contact_id' => $currentSend['contact_id'],
            ':email' => $currentSend['email'],
            ':subject' => $next['subject'],
            ':scheduled_at' => $scheduledAt->format('Y-m-d H:i:s'),
        ]);

        if ($currentSend['lead_id']) {
            $this->db->prepare("UPDATE `landing_leads` SET sequence_step = :step WHERE id = :id")
                ->execute([
                    ':step' => ((int)$currentSend['step_number']) + 1,
                    ':id' => $currentSend['lead_id'],
                ]);
        }
    }

    private function personalizeContent(string $content, array $data): string
    {
        $replacements = [
            '{{first_name}}' => htmlspecialchars((string)($data['first_name'] ?? 'ami(e)')),
            '{{last_name}}' => htmlspecialchars((string)($data['last_name'] ?? '')),
            '{{email}}' => htmlspecialchars((string)($data['email'] ?? '')),
            '{{unsubscribe_url}}' => $this->generateUnsubscribeUrl((string)($data['email'] ?? '')),
            '{{current_year}}' => date('Y'),
        ];
        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    private function addTrackingPixel(string $html, int $sendId): string
    {
        $pixel = '<img src="/track/open/' . $sendId . '/pixel.gif" width="1" height="1" alt="" style="display:none;">';
        return str_replace('</body>', $pixel . '</body>', $html);
    }

    private function addUtmToLinks(string $html, array $send): string
    {
        if (empty($send['utm_campaign'])) {
            return $html;
        }

        return (string)preg_replace_callback(
            '/<a\s+([^>]*href=["\'])([^"\']+)(["\'][^>]*)>/i',
            function ($matches) use ($send) {
                $url = $matches[2];
                if (str_contains($url, 'utm_') || str_starts_with($url, 'mailto:')) {
                    return $matches[0];
                }
                $sep = str_contains($url, '?') ? '&' : '?';
                $utm = 'utm_source=email'
                     . '&utm_medium=sequence'
                     . '&utm_campaign=' . urlencode((string)($send['utm_campaign'] ?? ''))
                     . '&utm_content=' . urlencode((string)($send['utm_content'] ?? ''));
                return '<a ' . $matches[1] . $url . $sep . $utm . $matches[3] . '>';
            },
            $html
        );
    }

    private function generateUnsubscribeUrl(string $email): string
    {
        $token = hash_hmac('sha256', $email, $_ENV['APP_SECRET'] ?? 'secret');
        return '/unsubscribe?email=' . urlencode($email) . '&token=' . $token;
    }

    private function generateMessageId(): string
    {
        return '<' . uniqid('crm_', true) . '@' . ($_SERVER['HTTP_HOST'] ?? 'crm.local') . '>';
    }

    public function trackOpen(int $sendId): void
    {
        $stmt = $this->db->prepare(" 
            UPDATE `email_sends`
            SET status = CASE WHEN status = 'sent' THEN 'opened' ELSE status END,
                opened_at = COALESCE(opened_at, NOW())
            WHERE id = :id
        ");
        $stmt->execute([':id' => $sendId]);

        $this->db->prepare(" 
            UPDATE `email_sequence_steps` ess
            JOIN `email_sends` es ON es.step_id = ess.id
            SET ess.opens_count = ess.opens_count + 1
            WHERE es.id = :id
        ")->execute([':id' => $sendId]);
    }

    public function trackClick(int $sendId, string $url): void
    {
        $this->db->prepare(" 
            UPDATE `email_sends`
            SET status = 'clicked',
                clicked_at = COALESCE(clicked_at, NOW()),
                click_url = :url
            WHERE id = :id
        ")->execute([':url' => $url, ':id' => $sendId]);

        $this->db->prepare(" 
            UPDATE `email_sequence_steps` ess
            JOIN `email_sends` es ON es.step_id = ess.id
            SET ess.clicks_count = ess.clicks_count + 1
            WHERE es.id = :id
        ")->execute([':id' => $sendId]);
    }

    public function handleUnsubscribe(string $email, string $token): bool
    {
        $expected = hash_hmac('sha256', $email, $_ENV['APP_SECRET'] ?? 'secret');
        if (!hash_equals($expected, $token)) {
            return false;
        }

        $this->db->prepare(" 
            UPDATE `email_sends`
            SET status = 'unsubscribed'
            WHERE email = :email AND status = 'pending'
        ")->execute([':email' => $email]);

        $this->db->prepare(" 
            UPDATE `crm_contacts`
            SET status = 'inactive',
                tags = JSON_ARRAY_APPEND(COALESCE(tags, JSON_ARRAY()), '$', 'unsubscribed')
            WHERE email = :email
        ")->execute([':email' => $email]);

        return true;
    }
}

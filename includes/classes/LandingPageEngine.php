<?php
declare(strict_types=1);

/**
 * LandingPageEngine
 * Moteur de création et rendu des pages de capture
 */
class LandingPageEngine
{
    private PDO $db;
    private array $config;

    public const TEMPLATES = [
        'classic'    => 'Classique — Hero + Bénéfices + Formulaire',
        'minimal'    => 'Minimaliste — Formulaire centré',
        'video'      => 'Video Hero + Formulaire',
        'two_cols'   => 'Deux colonnes — Contenu + Formulaire',
        'urgency'    => 'Urgence — Countdown + Offre limitée',
    ];

    public const SOURCES = [
        'google_ads'    => ['label' => 'Google Ads',    'icon' => 'fab fa-google',    'color' => '#4285F4'],
        'facebook_ads'  => ['label' => 'Facebook Ads',  'icon' => 'fab fa-facebook',  'color' => '#1877F2'],
        'instagram_ads' => ['label' => 'Instagram Ads', 'icon' => 'fab fa-instagram', 'color' => '#E4405F'],
        'email'         => ['label' => 'Email',         'icon' => 'fas fa-envelope',  'color' => '#6B7280'],
        'organic'       => ['label' => 'Organique',     'icon' => 'fas fa-leaf',      'color' => '#16A34A'],
        'other'         => ['label' => 'Autre',         'icon' => 'fas fa-link',      'color' => '#9CA3AF'],
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->config = $this->loadConfig();
    }

    private function loadConfig(): array
    {
        $stmt = $this->db->prepare(
            "SELECT `key`, `value` FROM `settings`
             WHERE `group` IN ('smtp','tracking','advisor')"
        );
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        return $rows ?: [];
    }

    public function getAll(string $source = '', string $status = ''): array
    {
        $sql = "SELECT lp.*,
                       r.title AS resource_name,
                       ec.name AS sequence_name
                FROM `landing_pages` lp
                LEFT JOIN `resources` r ON r.id = lp.resource_id
                LEFT JOIN `email_campaigns` ec ON ec.id = lp.sequence_id
                WHERE 1=1";
        $params = [];

        if ($source) {
            $sql .= " AND lp.source = :source";
            $params[] = [':source', $source, PDO::PARAM_STR];
        }
        if ($status) {
            $sql .= " AND lp.status = :status";
            $params[] = [':status', $status, PDO::PARAM_STR];
        }

        $sql .= " ORDER BY lp.created_at DESC";
        $stmt = $this->db->prepare($sql);
        foreach ($params as [$k, $v, $t]) {
            $stmt->bindValue($k, $v, $t);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT lp.*, r.file_url AS resource_file_url
             FROM `landing_pages` lp
             LEFT JOIN `resources` r ON r.id = lp.resource_id
             WHERE lp.slug = :slug LIMIT 1"
        );
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        foreach (['benefits', 'social_proof'] as $field) {
            if (!empty($row[$field])) {
                $row[$field] = json_decode((string)$row[$field], true);
            }
        }
        return $row;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `landing_pages` WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        foreach (['benefits', 'social_proof'] as $field) {
            if (!empty($row[$field])) {
                $row[$field] = json_decode((string)$row[$field], true);
            }
        }
        return $row;
    }

    public function create(array $data): int
    {
        $slug = $this->generateSlug($data['name'] ?? 'page');

        $stmt = $this->db->prepare(" 
            INSERT INTO `landing_pages` (
                slug, name, title, subtitle, source, template,
                headline, subheadline, body_content,
                benefits, social_proof, cta_text, cta_color,
                resource_id, resource_title, resource_image,
                thankyou_slug, thankyou_title, thankyou_message,
                thankyou_redirect, thankyou_redirect_delay,
                sequence_id, gtm_container_id, fb_pixel_id,
                fb_event, google_ads_label, seo_title,
                seo_description, seo_noindex, status
            ) VALUES (
                :slug, :name, :title, :subtitle, :source, :template,
                :headline, :subheadline, :body_content,
                :benefits, :social_proof, :cta_text, :cta_color,
                :resource_id, :resource_title, :resource_image,
                :thankyou_slug, :thankyou_title, :thankyou_message,
                :thankyou_redirect, :thankyou_redirect_delay,
                :sequence_id, :gtm_container_id, :fb_pixel_id,
                :fb_event, :google_ads_label, :seo_title,
                :seo_description, :seo_noindex, :status
            )
        ");

        $thankyouSlug = 'merci-' . $slug;

        $stmt->execute([
            ':slug' => $slug,
            ':name' => $data['name'],
            ':title' => $data['title'],
            ':subtitle' => $data['subtitle'] ?? null,
            ':source' => $data['source'] ?? 'google_ads',
            ':template' => $data['template'] ?? 'classic',
            ':headline' => $data['headline'] ?? null,
            ':subheadline' => $data['subheadline'] ?? null,
            ':body_content' => $data['body_content'] ?? null,
            ':benefits' => json_encode($data['benefits'] ?? []),
            ':social_proof' => json_encode($data['social_proof'] ?? []),
            ':cta_text' => $data['cta_text'] ?? 'Télécharger gratuitement',
            ':cta_color' => $data['cta_color'] ?? '#2563EB',
            ':resource_id' => $data['resource_id'] ?? null,
            ':resource_title' => $data['resource_title'] ?? null,
            ':resource_image' => $data['resource_image'] ?? null,
            ':thankyou_slug' => $thankyouSlug,
            ':thankyou_title' => $data['thankyou_title'] ?? 'Merci ! Votre guide est en route 🎉',
            ':thankyou_message' => $data['thankyou_message'] ?? null,
            ':thankyou_redirect' => $data['thankyou_redirect'] ?? null,
            ':thankyou_redirect_delay' => $data['thankyou_redirect_delay'] ?? 0,
            ':sequence_id' => $data['sequence_id'] ?? null,
            ':gtm_container_id' => $data['gtm_container_id'] ?? null,
            ':fb_pixel_id' => $data['fb_pixel_id'] ?? null,
            ':fb_event' => $data['fb_event'] ?? 'Lead',
            ':google_ads_label' => $data['google_ads_label'] ?? null,
            ':seo_title' => $data['seo_title'] ?? null,
            ':seo_description' => $data['seo_description'] ?? null,
            ':seo_noindex' => $data['seo_noindex'] ?? 1,
            ':status' => $data['status'] ?? 'draft',
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $allowed = [
            'name', 'title', 'subtitle', 'source', 'template',
            'headline', 'subheadline', 'cta_text', 'cta_color',
            'resource_id', 'resource_title', 'resource_image',
            'thankyou_title', 'thankyou_message', 'thankyou_redirect',
            'thankyou_redirect_delay', 'sequence_id', 'gtm_container_id',
            'fb_pixel_id', 'fb_event', 'google_ads_label',
            'seo_title', 'seo_description', 'seo_noindex', 'status',
            'hero_image', 'custom_head_code', 'custom_body_code'
        ];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "`{$field}` = :{$field}";
            }
        }

        foreach (['benefits', 'social_proof'] as $jf) {
            if (array_key_exists($jf, $data)) {
                $data[$jf] = json_encode($data[$jf]);
                $fields[] = "`{$jf}` = :{$jf}";
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE `landing_pages` SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $data[':id'] = $id;
        return $stmt->execute($data);
    }

    public function processLead(int $landingId, array $formData): array
    {
        $errors = $this->validateLeadForm($formData);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $landing = $this->getById($landingId);
        if (!$landing) {
            return ['success' => false, 'errors' => ['page' => 'Page introuvable']];
        }

        $leadId = $this->insertLead($landingId, $formData);
        $contactId = $this->upsertContact($formData, $landing);

        $this->db->prepare("UPDATE `landing_leads` SET contact_id = :cid WHERE id = :lid")
            ->execute([':cid' => $contactId, ':lid' => $leadId]);

        if ($landing['sequence_id']) {
            $this->enrollInSequence($leadId, $contactId, (int)$landing['sequence_id'], (string)$formData['email']);
        }

        $this->db->prepare("UPDATE `landing_pages` SET leads_count = leads_count + 1 WHERE id = :id")
            ->execute([':id' => $landingId]);

        $this->sendConfirmationEmail($formData, $landing);

        return [
            'success' => true,
            'lead_id' => $leadId,
            'contact_id' => $contactId,
            'thankyou_slug' => $landing['thankyou_slug'],
            'redirect_url' => '/merci/' . $landing['thankyou_slug'],
        ];
    }

    private function validateLeadForm(array $data): array
    {
        $errors = [];

        if (empty($data['first_name'])) {
            $errors['first_name'] = 'Le prénom est requis';
        }
        if (empty($data['email']) || !filter_var((string)$data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email invalide';
        }
        if (empty($data['gdpr_consent'])) {
            $errors['gdpr_consent'] = 'Le consentement RGPD est requis';
        }

        return $errors;
    }

    private function insertLead(int $landingId, array $data): int
    {
        $stmt = $this->db->prepare(" 
            INSERT INTO `landing_leads` (
                landing_id, first_name, last_name, email, phone,
                city, project_type, custom_fields, gdpr_consent,
                gdpr_date, source, utm_source, utm_medium,
                utm_campaign, utm_content, utm_term,
                gclid, fbclid, ip_hash, user_agent
            ) VALUES (
                :landing_id, :first_name, :last_name, :email, :phone,
                :city, :project_type, :custom_fields, :gdpr_consent,
                NOW(), :source, :utm_source, :utm_medium,
                :utm_campaign, :utm_content, :utm_term,
                :gclid, :fbclid, :ip_hash, :user_agent
            )
        ");

        $stmt->execute([
            ':landing_id' => $landingId,
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'] ?? null,
            ':email' => strtolower(trim((string)$data['email'])),
            ':phone' => $data['phone'] ?? null,
            ':city' => $data['city'] ?? null,
            ':project_type' => $data['project_type'] ?? null,
            ':custom_fields' => json_encode($data['custom_fields'] ?? []),
            ':gdpr_consent' => 1,
            ':source' => $_COOKIE['utm_source'] ?? ($data['source'] ?? null),
            ':utm_source' => $_COOKIE['utm_source'] ?? ($data['utm_source'] ?? null),
            ':utm_medium' => $_COOKIE['utm_medium'] ?? ($data['utm_medium'] ?? null),
            ':utm_campaign' => $_COOKIE['utm_campaign'] ?? ($data['utm_campaign'] ?? null),
            ':utm_content' => $_COOKIE['utm_content'] ?? ($data['utm_content'] ?? null),
            ':utm_term' => $_COOKIE['utm_term'] ?? ($data['utm_term'] ?? null),
            ':gclid' => $_COOKIE['gclid'] ?? ($data['gclid'] ?? null),
            ':fbclid' => $_COOKIE['fbclid'] ?? ($data['fbclid'] ?? null),
            ':ip_hash' => hash('sha256', $_SERVER['REMOTE_ADDR'] ?? ''),
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    private function upsertContact(array $data, array $landing): int
    {
        $stmt = $this->db->prepare("SELECT id FROM `crm_contacts` WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => strtolower(trim((string)$data['email']))]);
        $existing = $stmt->fetchColumn();

        if ($existing) {
            $this->db->prepare("UPDATE `crm_contacts` SET last_contact_at = NOW(), updated_at = NOW() WHERE id = :id")
                ->execute([':id' => $existing]);
            return (int)$existing;
        }

        $stmt = $this->db->prepare(" 
            INSERT INTO `crm_contacts` (
                type, status, first_name, last_name, email,
                phone, city, project_type, source, source_detail,
                gdpr_consent, gdpr_date, score, last_contact_at
            ) VALUES (
                'prospect', 'new', :first_name, :last_name, :email,
                :phone, :city, :project_type, :source, :source_detail,
                1, NOW(), 20, NOW()
            )
        ");
        $stmt->execute([
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'] ?? '',
            ':email' => strtolower(trim((string)$data['email'])),
            ':phone' => $data['phone'] ?? null,
            ':city' => $data['city'] ?? null,
            ':project_type' => $data['project_type'] ?? null,
            ':source' => 'landing_' . ($landing['source'] ?? 'web'),
            ':source_detail' => $landing['name'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    private function enrollInSequence(int $leadId, int $contactId, int $sequenceId, string $email): void
    {
        $stmt = $this->db->prepare(" 
            SELECT * FROM `email_sequence_steps`
            WHERE campaign_id = :cid AND step_number = 1 AND is_active = 1
            LIMIT 1
        ");
        $stmt->execute([':cid' => $sequenceId]);
        $firstStep = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$firstStep) {
            return;
        }

        $scheduledAt = $this->calculateSendTime((int)$firstStep['delay_days'], (int)$firstStep['delay_hours']);

        $this->db->prepare(" 
            INSERT INTO `email_sends` (
                campaign_id, step_id, lead_id, contact_id,
                email, subject, status, scheduled_at
            ) VALUES (
                :campaign_id, :step_id, :lead_id, :contact_id,
                :email, :subject, 'pending', :scheduled_at
            )
        ")->execute([
            ':campaign_id' => $sequenceId,
            ':step_id' => $firstStep['id'],
            ':lead_id' => $leadId,
            ':contact_id' => $contactId,
            ':email' => $email,
            ':subject' => $firstStep['subject'],
            ':scheduled_at' => $scheduledAt,
        ]);

        $this->db->prepare("UPDATE `landing_leads` SET sequence_enrolled = 1, sequence_step = 1 WHERE id = :id")
            ->execute([':id' => $leadId]);
    }

    private function calculateSendTime(int $days, int $hour): string
    {
        $dt = new DateTime();
        if ($days > 0) {
            $dt->modify("+{$days} days");
        }
        $dt->setTime($hour, 0, 0);
        return $dt->format('Y-m-d H:i:s');
    }

    private function sendConfirmationEmail(array $data, array $landing): void
    {
        $mailer = new SmtpMailer($this->config);
        $mailer->send(
            to: $data['email'],
            toName: $data['first_name'],
            subject: '📩 Votre ' . ($landing['resource_title'] ?? 'guide') . ' est prêt !',
            html: $this->buildConfirmationEmailHtml($data, $landing)
        );
    }

    private function buildConfirmationEmailHtml(array $data, array $landing): string
    {
        $name = htmlspecialchars((string)$data['first_name']);
        $resourceTitle = htmlspecialchars((string)($landing['resource_title'] ?? 'guide'));
        $downloadUrl = !empty($landing['resource_file_url'])
            ? '<a href="' . htmlspecialchars((string)$landing['resource_file_url'])
                . '" style="background:#2563EB;color:#fff;padding:14px 28px;'
                . 'border-radius:8px;text-decoration:none;font-weight:bold;'
                . 'display:inline-block;margin:20px 0;">📥 Télécharger maintenant</a>'
            : '';

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px;color:#1e293b;">
            <h2 style="color:#2563EB;">Bonjour {$name} ! 🎉</h2>
            <p>Merci pour votre inscription.</p>
            <p>Votre <strong>{$resourceTitle}</strong> est disponible :</p>
            {$downloadUrl}
            <hr style="border:none;border-top:1px solid #e2e8f0;margin:30px 0;">
            <p style="color:#64748b;font-size:0.85rem;">
                Vous recevrez prochainement des conseils personnalisés.<br>
                Pour vous désinscrire, <a href="{{unsubscribe_url}}">cliquez ici</a>.
            </p>
        </body>
        </html>
        HTML;
    }

    public function incrementViews(int $landingId): void
    {
        $this->db->prepare("UPDATE `landing_pages` SET views_count = views_count + 1 WHERE id = :id")
            ->execute([':id' => $landingId]);
    }

    public function getStats(int $landingId): array
    {
        $stmt = $this->db->prepare(" 
            SELECT
                lp.views_count,
                lp.leads_count,
                ROUND(CASE WHEN lp.views_count > 0 THEN (lp.leads_count / lp.views_count) * 100 ELSE 0 END, 2) AS conversion_rate,
                COUNT(DISTINCT ll.utm_source) AS sources_count,
                SUM(CASE WHEN ll.utm_source = 'google' THEN 1 ELSE 0 END) AS google_leads,
                SUM(CASE WHEN ll.utm_source = 'facebook' THEN 1 ELSE 0 END) AS fb_leads
            FROM `landing_pages` lp
            LEFT JOIN `landing_leads` ll ON ll.landing_id = lp.id
            WHERE lp.id = :id
            GROUP BY lp.id
        ");
        $stmt->execute([':id' => $landingId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    private function generateSlug(string $text): string
    {
        $slug = strtolower($text);
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', (string)$slug);
        $slug = trim((string)$slug, '-');
        $slug = substr((string)$slug, 0, 80);

        $base = $slug;
        $count = 1;
        while ($this->slugExists((string)$slug)) {
            $slug = $base . '-' . $count++;
        }
        return (string)$slug;
    }

    private function slugExists(string $slug): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `landing_pages` WHERE slug = :slug");
        $stmt->execute([':slug' => $slug]);
        return (int)$stmt->fetchColumn() > 0;
    }
}

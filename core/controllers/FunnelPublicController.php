<?php

require_once MODULES_PATH . '/funnels/repositories/FunnelRepository.php';
require_once MODULES_PATH . '/funnels/services/FunnelService.php';
require_once MODULES_PATH . '/funnels/services/SequenceCrmService.php';
require_once ROOT_PATH . '/core/services/LeadService.php';

class FunnelPublicController
{
    private PDO $db;
    private FunnelRepository $repo;
    private FunnelService $service;

    public function __construct()
    {
        $this->db      = \Database::getInstance();
        $this->repo    = new FunnelRepository($this->db);
        $this->service = new FunnelService($this->db);
    }

    /**
     * Affiche la landing page publique.
     * GET /lp/{slug}
     */
    public function show(array $params): void
    {
        $slug   = $params['slug'] ?? '';
        $funnel = $this->repo->findBySlug($slug);

        if (!$funnel) {
            http_response_code(404);
            include PUBLIC_PATH . '/pages/core/404.php';
            return;
        }

        // Tracking vue
        $this->trackEvent($funnel['id'], 'view');

        // Décoder JSON
        $funnel['thankyou_config'] = json_decode($funnel['thankyou_config'] ?? '{}', true) ?? [];
        $funnel['faq_json']        = json_decode($funnel['faq_json'] ?? '[]', true) ?? [];

        // Données settings conseiller
        $advisorName = defined('ADVISOR_NAME') ? ADVISOR_NAME : setting('advisor_name', '');
        $appUrl      = defined('APP_URL') ? APP_URL : '';

        // Template LP (sans menu)
        $templateFile = PUBLIC_PATH . '/templates/lp/' . $funnel['template_id'] . '.php';
        if (!file_exists($templateFile)) {
            $templateFile = PUBLIC_PATH . '/templates/lp/guide_vendeur_v1.php';
        }

        require PUBLIC_PATH . '/templates/lp/layout_lp.php';
    }

    /**
     * Traite la soumission du formulaire LP.
     * POST /lp/{slug}/submit
     */
    public function submit(array $params): void
    {
        $slug   = $params['slug'] ?? '';
        $funnel = $this->repo->findBySlug($slug);

        if (!$funnel) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Page introuvable']);
            return;
        }

        // Validation basique
        $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $firstName= trim(strip_tags($_POST['first_name'] ?? ''));
        $phone    = trim(strip_tags($_POST['phone'] ?? ''));
        $consent  = !empty($_POST['consent']);

        if (!$email || !$firstName) {
            $this->redirectWithError($slug, 'Prénom et email requis.');
            return;
        }

        if (!$consent) {
            $this->redirectWithError($slug, 'Vous devez accepter la politique de confidentialité.');
            return;
        }

        // Honeypot anti-bot
        if (!empty($_POST['website'])) {
            $this->redirectToThankyou($slug);
            return;
        }

        // Récupérer UTMs depuis POST ou cookie
        $utmSource   = $this->getUtm('utm_source',   $funnel['utm_source'] ?? '');
        $utmMedium   = $this->getUtm('utm_medium',   $funnel['utm_medium'] ?? '');
        $utmCampaign = $this->getUtm('utm_campaign', $funnel['utm_campaign'] ?? '');
        $utmContent  = $this->getUtm('utm_content',  $funnel['utm_content'] ?? '');
        $utmKeyword  = $this->getUtm('utm_term',     $funnel['keyword'] ?? '');

        // Créer le lead
        $leadId = LeadService::capture([
            'source_type'  => LeadService::SOURCE_RESSOURCE,
            'funnel_id'    => $funnel['id'],
            'first_name'   => $firstName,
            'last_name'    => trim(strip_tags($_POST['last_name'] ?? '')),
            'email'        => $email,
            'phone'        => $phone,
            'intent'       => $funnel['persona'] ?? 'vendeur',
            'consent'      => 1,
            'pipeline'     => 'new',
            'stage'        => 'lead',
            'utm_source'   => $utmSource,
            'utm_medium'   => $utmMedium,
            'utm_campaign' => $utmCampaign,
            'utm_content'  => $utmContent,
            'utm_keyword'  => $utmKeyword,
        ]);

        // Tracking soumission
        $this->trackEvent($funnel['id'], 'submit');

        // Inscrire dans la séquence si configurée
        if (!empty($funnel['sequence_id']) && $leadId) {
            $seqService = new SequenceCrmService($this->db);

            // Construire l'URL de téléchargement si ressource liée
            $ressourceUrl = '';
            if (!empty($funnel['ressource_id'])) {
                $ressourceUrl = $this->generateDownloadToken($leadId, (int) $funnel['ressource_id']);
            }

            $seqService->enroll($leadId, (int) $funnel['sequence_id'], [
                '[RESSOURCE_URL]' => $ressourceUrl,
                '[CTA_LABEL]'     => $funnel['cta_label'] ?? 'Télécharger',
            ]);
        }

        $this->redirectToThankyou($slug);
    }

    /**
     * Affiche la thank you page.
     * GET /lp/{slug}/merci
     */
    public function thankyou(array $params): void
    {
        $slug   = $params['slug'] ?? '';
        $funnel = $this->repo->findBySlug($slug);

        if (!$funnel) {
            http_response_code(404);
            return;
        }

        $funnel['thankyou_config'] = json_decode($funnel['thankyou_config'] ?? '{}', true) ?? [];
        $type = $funnel['thankyou_type'] ?? 'telechargement';

        $tplFile = PUBLIC_PATH . '/templates/lp/thankyou/' . $type . '.php';
        if (!file_exists($tplFile)) {
            $tplFile = PUBLIC_PATH . '/templates/lp/thankyou/telechargement.php';
        }

        require PUBLIC_PATH . '/templates/lp/layout_lp.php';
    }

    /**
     * Téléchargement sécurisé d'une ressource.
     * GET /ressource/{token}
     */
    public function download(array $params): void
    {
        $token = $params['token'] ?? '';

        [$leadId, $ressourceId] = $this->decodeDownloadToken($token);

        if (!$leadId || !$ressourceId) {
            http_response_code(403);
            echo 'Lien invalide ou expiré.';
            return;
        }

        $stmt = $this->db->prepare('SELECT * FROM ressources WHERE id = :id AND status = "published"');
        $stmt->execute([':id' => $ressourceId]);
        $ressource = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ressource || empty($ressource['file_path'])) {
            http_response_code(404);
            echo 'Ressource introuvable.';
            return;
        }

        $filePath = ROOT_PATH . '/' . ltrim($ressource['file_path'], '/');
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo 'Fichier introuvable.';
            return;
        }

        // Incrémenter compteur téléchargements
        $this->db->prepare('UPDATE ressources SET downloads = downloads + 1 WHERE id = :id')
                 ->execute([':id' => $ressourceId]);

        $this->trackEventById($ressourceId, 'download');

        $filename = basename($filePath);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache');
        readfile($filePath);
        exit;
    }

    // ---- Privé ----

    private function trackEvent(int $funnelId, string $type): void
    {
        try {
            $sessionId = session_id() ?: bin2hex(random_bytes(16));
            $ipHash    = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');

            $stmt = $this->db->prepare('
                INSERT INTO funnel_events
                    (funnel_id, event_type, session_id, ip_hash, utm_source, utm_medium, utm_campaign, referrer, user_agent)
                VALUES
                    (:fid, :type, :sid, :ip, :src, :med, :cmp, :ref, :ua)
            ');
            $stmt->execute([
                ':fid'  => $funnelId,
                ':type' => $type,
                ':sid'  => $sessionId,
                ':ip'   => $ipHash,
                ':src'  => $this->getUtm('utm_source', ''),
                ':med'  => $this->getUtm('utm_medium', ''),
                ':cmp'  => $this->getUtm('utm_campaign', ''),
                ':ref'  => substr($_SERVER['HTTP_REFERER'] ?? '', 0, 500),
                ':ua'   => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 300),
            ]);
        } catch (\Exception $e) {
            error_log('[FunnelTracking] ' . $e->getMessage());
        }
    }

    private function trackEventById(int $ressourceId, string $type): void
    {
        // Retrouver le funnel lié à la ressource
        $stmt = $this->db->prepare('SELECT id FROM funnels WHERE ressource_id = :rid LIMIT 1');
        $stmt->execute([':rid' => $ressourceId]);
        $funnel = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($funnel) {
            $this->trackEvent((int) $funnel['id'], $type);
        }
    }

    private function getUtm(string $key, string $fallback = ''): string
    {
        return trim(
            $_GET[$key]
            ?? $_COOKIE['utm_' . str_replace('utm_', '', $key)]
            ?? $_POST[$key]
            ?? $fallback
        );
    }

    private function generateDownloadToken(int $leadId, int $ressourceId): string
    {
        $expiry  = time() + (72 * 3600);
        $payload = base64_encode("$leadId:$ressourceId:$expiry");
        $sig     = hash_hmac('sha256', $payload, defined('APP_KEY') ? APP_KEY : 'secret');
        return $payload . '.' . substr($sig, 0, 16);
    }

    private function decodeDownloadToken(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 2) return [0, 0];

        [$payload, $sig] = $parts;
        $expectedSig = substr(hash_hmac('sha256', $payload, defined('APP_KEY') ? APP_KEY : 'secret'), 0, 16);

        if (!hash_equals($expectedSig, $sig)) return [0, 0];

        $decoded = base64_decode($payload);
        [$leadId, $ressourceId, $expiry] = explode(':', $decoded) + [0, 0, 0];

        if (time() > (int) $expiry) return [0, 0];

        return [(int) $leadId, (int) $ressourceId];
    }

    private function redirectToThankyou(string $slug): void
    {
        header('Location: ' . rtrim(APP_URL, '/') . '/lp/' . $slug . '/merci');
        exit;
    }

    private function redirectWithError(string $slug, string $message): void
    {
        $_SESSION['funnel_error'] = $message;
        header('Location: ' . rtrim(APP_URL, '/') . '/lp/' . $slug . '?error=1');
        exit;
    }
}

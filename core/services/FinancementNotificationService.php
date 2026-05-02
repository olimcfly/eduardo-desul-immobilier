<?php
declare(strict_types=1);

/**
 * Après soumission /financement : notification conseiller, accusé prospect (IA si clé dispo), séquence CRM différée.
 * Le lead est déjà enregistré (crm_leads.source_type = financement).
 */
final class FinancementNotificationService
{
    public const CRM_SEQUENCE_NAME = 'Séquence Financement — 3 relances';

    /** Délai avant le 1er e-mail de séquence CRM (jours) — l’accusé IA part immédiatement. */
    public const SEQUENCE_FIRST_STEP_DELAY_DAYS = 2;

    public static function afterCapture(int $leadId, array $formData): void
    {
        if ($leadId <= 0) {
            return;
        }

        $admin = trim((string) (defined('APP_EMAIL') ? APP_EMAIL : ''));
        if ($admin === '') {
            $admin = trim((string) (setting('smtp_from', '') ?: ''));
        }

        $name = trim(($formData['prenom'] ?? '') . ' ' . ($formData['nom'] ?? ''));
        $iaBody = self::prospectBodyWithOptionalAi($formData);

        if ($admin !== '') {
            self::sendAdmin($admin, $formData, $name, $leadId);
        }

        $email = trim((string) ($formData['email'] ?? ''));
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $pre = trim((string) ($formData['prenom'] ?? ''));
            self::sendProspect($email, $pre !== '' ? $pre : 'Bonjour', $iaBody);
        }

        try {
            LeadService::logInteraction(
                $leadId,
                'email',
                'Accusé de réception (financement) + rappel séquence programmé'
            );
        } catch (Throwable) {
        }

        self::enrollCrmSequenceDeferred($leadId);
    }

    private static function prospectBodyWithOptionalAi(array $d): string
    {
        $prenom = trim((string) ($d['prenom'] ?? 'vous'));
        $ligne = '- Type de projet : ' . ($d['type_projet'] ?? '—') . "\n"
            . '- Secteur : ' . ($d['secteur_recherche'] ?? '—') . "\n"
            . '- Budget estimé : ' . ($d['budget_estime'] ?? '—') . "\n"
            . '- Délai : ' . ($d['delai_projet'] ?? '—');

        if (!class_exists('AiService')) {
            require_once ROOT_PATH . '/core/services/AiService.php';
        }

        try {
            $sys = "Tu es " . (defined('ADVISOR_NAME') ? ADVISOR_NAME : "un conseiller immobilier indépendant")
                . " à Bordeaux. Tu réponds en français, 90 à 120 mots maximum, vouvoiement, ton professionnel et rassurant, sans HTML, sans emojis, sans promesse de taux. "
                . "Tu t'appuies sur les seuls faits indiqués par l'utilisateur.";

            $user = "Le prospect a déposé une demande d'accompagnement sur la page « financement ». Rédige le corps d'un e-mail d'accusé de réception personnalisé. "
                . "Inclus : remerciement, une phrase qui montre que le contexte est pris en compte, 2 prochaines étapes courantes (sans être une liste à puces), rappel confidentiel.\n"
                . "Contexte (texte) :\n" . $ligne
                . "\nPrénom : " . $prenom;

            return trim(AiService::ask($sys, $user));
        } catch (Throwable $e) {
            error_log('[FinancementNotification] IA: ' . $e->getMessage());

            return "Bonjour " . $prenom . ",\n\n"
                . "Nous avons bien reçu votre demande d'accompagnement au financement. "
                . "Un conseiller en reprend les éléments (projet, secteur, budget et délai) et revient vers vous par téléphone ou e-mail dans les meilleurs délais, "
                . "sans engagement de votre part.\n\n"
                . "Bien cordialement,\n"
                . (defined('ADVISOR_NAME') ? ADVISOR_NAME : 'L\'équipe');
        }
    }

    private static function sendAdmin(string $to, array $d, string $name, int $leadId): void
    {
        $app = defined('APP_NAME') ? APP_NAME : 'Site';
        $subject = "[Financement] Nouvelle demande — {$name} (lead #{$leadId})";

        $lines = [
            "Lead CRM #{$leadId} — source financement",
            "Nom    : {$name}",
            "Email  : " . ($d['email'] ?? ''),
            "Tél.   : " . ($d['telephone'] ?? '—'),
            "Projet : " . ($d['type_projet'] ?? '—'),
            "Secteur recherché : " . ($d['secteur_recherche'] ?? '—'),
            "Budget estimé   : " . ($d['budget_estime'] ?? '—'),
            "Apport          : " . ($d['apport_personnel'] ?? '—'),
            "Sit. pro.       : " . ($d['situation_professionnelle'] ?? '—'),
            "Délai           : " . ($d['delai_projet'] ?? '—'),
        ];
        $msg = (string) ($d['message'] ?? '');
        if ($msg !== '') {
            $lines[] = "Message : \n" . $msg;
        }
        $text = implode("\n", $lines);

        $tr = static function (string $s): string {
            return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        };
        $html = self::htmlShell(
            'Nouvelle demande financement',
            "<table style=\"width:100%;border-collapse:collapse\">"
            . self::trAdmin('ID lead', (string) $leadId)
            . self::trAdmin('Contact', $tr($name) . " — " . $tr($d['email'] ?? ''))
            . self::trAdmin('Téléphone', $tr($d['telephone'] ?? '—'))
            . self::trAdmin('Projet', $tr($d['type_projet'] ?? '—'))
            . self::trAdmin('Secteur', $tr($d['secteur_recherche'] ?? '—'))
            . self::trAdmin('Budget', $tr($d['budget_estime'] ?? '—'))
            . self::trAdmin('Délai', $tr($d['delai_projet'] ?? '—'))
            . ($msg !== '' ? '<tr><td style="padding:8px;vertical-align:top;font-weight:600">Message</td><td>' . nl2br($tr($msg)) . '</td></tr>' : '')
            . '</table>'
        );

        if (!class_exists('MailService')) {
            require_once ROOT_PATH . '/core/services/MailService.php';
        }
        $ok = MailService::send($to, $subject, $text, $html);
        if (!$ok) {
            error_log('[FinancementNotification] échec envoi e-mail conseiller vers ' . $to . ' (vérifier SMTP dans Paramètres ou .env)');
        }
    }

    private static function trAdmin(string $k, string $v): string
    {
        return '<tr><td style="padding:8px 0;font-weight:600;width:150px">' . $k
            . '</td><td>' . $v . '</td></tr>';
    }

    private static function sendProspect(string $to, string $greeting, string $bodyIa): void
    {
        $app = defined('APP_NAME') ? APP_NAME : 'Eduardo Desul Immobilier';
        $appUrl = defined('APP_URL') ? rtrim((string) APP_URL, '/') : '';
        $contact = $appUrl !== '' ? $appUrl . '/contact' : '/contact';
        $subject = 'Votre demande de financement a bien été reçue — ' . $app;

        $text = $bodyIa . "\n\n" . (defined('ADVISOR_NAME') ? ADVISOR_NAME : '');

        $pHtml = nl2br(htmlspecialchars($bodyIa, ENT_QUOTES, 'UTF-8'));
        $html = self::htmlShell(
            $subject,
            "<div style=\"font-size:15px;line-height:1.65\">{$pHtml}</div>"
            . "<p style=\"margin-top:22px\"><a href=\"{$contact}\" style=\"color:#1a3c5e;font-weight:600\">Accès contact</a></p>"
        );

        if (!class_exists('MailService')) {
            require_once ROOT_PATH . '/core/services/MailService.php';
        }
        $ok = MailService::send($to, $subject, $text, $html);
        if (!$ok) {
            error_log('[FinancementNotification] échec envoi accusé vers ' . $to . ' (vérifier SMTP / spam)');
        }
    }

    private static function htmlShell(string $title, string $bodyHtml): string
    {
        $appName = defined('APP_NAME') ? APP_NAME : 'Site';
        $appUrl  = defined('APP_URL') ? APP_URL : '#';
        $y       = (string) date('Y');

        return "<!DOCTYPE html><html lang='fr'><head><meta charset='UTF-8'><title>" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "</title></head>
<body style='margin:0;background:#f8f7f4;font-family:system-ui,sans-serif;color:#1a1a2e'>
<table width='100%' cellpadding='0' cellspacing='0' style='padding:28px 0'><tr><td align='center'>
<table width='600' style='max-width:600px;background:#fff;border-radius:10px;overflow:hidden;border:1px solid #e5e0d8'>
<tr><td style='background:#1a3c5e;padding:18px 24px'><a href='" . htmlspecialchars($appUrl) . "' style='color:#c9a84c;font-weight:700;text-decoration:none'>"
            . htmlspecialchars($appName) . "</a></td></tr>
<tr><td style='padding:24px 26px 28px'>{$bodyHtml}</td></tr>
<tr><td style='font-size:12px;color:#64748b;padding:12px 24px;text-align:center'>© {$y} " . htmlspecialchars($appName) . "</td></tr>
</table></td></tr></table></body></html>";
    }

    private static function enrollCrmSequenceDeferred(int $leadId): void
    {
        $file = ROOT_PATH . '/modules/funnels/services/SequenceCrmService.php';
        if (!is_file($file)) {
            return;
        }
        require_once $file;
        if (!class_exists('SequenceCrmService')) {
            return;
        }

        try {
            $pdo = db();
            $st  = $pdo->prepare('SELECT id FROM crm_sequences WHERE name = ? AND status = ? LIMIT 1');
            $st->execute([self::CRM_SEQUENCE_NAME, 'active']);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return;
            }
            $seqId = (int) $row['id'];
            $svc   = new SequenceCrmService($pdo);
            $svc->enrollDeferred($leadId, $seqId, self::SEQUENCE_FIRST_STEP_DELAY_DAYS, []);
        } catch (Throwable $e) {
            error_log('[FinancementNotification] séquence CRM: ' . $e->getMessage());
        }
    }
}

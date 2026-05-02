<?php
declare(strict_types=1);

/**
 * Après soumission /avis-de-valeur : e-mail conseiller (récap) + accusé prospect (IA si clé dispo).
 * Lead déjà enregistré (crm_leads.source_type = avis_valeur).
 */
final class AvisValeurNotificationService
{
    /**
     * @param array<string, string> $formData clés attendues : prenom, nom, email, telephone, adresse_bien,
     *                                          type_bien, surface_m2, message
     */
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
                'Accusé de réception (avis de valeur) envoyé'
            );
        } catch (Throwable) {
        }
    }

    private static function prospectBodyWithOptionalAi(array $d): string
    {
        $prenom = trim((string) ($d['prenom'] ?? 'vous'));
        $ligne = '- Adresse du bien : ' . ($d['adresse_bien'] ?? '—') . "\n"
            . '- Type : ' . ($d['type_bien'] ?? '—') . "\n"
            . '- Surface (m²) : ' . ($d['surface_m2'] ?? '—');

        if (!class_exists('AiService')) {
            require_once ROOT_PATH . '/core/services/AiService.php';
        }

        try {
            $sys = "Tu es " . (defined('ADVISOR_NAME') ? ADVISOR_NAME : "un conseiller immobilier indépendant")
                . " à Bordeaux. Tu réponds en français, 90 à 120 mots maximum, vouvoiement, ton professionnel et rassurant, sans HTML, sans emojis. "
                . "Tu t'appuies sur les seuls faits indiqués par l'utilisateur.";

            $user = "Le prospect a déposé une demande d'avis de valeur sur le site. Rédige le corps d'un e-mail d'accusé de réception personnalisé. "
                . "Inclus : remerciement, une phrase montrant que le contexte est pris en compte, 2 prochaines étapes courantes (sans liste à puces), rappel confidentiel.\n"
                . "Contexte (texte) :\n" . $ligne
                . "\nPrénom : " . $prenom;

            return trim(AiService::ask($sys, $user));
        } catch (Throwable $e) {
            error_log('[AvisValeurNotification] IA: ' . $e->getMessage());

            return "Bonjour " . $prenom . ",\n\n"
                . "Nous avons bien reçu votre demande d'avis de valeur. "
                . "Un conseiller en reprend les éléments (adresse, type de bien, surface le cas échéant) et revient vers vous par téléphone ou e-mail dans les meilleurs délais, "
                . "sans engagement de votre part.\n\n"
                . "Bien cordialement,\n"
                . (defined('ADVISOR_NAME') ? ADVISOR_NAME : 'L\'équipe');
        }
    }

    private static function sendAdmin(string $to, array $d, string $name, int $leadId): void
    {
        $subject = "[Avis de valeur] Nouvelle demande — {$name} (lead #{$leadId})";

        $lines = [
            "Lead CRM #{$leadId} — source avis_valeur",
            "Nom    : {$name}",
            "Email  : " . ($d['email'] ?? ''),
            "Tél.   : " . ($d['telephone'] ?? '—'),
            "Adresse du bien : " . ($d['adresse_bien'] ?? '—'),
            "Type de bien    : " . ($d['type_bien'] ?? '—'),
            "Surface (m²)    : " . ($d['surface_m2'] ?? '—'),
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
            'Nouvelle demande avis de valeur',
            "<table style=\"width:100%;border-collapse:collapse\">"
            . self::trAdmin('ID lead', (string) $leadId)
            . self::trAdmin('Contact', $tr($name) . " — " . $tr($d['email'] ?? ''))
            . self::trAdmin('Téléphone', $tr($d['telephone'] ?? '—'))
            . self::trAdmin('Adresse du bien', $tr($d['adresse_bien'] ?? '—'))
            . self::trAdmin('Type', $tr($d['type_bien'] ?? '—'))
            . self::trAdmin('Surface (m²)', $tr($d['surface_m2'] ?? '—'))
            . ($msg !== '' ? '<tr><td style="padding:8px;vertical-align:top;font-weight:600">Message</td><td>' . nl2br($tr($msg)) . '</td></tr>' : '')
            . '</table>'
        );

        if (!class_exists('MailService')) {
            require_once ROOT_PATH . '/core/services/MailService.php';
        }
        $ok = MailService::send($to, $subject, $text, $html);
        if (!$ok) {
            error_log('[AvisValeurNotification] échec envoi e-mail conseiller vers ' . $to);
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
        $subject = 'Votre demande d\'avis de valeur a bien été reçue — ' . $app;

        $text = $bodyIa . "\n\n" . (defined('ADVISOR_NAME') ? ADVISOR_NAME : '');

        $pHtml = nl2br(htmlspecialchars($bodyIa, ENT_QUOTES, 'UTF-8'));
        $html = self::htmlShell(
            $subject,
            "<div style=\"font-size:15px;line-height:1.65\">{$pHtml}</div>"
            . "<p style=\"margin-top:22px\"><a href=\"{$contact}\" style=\"color:#1a3c5e;font-weight:600\">Nous contacter</a></p>"
        );

        if (!class_exists('MailService')) {
            require_once ROOT_PATH . '/core/services/MailService.php';
        }
        $ok = MailService::send($to, $subject, $text, $html);
        if (!$ok) {
            error_log('[AvisValeurNotification] échec envoi accusé vers ' . $to);
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
}

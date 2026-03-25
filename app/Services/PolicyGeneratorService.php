<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

class PolicyGeneratorService
{
    public function __construct(private PDO $db)
    {
    }

    public function generate(int $siteId, array $config, string $version = 'v1'): array
    {
        $siteName = htmlspecialchars(trim((string) ($config['site_name'] ?? '')), ENT_QUOTES, 'UTF-8');
        $email = filter_var(trim((string) ($config['email'] ?? '')), FILTER_VALIDATE_EMAIL) ?: 'privacy@example.com';
        $toolsUsed = array_map(
            static fn ($tool): string => htmlspecialchars(trim((string) $tool), ENT_QUOTES, 'UTF-8'),
            (array) ($config['tools_used'] ?? [])
        );

        $toolsList = $toolsUsed === []
            ? '<li>CRM interne</li>'
            : '<li>' . implode('</li><li>', array_filter($toolsUsed)) . '</li>';

        $html = <<<HTML
<h1>Politique de confidentialité</h1>
<p><strong>Dernière mise à jour :</strong> {DATE}</p>
<p>{SITE_NAME} respecte votre vie privée et traite vos données dans le respect du RGPD.</p>
<h2>1. Responsable du traitement</h2>
<p>Le responsable du traitement est {SITE_NAME}. Contact: <a href="mailto:{EMAIL}">{EMAIL}</a>.</p>
<h2>2. Données collectées</h2>
<p>Nous collectons uniquement les données strictement nécessaires: identité, coordonnées, projet immobilier, consentements.</p>
<h2>3. Outils utilisés</h2>
<ul>{TOOLS_LIST}</ul>
<h2>4. Vos droits</h2>
<p>Vous disposez d'un droit d'accès, rectification, suppression et opposition. Demande à <a href="mailto:{EMAIL}">{EMAIL}</a>.</p>
<h2>5. Durées de conservation</h2>
<p>Les données sont conservées selon les règles de conservation définies par le site.</p>
HTML;

        $rendered = str_replace(
            ['{DATE}', '{SITE_NAME}', '{EMAIL}', '{TOOLS_LIST}'],
            [gmdate('Y-m-d'), $siteName, $email, $toolsList],
            $html
        );

        $id = $this->storePolicy($siteId, $rendered, $version);

        return [
            'id' => $id,
            'site_id' => $siteId,
            'version' => $version,
            'html' => $rendered,
        ];
    }

    public function getLatest(int $siteId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM rgpd_policies WHERE site_id = :site_id ORDER BY created_at DESC LIMIT 1');
        $stmt->execute([':site_id' => $siteId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    private function storePolicy(int $siteId, string $html, string $version): int
    {
        $sql = 'INSERT INTO rgpd_policies (site_id, version, html_content, created_at)
                VALUES (:site_id, :version, :html_content, :created_at)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':site_id' => $siteId,
            ':version' => substr(preg_replace('/[^a-z0-9._-]/i', '', $version), 0, 30),
            ':html_content' => $html,
            ':created_at' => gmdate('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->lastInsertId();
    }
}

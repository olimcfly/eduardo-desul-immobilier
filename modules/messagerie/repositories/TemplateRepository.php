<?php

declare(strict_types=1);

class TemplateRepository
{
    public function __construct(private PDO $pdo) {}

    public function getAll(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM email_templates
             WHERE user_id = ? OR is_default = 1
             ORDER BY category ASC, name ASC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCategory(int $userId, string $category): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM email_templates
             WHERE (user_id = ? OR is_default = 1) AND category = ?
             ORDER BY name ASC
        ");
        $stmt->execute([$userId, $category]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $userId, int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM email_templates WHERE id = ? AND (user_id = ? OR is_default = 1)");
        $stmt->execute([$id, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function insert(int $userId, array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO email_templates (user_id, name, category, subject, body_html)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $data['name'],
            $data['category'] ?? 'general',
            $data['subject'] ?? '',
            $data['body_html'] ?? '',
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $userId, int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE email_templates
               SET name = ?, category = ?, subject = ?, body_html = ?, updated_at = NOW()
             WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([
            $data['name'],
            $data['category'] ?? 'general',
            $data['subject'] ?? '',
            $data['body_html'] ?? '',
            $id,
            $userId,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $userId, int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM email_templates WHERE id = ? AND user_id = ? AND is_default = 0");
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount() > 0;
    }

    public function incrementUsage(int $id): void
    {
        $this->pdo->prepare("UPDATE email_templates SET usage_count = usage_count + 1 WHERE id = ?")
                  ->execute([$id]);
    }

    public function categories(): array
    {
        return [
            'prospection'   => 'Prospection',
            'rdv'           => 'RDV & Visite',
            'suivi_vendeur' => 'Suivi vendeur',
            'suivi_acheteur'=> 'Suivi acheteur',
            'offre'         => 'Offre & Négociation',
            'financement'   => 'Financement',
            'signature'     => 'Signature',
            'apres_vente'   => 'Après-vente',
            'general'       => 'Général',
        ];
    }

    public function seedDefaults(int $userId): void
    {
        // Ne seed que si aucun template n'existe encore pour cet user
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM email_templates WHERE user_id = ?");
        $stmt->execute([$userId]);
        if ((int)$stmt->fetchColumn() > 0) return;

        $defaults = [
            [
                'category' => 'rdv',
                'name'     => 'Confirmation de RDV',
                'subject'  => 'Confirmation de votre rendez-vous — {{date_rdv}}',
                'body_html'=> '<p>Bonjour {{contact_prenom}},</p>
<p>Je vous confirme notre rendez-vous le <strong>{{date_rdv}}</strong> à <strong>{{heure_rdv}}</strong>.</p>
<p>{{details_rdv}}</p>
<p>N\'hésitez pas à me contacter pour toute question.</p>
<p>Cordialement,<br>{{conseiller_nom}}</p>',
            ],
            [
                'category' => 'rdv',
                'name'     => 'Relance après visite',
                'subject'  => 'Suite à votre visite — {{bien_titre}}',
                'body_html'=> '<p>Bonjour {{contact_prenom}},</p>
<p>J\'espère que la visite de <strong>{{bien_titre}}</strong> vous a plu.</p>
<p>Je reste disponible pour répondre à vos questions ou organiser une seconde visite si vous le souhaitez.</p>
<p>Quels sont vos retours sur ce bien ?</p>
<p>Cordialement,<br>{{conseiller_nom}}</p>',
            ],
            [
                'category' => 'suivi_vendeur',
                'name'     => 'Bienvenue — Nouveau mandat',
                'subject'  => 'Bienvenue — Lancement de votre mise en vente',
                'body_html'=> '<p>Bonjour {{contact_prenom}},</p>
<p>Je suis ravi(e) de vous accompagner dans la vente de votre bien.</p>
<p>Votre bien est désormais actif sur notre réseau. Je vous tiendrai informé(e) à chaque étape : visites, retours acheteurs, offres reçues.</p>
<p>N\'hésitez pas à me solliciter à tout moment.</p>
<p>Cordialement,<br>{{conseiller_nom}}</p>',
            ],
            [
                'category' => 'suivi_vendeur',
                'name'     => 'Point hebdomadaire vendeur',
                'subject'  => 'Point de la semaine — {{bien_titre}}',
                'body_html'=> '<p>Bonjour {{contact_prenom}},</p>
<p>Voici le point de la semaine concernant votre bien :</p>
<ul>
<li>Nombre de visites : {{nb_visites}}</li>
<li>Retours : {{retours}}</li>
<li>Prochaines actions : {{prochaines_actions}}</li>
</ul>
<p>Cordialement,<br>{{conseiller_nom}}</p>',
            ],
            [
                'category' => 'offre',
                'name'     => 'Transmission d\'une offre',
                'subject'  => 'Offre d\'achat reçue pour votre bien',
                'body_html'=> '<p>Bonjour {{contact_prenom}},</p>
<p>J\'ai le plaisir de vous transmettre une offre d\'achat pour votre bien :</p>
<p><strong>Montant proposé : {{montant_offre}} €</strong></p>
<p>{{details_offre}}</p>
<p>Je reste disponible pour en discuter et vous conseiller sur la suite.</p>
<p>Cordialement,<br>{{conseiller_nom}}</p>',
            ],
            [
                'category' => 'financement',
                'name'     => 'Suivi dossier financement',
                'subject'  => 'Mise à jour de votre dossier financement',
                'body_html'=> '<p>Bonjour {{contact_prenom}},</p>
<p>Je fais le point sur l\'avancement de votre dossier de financement.</p>
<p>{{statut_financement}}</p>
<p>Les prochaines étapes sont : {{prochaines_etapes}}</p>
<p>Cordialement,<br>{{conseiller_nom}}</p>',
            ],
            [
                'category' => 'signature',
                'name'     => 'Félicitations — Signature acte',
                'subject'  => 'Félicitations pour votre acquisition !',
                'body_html'=> '<p>Bonjour {{contact_prenom}},</p>
<p>Toutes mes félicitations pour la signature de l\'acte authentique !</p>
<p>Ce fut un plaisir de vous accompagner dans ce projet. N\'hésitez pas à faire appel à moi pour votre prochain projet immobilier ou pour recommander mes services à vos proches.</p>
<p>Très cordialement,<br>{{conseiller_nom}}</p>',
            ],
            [
                'category' => 'prospection',
                'name'     => 'Estimation gratuite — Suivi',
                'subject'  => 'Votre estimation gratuite — Résultats',
                'body_html'=> '<p>Bonjour {{contact_prenom}},</p>
<p>Suite à votre demande d\'estimation, j\'ai étudié votre bien avec attention.</p>
<p>La valeur estimée se situe entre <strong>{{prix_min}} €</strong> et <strong>{{prix_max}} €</strong>.</p>
<p>Je serais ravi(e) de vous rencontrer pour vous présenter une analyse complète du marché local et vous proposer une stratégie de vente adaptée.</p>
<p>Cordialement,<br>{{conseiller_nom}}</p>',
            ],
        ];

        foreach ($defaults as $tpl) {
            $this->insert($userId, $tpl);
        }
    }
}

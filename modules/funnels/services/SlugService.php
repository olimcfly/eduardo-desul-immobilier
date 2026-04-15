<?php

class SlugService
{
    /**
     * Génère un slug URL-safe à partir des champs du funnel.
     */
    public static function generateSlug(array $data): string
    {
        $parts = array_filter([
            $data['template_id'] ?? '',
            $data['ville'] ?? '',
            $data['quartier'] ?? '',
            date('Y'),
        ]);

        $raw = implode('-', $parts);
        return self::slugify($raw);
    }

    /**
     * Génère le SEO title (max 70 car.) selon le template + persona + ville.
     */
    public static function generateSeoTitle(array $data): string
    {
        $ville    = $data['ville'] ?? '';
        $quartier = $data['quartier'] ?? '';
        $keyword  = $data['keyword'] ?? '';
        $persona  = $data['persona'] ?? 'vendeur';
        $year     = date('Y');

        $loc = trim("$ville $quartier");

        $patterns = [
            'guide_vendeur_v1'    => "Vendre votre bien à $loc | Guide Gratuit $year",
            'estimation_cta_v1'   => "Estimation Immobilière à $loc | Gratuite & Immédiate",
            'rdv_direct_v1'       => "Prenez Rendez-vous avec votre Expert Immo à $loc",
            'guide_acheteur_v1'   => "Acheter à $loc | Guide Complet $year",
            'guide_local_v1'      => "Guide Immobilier $loc $year | Conseils & Prix du Marché",
            'fiche_ville_v1'      => "Immobilier à $loc — Prix, Tendances & Conseils $year",
            'tunnel_estimation_v1'=> "Estimez votre bien à $loc en 2 minutes",
            'prise_rdv_v1'        => "Conseiller Immobilier à $loc — Réservez votre RDV",
        ];

        $title = $patterns[$data['template_id'] ?? ''] ?? "$keyword à $loc | $year";

        return mb_substr($title, 0, 70);
    }

    /**
     * Génère la meta description (max 160 car.).
     */
    public static function generateMetaDescription(array $data): string
    {
        $ville    = $data['ville'] ?? '';
        $quartier = $data['quartier'] ?? '';
        $loc      = trim("$ville $quartier");
        $year     = date('Y');

        $patterns = [
            'guide_vendeur_v1'    => "Téléchargez gratuitement notre guide complet pour vendre votre bien à $loc au meilleur prix. Conseils d'expert, étapes clés et simulation incluse.",
            'estimation_cta_v1'   => "Obtenez une estimation gratuite et immédiate de votre bien à $loc. Données marché $year, analyse personnalisée en 2 minutes.",
            'rdv_direct_v1'       => "Rencontrez votre conseiller immobilier à $loc. Rendez-vous gratuit, sans engagement. Expertise locale et accompagnement personnalisé.",
            'guide_acheteur_v1'   => "Tout ce qu'il faut savoir pour acheter à $loc : prix du marché $year, quartiers, démarches et conseils d'expert. Guide gratuit à télécharger.",
            'guide_local_v1'      => "Découvrez le marché immobilier à $loc : prix au m², tendances $year, conseils et guide gratuit pour vendre ou acheter dans les meilleures conditions.",
            'fiche_ville_v1'      => "Prix de l'immobilier à $loc : évolution, quartiers, conseils achat/vente $year. Votre expert immobilier local répond à toutes vos questions.",
            'tunnel_estimation_v1'=> "Estimez la valeur de votre bien à $loc en 2 minutes. Estimation indicative basée sur les données du marché local $year.",
            'prise_rdv_v1'        => "Réservez un rendez-vous gratuit avec votre conseiller immobilier à $loc. Estimation, conseil vente ou achat — sans engagement.",
        ];

        $meta = $patterns[$data['template_id'] ?? '']
             ?? "Découvrez nos services immobiliers à $loc. Expertise locale, accompagnement personnalisé.";

        return mb_substr($meta, 0, 160);
    }

    /**
     * Génère le H1 de la landing page.
     */
    public static function generateH1(array $data): string
    {
        $ville    = $data['ville'] ?? '';
        $quartier = $data['quartier'] ?? '';
        $loc      = trim("$ville $quartier");

        $patterns = [
            'guide_vendeur_v1'    => "Vendez votre bien à $loc au meilleur prix",
            'estimation_cta_v1'   => "Quelle est la valeur de votre bien à $loc ?",
            'rdv_direct_v1'       => "Votre expert immobilier à $loc vous reçoit",
            'guide_acheteur_v1'   => "Votre guide pour acheter à $loc",
            'guide_local_v1'      => "Le marché immobilier à $loc en " . date('Y'),
            'fiche_ville_v1'      => "Immobilier à $loc — L'avis de l'expert local",
            'tunnel_estimation_v1'=> "Estimez votre bien à $loc en 2 minutes",
            'prise_rdv_v1'        => "Parlons de votre projet immobilier à $loc",
        ];

        return $patterns[$data['template_id'] ?? ''] ?? "Votre projet immobilier à $loc";
    }

    /**
     * Génère un slug avec auto-incrément si doublon (vérifie via callback).
     */
    public static function uniqueSlug(string $base, callable $existsFn): string
    {
        $slug = self::slugify($base);
        $candidate = $slug;
        $i = 2;

        while ($existsFn($candidate)) {
            $candidate = "$slug-$i";
            $i++;
        }

        return $candidate;
    }

    private static function slugify(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');

        $map = [
            'à'=>'a','â'=>'a','ä'=>'a','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
            'î'=>'i','ï'=>'i','ô'=>'o','ö'=>'o','ù'=>'u','û'=>'u','ü'=>'u',
            'ç'=>'c','ñ'=>'n','œ'=>'oe','æ'=>'ae',
        ];
        $text = strtr($text, $map);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', trim($text));

        return trim($text, '-');
    }
}

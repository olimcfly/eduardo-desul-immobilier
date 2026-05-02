<?php
declare(strict_types=1);


$siteSettings = $siteSettings ?? [];

// CMS override (page: home) - MVP
try {
    $cmsStmt = db()->prepare("SELECT data_json, status FROM cms_pages WHERE slug = 'home' LIMIT 1");
    $cmsStmt->execute();
    $cmsHome = $cmsStmt->fetch(PDO::FETCH_ASSOC);
    if (is_array($cmsHome) && (($cmsHome['status'] ?? '') === 'published')) {
        $cmsJson = json_decode((string)($cmsHome['data_json'] ?? ''), true);
        if (is_array($cmsJson)) {
            foreach ($cmsJson as $key => $value) {
                $siteSettings[$key] = $value;
            }
        }
    }
} catch (Throwable $e) {
    error_log('[CMS HOME] ' . $e->getMessage());
}

// Compteur simple de visites page home (fichier local)
try {
    $viewsPath = dirname(__DIR__, 3) . '/storage/cms-home-views.txt';
    $viewsDir = dirname($viewsPath);
    if (!is_dir($viewsDir)) {
        @mkdir($viewsDir, 0775, true);
    }
    $fh = @fopen($viewsPath, 'c+');
    if ($fh !== false) {
        if (@flock($fh, LOCK_EX)) {
            $rawCount = stream_get_contents($fh);
            $currentCount = max(0, (int)trim((string)$rawCount));
            $currentCount++;
            ftruncate($fh, 0);
            rewind($fh);
            fwrite($fh, (string)$currentCount);
            fflush($fh);
            flock($fh, LOCK_UN);
        }
        fclose($fh);
    }
} catch (Throwable $e) {
    error_log('[HOME VIEWS] ' . $e->getMessage());
}

$cmsApplyCityToken = static function ($value, string $city) use (&$cmsApplyCityToken) {
    if (is_array($value)) {
        $out = [];
        foreach ($value as $k => $v) {
            $out[$k] = $cmsApplyCityToken($v, $city);
        }
        return $out;
    }
    if (is_string($value)) {
        return strtr($value, [
            '{{city}}' => $city,
            '{{zone_city}}' => $city,
            'Votre ville' => $city,
            'secteur de Votre ville' => 'secteur de ' . $city,
            'Guide local local' => 'Guide local ' . $city,
        ]);
    }
    return $value;
};

$advisorName = $advisorName ?? ($siteSettings['advisor_name'] ?? ($_ENV['ADVISOR_NAME'] ?? 'Eduardo Desul'));
$advisorTitle = $advisorTitle ?? ($siteSettings['advisor_title'] ?? ($_ENV['ADVISOR_TITLE'] ?? 'Conseiller immobilier local'));
$advisorPhone = $advisorPhone ?? ($siteSettings['phone'] ?? ($_ENV['APP_PHONE'] ?? ''));
$advisorEmail = $advisorEmail ?? ($siteSettings['email'] ?? ($_ENV['APP_EMAIL'] ?? ''));
$advisorCity = trim((string) ($advisorCity ?? setting('zone_city', APP_CITY ?: 'Bordeaux')));
if ($advisorCity === '') {
    $advisorCity = 'Bordeaux';
}
$siteName = $siteSettings['site_name'] ?? ($_ENV['APP_NAME'] ?? $advisorName);
$territoryName = $siteSettings['territory_name'] ?? ($siteSettings['territory'] ?? $advisorCity);
$brandNetwork = $siteSettings['brand_network'] ?? 'réseau immobilier';
$heroBg = $siteSettings['home_hero_bg'] ?? '/assets/images/hero-bg.jpg';
$advisorPhoto = $siteSettings['advisor_photo'] ?? '/assets/images/placeholder.php';

$pageTitle = $siteSettings['home_meta_title'] ?? "Conseiller immobilier à {$advisorCity} | Eduardo Desul Immobilier";
$metaDesc = $siteSettings['home_meta_description'] ?? "Vendez, achetez ou estimez votre bien à {$advisorCity} avec Eduardo Desul, conseiller immobilier local. Accompagnement humain, stratégie claire et estimation personnalisée.";
$metaKeywords = $siteSettings['home_meta_keywords'] ?? "immobilier {$advisorCity}, estimation immobilière {$advisorCity}, vente immobilière {$advisorCity}, achat immobilier {$advisorCity}";
$extraCss = ['/assets/css/home.css', '/assets/css/mere.css'];

$heroLabel = $siteSettings['home_hero_label'] ?? "Immobilier {$advisorCity} · {$territoryName}";
$heroTitle = $siteSettings['home_hero_title'] ?? "Vendre, acheter et estimer sereinement à {$advisorCity}, avec un conseiller local unique.";
$heroSubtitle = $siteSettings['home_hero_subtitle'] ?? "{$advisorName} vous accompagne de la stratégie jusqu'à la signature : estimation immobilière, vente et recherche ciblée d'opportunités à {$territoryName}.";
$heroPrimaryLabel = $siteSettings['home_hero_primary_label'] ?? 'Demander une estimation gratuite';
$heroPrimaryUrl = $siteSettings['home_hero_primary_url'] ?? '/estimation-gratuite';
$heroSecondaryLabel = $siteSettings['home_hero_secondary_label'] ?? 'Voir les biens à vendre';
$heroSecondaryUrl = $siteSettings['home_hero_secondary_url'] ?? '/biens';
$servicesSectionLabel = $siteSettings['home_services_section_label'] ?? 'Nos services';
$servicesSectionTitle = $siteSettings['home_services_section_title'] ?? 'Vente, achat, estimation : un accompagnement clair.';
$realitySectionLabel = $siteSettings['home_reality_section_label'] ?? 'Votre réalité immobilière';
$realitySectionTitle = $siteSettings['home_reality_section_title'] ?? 'Vous avez un projet sérieux.<br>Vous méritez un accompagnement à la hauteur.';
$realitySectionSubtitle = $siteSettings['home_reality_section_subtitle'] ?? 'Vendre au bon prix, acheter au bon moment et éviter les erreurs inutiles : ce sont les vraies préoccupations d’un projet immobilier.';
$comparisonSectionLabel = $siteSettings['home_comparison_section_label'] ?? 'Ce qui change vraiment';
$comparisonSectionTitle = $siteSettings['home_comparison_section_title'] ?? 'Avec ou sans un conseiller indépendant : la différence concrète.';
$comparisonSectionSubtitle = $siteSettings['home_comparison_section_subtitle'] ?? 'Pas une question de discours. Une question de résultat, de sécurité et de tranquillité d’esprit.';
$aboutSectionLabel = $siteSettings['home_about_section_label'] ?? 'Votre conseiller';
$aboutCtaLabel = $siteSettings['home_about_cta_label'] ?? 'Découvrir son parcours';
$aboutCtaUrl = $siteSettings['home_about_cta_url'] ?? '/a-propos';
$methodSectionLabel = $siteSettings['home_method_section_label'] ?? "La méthode {$advisorName}";
$methodSectionTitle = $siteSettings['home_method_section_title'] ?? 'Une méthode claire en 5 étapes pour sécuriser votre projet.';
$methodSectionSubtitle = $siteSettings['home_method_section_subtitle'] ?? 'Chaque étape a une fonction précise. Rien n’est improvisé.';
$methodPrimaryCtaLabel = $siteSettings['home_method_primary_cta_label'] ?? 'Réserver un rendez-vous';
$methodPrimaryCtaUrl = $siteSettings['home_method_primary_cta_url'] ?? '/contact';
$methodSecondaryCtaLabel = $siteSettings['home_method_secondary_cta_label'] ?? 'Consulter les secteurs';
$methodSecondaryCtaUrl = $siteSettings['home_method_secondary_cta_url'] ?? '/secteurs';
$testimonialsSectionLabel = $siteSettings['home_testimonials_section_label'] ?? "Ils l'ont fait";
$testimonialsSectionTitle = $siteSettings['home_testimonials_section_title'] ?? 'Des résultats concrets, des avis authentiques.';
$testimonialsCtaLabel = $siteSettings['home_testimonials_cta_label'] ?? 'Voir tous les avis clients';
$testimonialsCtaUrl = $siteSettings['home_testimonials_cta_url'] ?? '/avis-clients';
$featuredSectionLabel = $siteSettings['home_featured_section_label'] ?? 'Biens sélectionnés';
$featuredSectionTitle = $siteSettings['home_featured_section_title'] ?? "Des opportunités à {$territoryName}.";
$featuredSectionSubtitle = $siteSettings['home_featured_section_subtitle'] ?? 'Chaque bien est présenté avec ses informations clés pour vous permettre une décision rapide et éclairée.';
$featuredItemCtaLabel = $siteSettings['home_featured_item_cta_label'] ?? 'Voir le bien';
$featuredItemCtaUrl = $siteSettings['home_featured_item_cta_url'] ?? '/biens';
$featuredSectionCtaLabel = $siteSettings['home_featured_section_cta_label'] ?? 'Voir tous les biens disponibles';
$featuredSectionCtaUrl = $siteSettings['home_featured_section_cta_url'] ?? '/biens';
$marketSectionLabel = $siteSettings['home_market_section_label'] ?? 'Le marché local';
$marketSectionTitle = $siteSettings['home_market_section_title'] ?? "Immobilier à {$advisorCity} : comprendre le marché";
$marketSectionSubtitle = $siteSettings['home_market_section_subtitle'] ?? 'Le marché immobilier local demande de la lecture, du timing et une bonne compréhension des attentes acheteurs.';
$marketCtaLabel = $siteSettings['home_market_cta_label'] ?? 'Obtenir une estimation de mon bien';
$marketCtaUrl = $siteSettings['home_market_cta_url'] ?? '/estimation-gratuite';
$sellSectionLabel = $siteSettings['home_sell_section_label'] ?? 'Vendre sereinement';
$sellSectionTitle = $siteSettings['home_sell_section_title'] ?? "Comment vendre un bien immobilier à {$advisorCity}";
$sellSectionSubtitle = $siteSettings['home_sell_section_subtitle'] ?? 'Une vente réussie ne s’improvise pas. Chaque étape demande méthode, disponibilité et clarté.';
$sellCtaLabel = $siteSettings['home_sell_cta_label'] ?? 'Demander un avis de valeur gratuit';
$sellCtaUrl = $siteSettings['home_sell_cta_url'] ?? '/avis-de-valeur';
$faqSectionLabel = $siteSettings['home_faq_section_label'] ?? 'Questions fréquentes';
$faqSectionTitle = $siteSettings['home_faq_section_title'] ?? "FAQ immobilier à {$advisorCity}";
$faqSectionSubtitle = $siteSettings['home_faq_section_subtitle'] ?? 'Les questions que posent le plus souvent les vendeurs et acheteurs.';
$finalPrimaryCtaLabel = $siteSettings['home_final_primary_cta_label'] ?? 'Demander une estimation gratuite';
$finalPrimaryCtaUrl = $siteSettings['home_final_primary_cta_url'] ?? '/estimation-gratuite';
$finalSecondaryCtaLabel = $siteSettings['home_final_secondary_cta_label'] ?? 'Prendre contact';
$finalSecondaryCtaUrl = $siteSettings['home_final_secondary_cta_url'] ?? '/contact';
$finalThirdCtaLabel = $siteSettings['home_final_third_cta_label'] ?? 'Voir les biens';
$finalThirdCtaUrl = $siteSettings['home_final_third_cta_url'] ?? '/biens';
$finalFourthCtaLabel = $siteSettings['home_final_fourth_cta_label'] ?? 'Consulter les secteurs';
$finalFourthCtaUrl = $siteSettings['home_final_fourth_cta_url'] ?? '/secteurs';
$finalFifthCtaLabel = $siteSettings['home_final_fifth_cta_label'] ?? 'Avis clients';
$finalFifthCtaUrl = $siteSettings['home_final_fifth_cta_url'] ?? '/avis-clients';

$heroPillars = $siteSettings['home_hero_pillars'] ?? ['Vente', 'Achat', 'Estimation', 'Accompagnement 360°'];
if (is_string($heroPillars)) {
    $decoded = json_decode($heroPillars, true);
    $heroPillars = is_array($decoded) ? $decoded : array_map('trim', explode(',', $heroPillars));
}

$services = $siteSettings['home_services'] ?? [];
if (is_string($services)) {
    $decoded = json_decode($services, true);
    $services = is_array($decoded) ? $decoded : [];
}
if ($services === []) {
    $services = [
        ['title' => 'Vente', 'text' => 'Stratégie sur mesure pour vendre au bon prix et dans les meilleurs délais.'],
        ['title' => 'Achat', 'text' => 'Accès aux opportunités locales et tri pertinent pour votre projet.'],
        ['title' => 'Estimation', 'text' => 'Analyse précise du marché local pour une valorisation réaliste.'],
    ];
}

$stats = $siteSettings['home_stats'] ?? [
    ['value' => '4,9/5', 'label' => 'Avis clients'],
    ['value' => $territoryName, 'label' => 'Expertise terrain locale'],
    ['value' => '24h', 'label' => 'Délai de réponse'],
    ['value' => '360°', 'label' => 'Accompagnement complet'],
];
if (is_string($stats)) {
    $decoded = json_decode($stats, true);
    $stats = is_array($decoded) ? $decoded : [];
}

$prospectReality = $siteSettings['home_reality_cards'] ?? [
    [
        'title' => 'Vendre au bon prix',
        'text' => "Vous voulez une estimation fiable, pas un prix vitrine. L'objectif : vendre dans les bonnes conditions et dans un délai cohérent avec votre projet.",
    ],
    [
        'title' => 'Trouver les bonnes opportunités',
        'text' => "Le marché bouge vite. Vous avez besoin d'un tri pertinent et d'un accès aux opportunités locales qui correspondent vraiment à votre projet.",
    ],
    [
        'title' => 'Réduire la charge mentale',
        'text' => "Visites, négociation, compromis, suivi notaire : vous ne voulez pas gérer seul chaque détail ni prendre de risques inutiles.",
    ],
];
if (is_string($prospectReality)) {
    $decoded = json_decode($prospectReality, true);
    $prospectReality = is_array($decoded) ? $decoded : [];
}

$comparison = $siteSettings['home_comparison'] ?? [
    'with' => [
        'tag' => 'Avec accompagnement',
        'title' => 'Un projet structuré, sécurisé, sans mauvaise surprise.',
        'items' => [
            'Estimation fondée sur les données réelles du marché local',
            'Stratégie de commercialisation ou de recherche sur mesure',
            'Négociation cadrée et argumentée',
            'Un interlocuteur unique du premier échange jusqu’à la signature',
        ],
    ],
    'without' => [
        'tag' => 'Sans accompagnement',
        'title' => 'Des risques réels sur une décision à fort impact.',
        'items' => [
            'Surestimation ou sous-estimation fréquente',
            'Visibilité limitée et tri peu efficace',
            'Négociation souvent subie',
            'Charge administrative portée seul',
        ],
    ],
];
if (is_string($comparison)) {
    $decoded = json_decode($comparison, true);
    $comparison = is_array($decoded) ? $decoded : [];
}

$aboutTitle = $siteSettings['home_about_title'] ?? "{$advisorName} — conseiller immobilier indépendant, {$territoryName}.";
$aboutText = $siteSettings['home_about_text'] ?? "Interlocuteur unique, {$advisorName} accompagne les projets d'achat, de vente et d'estimation immobilière à {$advisorCity} avec une approche humaine, structurée et rigoureuse.";
$aboutBenefits = $siteSettings['home_about_benefits'] ?? [
    "Expert local : {$territoryName}.",
    'Accompagnement 360° : stratégie, commercialisation, négociation, sécurisation.',
    'Suivi personnalisé du premier échange jusqu’à la signature.',
];
if (is_string($aboutBenefits)) {
    $decoded = json_decode($aboutBenefits, true);
    $aboutBenefits = is_array($decoded) ? $decoded : array_map('trim', explode(',', $aboutBenefits));
}

$steps = $siteSettings['home_steps'] ?? [
    ['num' => '01', 'title' => 'Comprendre votre projet', 'text' => 'Objectifs, contraintes, timing et contexte du projet.'],
    ['num' => '02', 'title' => 'Définir la stratégie', 'text' => 'Positionnement, plan de commercialisation ou cahier de recherche.'],
    ['num' => '03', 'title' => 'Valoriser ou cibler', 'text' => 'Mise en valeur du bien ou sélection affinée des opportunités.'],
    ['num' => '04', 'title' => 'Négocier et sécuriser', 'text' => 'Négociation argumentée, vérifications et cadre sécurisé.'],
    ['num' => '05', 'title' => 'Accompagner jusqu’à la signature', 'text' => 'Suivi notarial et coordination jusqu’à l’acte authentique.'],
];
if (is_string($steps)) {
    $decoded = json_decode($steps, true);
    $steps = is_array($decoded) ? $decoded : [];
}

$testimonials = $siteSettings['home_testimonials'] ?? [
    ['stars' => '★★★★★', 'text' => 'Accompagnement clair du début à la fin. Notre projet a été structuré, fluide et sécurisé.', 'author' => 'Client vendeur'],
    ['stars' => '★★★★★', 'text' => 'Une vraie stratégie et un tri efficace. On a gagné beaucoup de temps.', 'author' => 'Client acquéreur'],
    ['stars' => '★★★★★', 'text' => 'Communication excellente et disponibilité réelle à chaque étape.', 'author' => 'Client estimation'],
];
if (is_string($testimonials)) {
    $decoded = json_decode($testimonials, true);
    $testimonials = is_array($decoded) ? $decoded : [];
}

$featuredProperties = $siteSettings['featured_properties'] ?? [
    [
        'title' => 'Bien mis en avant',
        'city' => $advisorCity,
        'price' => 'Sur demande',
        'surface' => '',
        'rooms' => '',
        'badge' => 'Sélection',
        'image' => '/assets/images/placeholder.php',
    ],
];
if (is_string($featuredProperties)) {
    $decoded = json_decode($featuredProperties, true);
    $featuredProperties = is_array($decoded) ? $decoded : [];
}

$marketCards = $siteSettings['home_market_cards'] ?? [
    [
        'title' => 'Un marché porteur et exigeant',
        'text' => "Le marché de {$territoryName} demande un positionnement juste et une bonne lecture de la demande locale.",
    ],
    [
        'title' => 'Des acheteurs bien informés',
        'text' => "Les acquéreurs comparent, négocient et arbitrent vite. Une estimation réaliste est indispensable.",
    ],
    [
        'title' => "{$advisorCity} et ses environs",
        'text' => "Chaque secteur a ses propres réalités de marché. Une connaissance fine du territoire fait la différence.",
    ],
];
if (is_string($marketCards)) {
    $decoded = json_decode($marketCards, true);
    $marketCards = is_array($decoded) ? $decoded : [];
}

$sellGuide = $siteSettings['home_sell_guide'] ?? [
    [
        'title' => '1. Estimer juste, pas haut',
        'text' => 'Un prix surestimé génère peu de visites et affaiblit la crédibilité de la mise en vente.',
    ],
    [
        'title' => '2. Préparer les documents en amont',
        'text' => 'Anticiper les démarches évite des délais inutiles et rassure les acquéreurs.',
    ],
    [
        'title' => '3. Valoriser et diffuser intelligemment',
        'text' => 'Photos, argumentaire et diffusion ciblée permettent d’attirer des contacts qualifiés.',
    ],
    [
        'title' => '4. Négocier et sécuriser la transaction',
        'text' => 'Une négociation menée avec méthode protège votre prix et la solidité de la vente.',
    ],
];
if (is_string($sellGuide)) {
    $decoded = json_decode($sellGuide, true);
    $sellGuide = is_array($decoded) ? $decoded : [];
}

$faqItems = $siteSettings['home_faq'] ?? [
    [
        'question' => "Combien vaut mon bien immobilier à {$advisorCity} ?",
        'answer' => "Le prix dépend du secteur, de l’état du bien, de ses caractéristiques et des références réelles du marché local. Un avis de valeur sérieux demande une vraie analyse.",
    ],
    [
        'question' => 'Quelle est la différence entre un conseiller indépendant et une agence ?',
        'answer' => 'Le niveau d’implication, le suivi, la disponibilité et la personnalisation de la stratégie changent souvent radicalement.',
    ],
    [
        'question' => 'Quels diagnostics sont nécessaires pour vendre ?',
        'answer' => 'Cela dépend du bien et de sa date de construction. Un accompagnement permet de ne rien oublier.',
    ],
    [
        'question' => "Combien de temps faut-il pour vendre à {$advisorCity} ?",
        'answer' => 'Tout dépend du prix, du bien, du secteur et de la qualité de préparation du dossier de vente.',
    ],
];
if (is_string($faqItems)) {
    $decoded = json_decode($faqItems, true);
    $faqItems = is_array($decoded) ? $decoded : [];
}

$finalCtaTitle = $siteSettings['home_final_cta_title'] ?? "Parlons de votre projet immobilier à {$advisorCity}.";
$finalCtaText = $siteSettings['home_final_cta_text'] ?? 'Choisissez votre prochain pas selon où vous en êtes.';

// Remplacement dynamique des tokens {{city}} sur les données CMS.
$heroLabel = $cmsApplyCityToken($heroLabel, (string)$advisorCity);
$heroTitle = $cmsApplyCityToken($heroTitle, (string)$advisorCity);
$heroSubtitle = $cmsApplyCityToken($heroSubtitle, (string)$advisorCity);
$services = $cmsApplyCityToken($services, (string)$advisorCity);
$stats = $cmsApplyCityToken($stats, (string)$advisorCity);
$prospectReality = $cmsApplyCityToken($prospectReality, (string)$advisorCity);
$comparison = $cmsApplyCityToken($comparison, (string)$advisorCity);
$aboutTitle = $cmsApplyCityToken($aboutTitle, (string)$advisorCity);
$aboutText = $cmsApplyCityToken($aboutText, (string)$advisorCity);
$aboutBenefits = $cmsApplyCityToken($aboutBenefits, (string)$advisorCity);
$steps = $cmsApplyCityToken($steps, (string)$advisorCity);
$testimonials = $cmsApplyCityToken($testimonials, (string)$advisorCity);
$featuredProperties = $cmsApplyCityToken($featuredProperties, (string)$advisorCity);
$marketCards = $cmsApplyCityToken($marketCards, (string)$advisorCity);
$sellGuide = $cmsApplyCityToken($sellGuide, (string)$advisorCity);
$faqItems = $cmsApplyCityToken($faqItems, (string)$advisorCity);
$finalCtaTitle = $cmsApplyCityToken($finalCtaTitle, (string)$advisorCity);
$finalCtaText = $cmsApplyCityToken($finalCtaText, (string)$advisorCity);
$servicesSectionLabel = $cmsApplyCityToken($servicesSectionLabel, (string)$advisorCity);
$servicesSectionTitle = $cmsApplyCityToken($servicesSectionTitle, (string)$advisorCity);
$realitySectionLabel = $cmsApplyCityToken($realitySectionLabel, (string)$advisorCity);
$realitySectionTitle = $cmsApplyCityToken($realitySectionTitle, (string)$advisorCity);
$realitySectionSubtitle = $cmsApplyCityToken($realitySectionSubtitle, (string)$advisorCity);
$comparisonSectionLabel = $cmsApplyCityToken($comparisonSectionLabel, (string)$advisorCity);
$comparisonSectionTitle = $cmsApplyCityToken($comparisonSectionTitle, (string)$advisorCity);
$comparisonSectionSubtitle = $cmsApplyCityToken($comparisonSectionSubtitle, (string)$advisorCity);
$aboutSectionLabel = $cmsApplyCityToken($aboutSectionLabel, (string)$advisorCity);
$aboutCtaLabel = $cmsApplyCityToken($aboutCtaLabel, (string)$advisorCity);
$methodSectionLabel = $cmsApplyCityToken($methodSectionLabel, (string)$advisorCity);
$methodSectionTitle = $cmsApplyCityToken($methodSectionTitle, (string)$advisorCity);
$methodSectionSubtitle = $cmsApplyCityToken($methodSectionSubtitle, (string)$advisorCity);
$methodPrimaryCtaLabel = $cmsApplyCityToken($methodPrimaryCtaLabel, (string)$advisorCity);
$methodSecondaryCtaLabel = $cmsApplyCityToken($methodSecondaryCtaLabel, (string)$advisorCity);
$testimonialsSectionLabel = $cmsApplyCityToken($testimonialsSectionLabel, (string)$advisorCity);
$testimonialsSectionTitle = $cmsApplyCityToken($testimonialsSectionTitle, (string)$advisorCity);
$testimonialsCtaLabel = $cmsApplyCityToken($testimonialsCtaLabel, (string)$advisorCity);
$featuredSectionLabel = $cmsApplyCityToken($featuredSectionLabel, (string)$advisorCity);
$featuredSectionTitle = $cmsApplyCityToken($featuredSectionTitle, (string)$advisorCity);
$featuredSectionSubtitle = $cmsApplyCityToken($featuredSectionSubtitle, (string)$advisorCity);
$featuredItemCtaLabel = $cmsApplyCityToken($featuredItemCtaLabel, (string)$advisorCity);
$featuredSectionCtaLabel = $cmsApplyCityToken($featuredSectionCtaLabel, (string)$advisorCity);
$marketSectionLabel = $cmsApplyCityToken($marketSectionLabel, (string)$advisorCity);
$marketSectionTitle = $cmsApplyCityToken($marketSectionTitle, (string)$advisorCity);
$marketSectionSubtitle = $cmsApplyCityToken($marketSectionSubtitle, (string)$advisorCity);
$marketCtaLabel = $cmsApplyCityToken($marketCtaLabel, (string)$advisorCity);
$sellSectionLabel = $cmsApplyCityToken($sellSectionLabel, (string)$advisorCity);
$sellSectionTitle = $cmsApplyCityToken($sellSectionTitle, (string)$advisorCity);
$sellSectionSubtitle = $cmsApplyCityToken($sellSectionSubtitle, (string)$advisorCity);
$sellCtaLabel = $cmsApplyCityToken($sellCtaLabel, (string)$advisorCity);
$faqSectionLabel = $cmsApplyCityToken($faqSectionLabel, (string)$advisorCity);
$faqSectionTitle = $cmsApplyCityToken($faqSectionTitle, (string)$advisorCity);
$faqSectionSubtitle = $cmsApplyCityToken($faqSectionSubtitle, (string)$advisorCity);
$finalPrimaryCtaLabel = $cmsApplyCityToken($finalPrimaryCtaLabel, (string)$advisorCity);
$finalSecondaryCtaLabel = $cmsApplyCityToken($finalSecondaryCtaLabel, (string)$advisorCity);
$finalThirdCtaLabel = $cmsApplyCityToken($finalThirdCtaLabel, (string)$advisorCity);
$finalFourthCtaLabel = $cmsApplyCityToken($finalFourthCtaLabel, (string)$advisorCity);
$finalFifthCtaLabel = $cmsApplyCityToken($finalFifthCtaLabel, (string)$advisorCity);
?>

<section class="hero hero--premium" aria-labelledby="home-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url('<?= e($heroBg) ?>');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label"><?= e($heroLabel) ?></span>
            <h1 id="home-hero-title"><?= e($heroTitle) ?></h1>
            <p class="hero__subtitle"><?= e($heroSubtitle) ?></p>

            <div class="hero__actions">
                <a href="<?= e($heroPrimaryUrl) ?>" class="btn btn--accent btn--lg"><?= e($heroPrimaryLabel) ?></a>
                <a href="<?= e($heroSecondaryUrl) ?>" class="btn btn--outline-white btn--lg"><?= e($heroSecondaryLabel) ?></a>
            </div>

            <?php if ($heroPillars !== []): ?>
                <div class="hero__pillars" role="list" aria-label="Domaines d'expertise">
                    <?php foreach ($heroPillars as $pillar): ?>
                        <span role="listitem"><?= e((string) $pillar) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section" id="services">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label"><?= e($servicesSectionLabel) ?></span>
            <h2 class="section-title"><?= e($servicesSectionTitle) ?></h2>
        </div>
        <div class="grid-3">
            <?php foreach ($services as $service): ?>
                <article class="card" data-animate>
                    <div class="card__body">
                        <h3 class="card__title"><?= e((string)($service['title'] ?? '')) ?></h3>
                        <p class="card__text"><?= e((string)($service['text'] ?? '')) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div class="stat-strip" role="region" aria-label="Chiffres clés">
    <div class="container">
        <div class="stat-strip__inner">
            <?php foreach ($stats as $item): ?>
                <div class="stat-item">
                    <span class="stat-item__value"><?= e($item['value'] ?? '') ?></span>
                    <span class="stat-item__label"><?= e($item['label'] ?? '') ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<section class="section section--alt" id="realite-prospect">
    <div class="container">
        <div class="section__header">
            <span class="section-label"><?= e($realitySectionLabel) ?></span>
            <h2 class="section-title"><?= nl2br(e($realitySectionTitle)) ?></h2>
            <p class="section-subtitle"><?= e($realitySectionSubtitle) ?></p>
        </div>

        <div class="grid-3">
            <?php foreach ($prospectReality as $card): ?>
                <article class="card" data-animate>
                    <div class="card__body">
                        <h3 class="card__title"><?= e($card['title'] ?? '') ?></h3>
                        <p class="card__text"><?= e($card['text'] ?? '') ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section" id="distinction">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label"><?= e($comparisonSectionLabel) ?></span>
            <h2 class="section-title"><?= e($comparisonSectionTitle) ?></h2>
            <p class="section-subtitle"><?= e($comparisonSectionSubtitle) ?></p>
        </div>

        <div class="grid-2 insight-grid">
            <article class="insight-card insight-card--gain" data-animate>
                <span class="insight-card__tag"><?= e($comparison['with']['tag'] ?? 'Avec accompagnement') ?></span>
                <h3><?= e($comparison['with']['title'] ?? '') ?></h3>
                <ul>
                    <?php foreach (($comparison['with']['items'] ?? []) as $item): ?>
                        <li><?= e((string) $item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </article>

            <article class="insight-card insight-card--risk" data-animate>
                <span class="insight-card__tag"><?= e($comparison['without']['tag'] ?? 'Sans accompagnement') ?></span>
                <h3><?= e($comparison['without']['title'] ?? '') ?></h3>
                <ul>
                    <?php foreach (($comparison['without']['items'] ?? []) as $item): ?>
                        <li><?= e((string) $item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </article>
        </div>
    </div>
</section>

<section class="section section--alt">
    <div class="container grid-2 about-split">
        <div data-animate>
            <span class="section-label"><?= e($aboutSectionLabel) ?></span>
            <h2 class="section-title"><?= e($aboutTitle) ?></h2>
            <p><?= e($aboutText) ?></p>

            <ul class="benefits-list">
                <?php foreach ($aboutBenefits as $item): ?>
                    <li><?= e((string) $item) ?></li>
                <?php endforeach; ?>
            </ul>

            <a href="<?= e($aboutCtaUrl) ?>" class="btn btn--outline"><?= e($aboutCtaLabel) ?></a>
        </div>

        <figure class="about-photo">
            <img src="<?= e($advisorPhoto) ?>" alt="<?= e($advisorName . ', conseiller immobilier à ' . $advisorCity) ?>" loading="lazy">
        </figure>
    </div>
</section>

<section class="section" id="methode">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label"><?= e($methodSectionLabel) ?></span>
            <h2 class="section-title"><?= e($methodSectionTitle) ?></h2>
            <p class="section-subtitle"><?= e($methodSectionSubtitle) ?></p>
        </div>

        <div class="grid-5-steps">
            <?php foreach ($steps as $step): ?>
                <article class="step-card" data-animate>
                    <span class="step-card__num"><?= e($step['num'] ?? '') ?></span>
                    <h3><?= e($step['title'] ?? '') ?></h3>
                    <p><?= e($step['text'] ?? '') ?></p>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-32">
            <a href="<?= e($methodPrimaryCtaUrl) ?>" class="btn btn--primary"><?= e($methodPrimaryCtaLabel) ?></a>
            <a href="<?= e($methodSecondaryCtaUrl) ?>" class="btn btn--outline"><?= e($methodSecondaryCtaLabel) ?></a>
        </div>
    </div>
</section>

<section class="section section--alt" id="preuves-sociales">
    <div class="container">
        <div class="section__header">
            <span class="section-label"><?= e($testimonialsSectionLabel) ?></span>
            <h2 class="section-title"><?= e($testimonialsSectionTitle) ?></h2>
        </div>

        <div class="grid-3">
            <?php foreach ($testimonials as $item): ?>
                <article class="testimonial" data-animate>
                    <div class="testimonial__stars"><?= e($item['stars'] ?? '★★★★★') ?></div>
                    <p class="testimonial__text"><?= e($item['text'] ?? '') ?></p>
                    <p class="testimonial__author"><?= e($item['author'] ?? '') ?></p>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-32">
            <a href="<?= e($testimonialsCtaUrl) ?>" class="btn btn--outline"><?= e($testimonialsCtaLabel) ?></a>
        </div>
    </div>
</section>

<section class="section" id="biens-en-vente">
    <div class="container">
        <div class="section__header">
            <span class="section-label"><?= e($featuredSectionLabel) ?></span>
            <h2 class="section-title"><?= e($featuredSectionTitle) ?></h2>
            <p class="section-subtitle"><?= e($featuredSectionSubtitle) ?></p>
        </div>

        <div class="grid-3">
            <?php foreach ($featuredProperties as $property): ?>
                <article class="card property-card-premium">
                    <img class="card__img" src="<?= e($property['image'] ?? '/assets/images/placeholder.php') ?>" alt="<?= e(($property['title'] ?? 'Bien') . ' à ' . ($property['city'] ?? $advisorCity)) ?>" loading="lazy">
                    <div class="card__body">
                        <span class="property-badge"><?= e($property['badge'] ?? 'Sélection') ?></span>
                        <h3 class="card__title"><?= e($property['title'] ?? '') ?></h3>
                        <p class="card__text property-meta"><?= e($property['city'] ?? '') ?><?= !empty($property['surface']) ? ' · ' . e($property['surface']) : '' ?><?= !empty($property['rooms']) ? ' · ' . e($property['rooms']) : '' ?></p>
                        <p class="property-price"><?= e($property['price'] ?? '') ?></p>
                        <a href="<?= e($featuredItemCtaUrl) ?>" class="btn btn--primary btn--sm"><?= e($featuredItemCtaLabel) ?></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-32">
            <a href="<?= e($featuredSectionCtaUrl) ?>" class="btn btn--outline btn--lg"><?= e($featuredSectionCtaLabel) ?></a>
        </div>
    </div>
</section>

<section class="section section--alt" id="marche-immobilier-local">
    <div class="container">
        <div class="section__header">
            <span class="section-label"><?= e($marketSectionLabel) ?></span>
            <h2 class="section-title"><?= e($marketSectionTitle) ?></h2>
            <p class="section-subtitle"><?= e($marketSectionSubtitle) ?></p>
        </div>

        <div class="grid-3">
            <?php foreach ($marketCards as $card): ?>
                <article class="card">
                    <div class="card__body">
                        <h3 class="card__title"><?= e($card['title'] ?? '') ?></h3>
                        <p class="card__text"><?= e($card['text'] ?? '') ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-32">
            <a href="<?= e($marketCtaUrl) ?>" class="btn btn--primary"><?= e($marketCtaLabel) ?></a>
        </div>
    </div>
</section>

<section class="section" id="comment-vendre-bien-immobilier">
    <div class="container">
        <div class="section__header">
            <span class="section-label"><?= e($sellSectionLabel) ?></span>
            <h2 class="section-title"><?= e($sellSectionTitle) ?></h2>
            <p class="section-subtitle"><?= e($sellSectionSubtitle) ?></p>
        </div>

        <div class="grid-2 sell-guide">
            <?php foreach ($sellGuide as $item): ?>
                <div class="sell-guide__item">
                    <h3 class="sell-guide__step"><?= e($item['title'] ?? '') ?></h3>
                    <p><?= e($item['text'] ?? '') ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-32">
            <a href="<?= e($sellCtaUrl) ?>" class="btn btn--outline"><?= e($sellCtaLabel) ?></a>
        </div>
    </div>
</section>

<section class="section section--alt" id="faq-immobilier-local">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label"><?= e($faqSectionLabel) ?></span>
            <h2 class="section-title"><?= e($faqSectionTitle) ?></h2>
            <p class="section-subtitle"><?= e($faqSectionSubtitle) ?></p>
        </div>

        <div class="faq">
            <?php foreach ($faqItems as $item): ?>
                <div class="faq__item">
                    <h3 class="faq__question"><?= e($item['question'] ?? '') ?></h3>
                    <p class="faq__answer"><?= e($item['answer'] ?? '') ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="cta-banner" id="cta-final">
    <div class="container">
        <h2><?= e($finalCtaTitle) ?></h2>
        <p><?= e($finalCtaText) ?></p>

        <div class="cta-banner__actions">
            <a href="<?= e($finalPrimaryCtaUrl) ?>" class="btn btn--accent btn--lg"><?= e($finalPrimaryCtaLabel) ?></a>
            <a href="<?= e($finalSecondaryCtaUrl) ?>" class="btn btn--outline-white btn--lg"><?= e($finalSecondaryCtaLabel) ?></a>
            <?php if (!empty($advisorPhone)): ?>
                <a href="tel:<?= e(preg_replace('/\s+/', '', (string) $advisorPhone)) ?>" class="btn btn--outline-white btn--lg">Appeler <?= e($advisorName) ?></a>
            <?php endif; ?>
        </div>

        <div class="cta-banner__actions cta-banner__actions--secondary">
            <a href="<?= e($finalThirdCtaUrl) ?>" class="btn btn--outline-white"><?= e($finalThirdCtaLabel) ?></a>
            <a href="<?= e($finalFourthCtaUrl) ?>" class="btn btn--outline-white"><?= e($finalFourthCtaLabel) ?></a>
            <a href="<?= e($finalFifthCtaUrl) ?>" class="btn btn--outline-white"><?= e($finalFifthCtaLabel) ?></a>
        </div>
    </div>
</section>

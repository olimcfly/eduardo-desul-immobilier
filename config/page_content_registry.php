<?php
declare(strict_types=1);

/**
 * Registre des champs CMS par page (généré par scripts/generate_page_content_registry.php).
 * Chaque slug correspond à page_contents.page_slug (ou au slug cms_pages quand mappé).
 */

return array (
  'pages-404' => 
  array (
    'label' => '404',
    'template' => 'pages/404',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '404 — Page introuvable',
          ),
        ),
      ),
    ),
    'tier' => 'secondary',
  ),
  'pages-actualites-index' => 
  array (
    'label' => 'actualites › index',
    'template' => 'pages/actualites/index',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Suivez l\'actualité du marché immobilier bordelais avec <?= ADVISOR_NAME ?>.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-annuaire-fiche' => 
  array (
    'label' => 'annuaire › fiche',
    'template' => 'pages/annuaire/fiche',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-biens-appartements' => 
  array (
    'label' => 'biens › appartements',
    'template' => 'pages/biens/appartements',
    'route_slug' => 'appartements',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => 'Appartements à vendre — Bordeaux & Bordeaux Métropole',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-biens-bien-detail' => 
  array (
    'label' => 'biens › bien — detail',
    'template' => 'pages/biens/bien-detail',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => 'Bien introuvable',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-biens-index' => 
  array (
    'label' => 'biens › index',
    'template' => 'pages/biens/index',
    'route_slug' => 'biens',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Découvrez notre sélection exclusive de biens immobiliers à Bordeaux et dans la Métropole bordelaise.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-biens-maisons' => 
  array (
    'label' => 'biens › maisons',
    'template' => 'pages/biens/maisons',
    'route_slug' => 'maisons',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => 'Biens à vendre — Bordeaux & Bordeaux Métropole',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-biens-prestige' => 
  array (
    'label' => 'biens › prestige',
    'template' => 'pages/biens/prestige',
    'route_slug' => 'prestige',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => 'Biens de prestige — Bordeaux & Bordeaux Métropole',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-biens-vendus' => 
  array (
    'label' => 'biens › vendus',
    'template' => 'pages/biens/vendus',
    'route_slug' => 'biens-vendus',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => 'Biens vendus — Bordeaux & Bordeaux Métropole',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-blog-article' => 
  array (
    'label' => 'blog › article',
    'template' => 'pages/blog/article',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-blog-index' => 
  array (
    'label' => 'blog › index',
    'template' => 'pages/blog/index',
    'route_slug' => 'blog',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Conseils, guides et actualités du marché immobilier à Bordeaux et dans le Bordeaux Métropole.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-capture-estimation-gratuite' => 
  array (
    'label' => 'capture › estimation — gratuite',
    'template' => 'pages/capture/estimation-gratuite',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Estimez gratuitement votre bien immobilier à Bordeaux. Résultat personnalisé sous 48h par <?= ADVISOR_NAME ?>.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-capture-guide-offert' => 
  array (
    'label' => 'capture › guide — offert',
    'template' => 'pages/capture/guide-offert',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Recevez gratuitement le guide immobilier de <?= ADVISOR_NAME ?> : conseils, tendances, stratégies pour réussir votre projet.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-capture-merci' => 
  array (
    'label' => 'capture › merci',
    'template' => 'pages/capture/merci',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'secondary',
  ),
  'pages-conversion-avis-valeur' => 
  array (
    'label' => 'conversion › avis — valeur',
    'template' => 'pages/conversion/avis-valeur',
    'route_slug' => 'avis-de-valeur',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-conversion-international-valuation' => 
  array (
    'label' => 'conversion › international — valuation',
    'template' => 'pages/conversion/international-valuation',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-conversion-merci' => 
  array (
    'label' => 'conversion › merci',
    'template' => 'pages/conversion/merci',
    'route_slug' => 'merci-contact',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'secondary',
  ),
  'pages-conversion-prendre-rendez-vous' => 
  array (
    'label' => 'conversion › prendre — rendez — vous',
    'template' => 'pages/conversion/prendre-rendez-vous',
    'route_slug' => 'prendre-rendez-vous',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => 'Prendre rendez-vous — Estimation affinée',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Demandez une estimation immobilière affinée avec un conseiller.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-conversion-prise-rdv' => 
  array (
    'label' => 'conversion › prise — rdv',
    'template' => 'pages/conversion/prise-rdv',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-core-a-propos' => 
  array (
    'label' => 'core › a — propos',
    'template' => 'pages/core/a-propos',
    'route_slug' => 'a-propos',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => 'À propos — {$advisorName} | Immobilier à {$advisorCity}',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Découvrez {$advisorName}, {$advisorTitle} à {$advisorCity}. Un accompagnement immobilier local, humain et structuré pour vendre, acheter ou estimer votre bien.',
          ),
        ),
      ),
      'content' => 
      array (
        'title' => 'Contenu affiché',
        'fields' => 
        array (
          'territory_description' => 
          array (
            'type' => 'text',
            'label' => 'Territory description',
            'default' => 'J\'accompagne mes clients sur {$advisorCity} et ses environs avec une approche locale, claire et personnalisée.',
          ),
          'about_hero_title' => 
          array (
            'type' => 'text',
            'label' => 'About hero title',
            'default' => '{$advisorName} - Votre partenaire immobilier à {$advisorCity}',
          ),
          'about_hero_subtitle' => 
          array (
            'type' => 'text',
            'label' => 'About hero subtitle',
            'default' => '{$advisorTitle} à {$advisorCity}',
          ),
          'about_intro_title' => 
          array (
            'type' => 'text',
            'label' => 'About intro title',
            'default' => 'Votre allié immobilier pour concrétiser votre projet à {$advisorCity}',
          ),
          'about_intro_text' => 
          array (
            'type' => 'textarea',
            'label' => 'About intro text',
            'default' => 'J\'accompagne vendeurs, acheteurs et investisseurs avec une approche humaine, locale et structurée pour faire avancer chaque projet dans les meilleures conditions.',
          ),
          'about_story_title' => 
          array (
            'type' => 'text',
            'label' => 'About story title',
            'default' => 'Une approche fondée sur l\'écoute, la clarté et l\'action',
          ),
          'about_story_text_1' => 
          array (
            'type' => 'textarea',
            'label' => 'About story text 1',
            'default' => 'Mon rôle ne se limite pas à ouvrir des portes ou diffuser une annonce. Mon objectif est de vous aider à prendre les bonnes décisions, au bon moment, avec une vraie stratégie.',
          ),
          'about_story_text_2' => 
          array (
            'type' => 'text',
            'label' => 'About story text 2',
            'default' => 'Je privilégie une relation simple, directe et transparente, avec une attention particulière portée à la qualité du suivi et à la compréhension du marché local.',
          ),
          'about_cta_title' => 
          array (
            'type' => 'text',
            'label' => 'About cta title',
            'default' => 'Parlons de votre projet immobilier',
          ),
          'about_cta_text' => 
          array (
            'type' => 'text',
            'label' => 'About cta text',
            'default' => 'Vous avez un projet de vente, d\'achat ou besoin d\'un avis de valeur ? Échangeons ensemble sur la meilleure stratégie à mettre en place.',
          ),
          'speciality_1' => 
          array (
            'type' => 'text',
            'label' => 'Speciality 1',
            'default' => 'vente immobilière',
          ),
          'speciality_2' => 
          array (
            'type' => 'text',
            'label' => 'Speciality 2',
            'default' => 'accompagnement local',
          ),
          'speciality_3' => 
          array (
            'type' => 'text',
            'label' => 'Speciality 3',
            'default' => 'estimation immobilière',
          ),
          'years_experience' => 
          array (
            'type' => 'text',
            'label' => 'Years experience',
            'default' => '10+ ans',
          ),
          'approach_label' => 
          array (
            'type' => 'text',
            'label' => 'Approach label',
            'default' => 'Accompagnement humain',
          ),
          'about_cta_primary_label' => 
          array (
            'type' => 'text',
            'label' => 'About cta primary label',
            'default' => 'Contactez-moi',
          ),
          'about_cta_primary_url' => 
          array (
            'type' => 'text',
            'label' => 'About cta primary url',
            'default' => '/contact',
          ),
          'about_cta_secondary_label' => 
          array (
            'type' => 'text',
            'label' => 'About cta secondary label',
            'default' => 'Demander une estimation',
          ),
          'about_cta_secondary_url' => 
          array (
            'type' => 'text',
            'label' => 'About cta secondary url',
            'default' => '/estimation-gratuite',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-core-contact' => 
  array (
    'label' => 'core › contact',
    'template' => 'pages/core/contact',
    'route_slug' => 'contact',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Page title',
            'default' => 'Contact — {{advisor_name}} | Immobilier {{zone_city}}',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Contactez {{advisor_name}}, conseiller immobilier à {{zone_city}}. Réponse rapide et accompagnement personnalisé.',
          ),
        ),
      ),
      'content' => 
      array (
        'title' => 'Contenu affiché',
        'fields' => 
        array (
          'contact_title' => 
          array (
            'type' => 'text',
            'label' => 'Contact title',
            'default' => 'Contactez {{advisor_name}}',
          ),
          'contact_subtitle' => 
          array (
            'type' => 'text',
            'label' => 'Contact subtitle',
            'default' => 'Je vous réponds personnellement sous 24h.',
          ),
          'contact_form_title' => 
          array (
            'type' => 'text',
            'label' => 'Contact form title',
            'default' => 'Envoyez-moi un message',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-core-home' => 
  array (
    'label' => 'core › home',
    'template' => 'pages/core/home',
    'route_slug' => 'home',
    'sections' => 
    array (
      'content' => 
      array (
        'title' => 'Contenu affiché',
        'fields' => 
        array (
          'home_meta_title' => 
          array (
            'type' => 'text',
            'label' => 'Home meta title',
            'default' => 'Immobilier {$advisorCity} — {$advisorName} | Vente, Achat, Estimation',
          ),
          'home_meta_description' => 
          array (
            'type' => 'text',
            'label' => 'Home meta description',
            'default' => '{$advisorTitle} à {$advisorCity} : vente, achat, estimation immobilière et accompagnement local.',
          ),
          'home_meta_keywords' => 
          array (
            'type' => 'text',
            'label' => 'Home meta keywords',
            'default' => 'immobilier {$advisorCity}, estimation immobilière {$advisorCity}, vente immobilière {$advisorCity}, achat immobilier {$advisorCity}',
          ),
          'home_hero_label' => 
          array (
            'type' => 'text',
            'label' => 'Home hero label',
            'default' => 'Immobilier {$advisorCity} · {$territoryName}',
          ),
          'home_hero_title' => 
          array (
            'type' => 'text',
            'label' => 'Home hero title',
            'default' => 'Vendre, acheter et estimer sereinement à {$advisorCity}, avec un conseiller local unique.',
          ),
          'home_hero_subtitle' => 
          array (
            'type' => 'text',
            'label' => 'Home hero subtitle',
            'default' => '{$advisorName} vous accompagne de la stratégie jusqu\'à la signature : estimation immobilière, vente et recherche ciblée d\'opportunités à {$territoryName}.',
          ),
          'home_method_section_label' => 
          array (
            'type' => 'text',
            'label' => 'Home method section label',
            'default' => 'La méthode {$advisorName}',
          ),
          'home_testimonials_section_label' => 
          array (
            'type' => 'text',
            'label' => 'Home testimonials section label',
            'default' => 'Ils l\'ont fait',
          ),
          'home_featured_section_title' => 
          array (
            'type' => 'text',
            'label' => 'Home featured section title',
            'default' => 'Des opportunités à {$territoryName}.',
          ),
          'home_market_section_title' => 
          array (
            'type' => 'text',
            'label' => 'Home market section title',
            'default' => 'Immobilier à {$advisorCity} : comprendre le marché',
          ),
          'home_sell_section_title' => 
          array (
            'type' => 'text',
            'label' => 'Home sell section title',
            'default' => 'Comment vendre un bien immobilier à {$advisorCity}',
          ),
          'home_faq_section_title' => 
          array (
            'type' => 'text',
            'label' => 'Home faq section title',
            'default' => 'FAQ immobilier à {$advisorCity}',
          ),
          'home_about_title' => 
          array (
            'type' => 'text',
            'label' => 'Home about title',
            'default' => '{$advisorName} — conseiller immobilier indépendant, {$territoryName}.',
          ),
          'home_about_text' => 
          array (
            'type' => 'textarea',
            'label' => 'Home about text',
            'default' => 'Interlocuteur unique, {$advisorName} accompagne les projets d\'achat, de vente et d\'estimation immobilière à {$advisorCity} avec une approche humaine, structurée et rigoureuse.',
          ),
          'home_final_cta_title' => 
          array (
            'type' => 'text',
            'label' => 'Home final cta title',
            'default' => 'Parlons de votre projet immobilier à {$advisorCity}.',
          ),
          'brand_network' => 
          array (
            'type' => 'text',
            'label' => 'Brand network',
            'default' => 'réseau immobilier',
          ),
          'home_hero_bg' => 
          array (
            'type' => 'text',
            'label' => 'Home hero bg',
            'default' => '/assets/images/hero-bg.jpg',
          ),
          'advisor_photo' => 
          array (
            'type' => 'text',
            'label' => 'Advisor photo',
            'default' => '/assets/images/placeholder.php',
          ),
          'home_hero_primary_label' => 
          array (
            'type' => 'text',
            'label' => 'Home hero primary label',
            'default' => 'Demander une estimation gratuite',
          ),
          'home_hero_primary_url' => 
          array (
            'type' => 'text',
            'label' => 'Home hero primary url',
            'default' => '/estimation-gratuite',
          ),
          'home_hero_secondary_label' => 
          array (
            'type' => 'text',
            'label' => 'Home hero secondary label',
            'default' => 'Voir les biens à vendre',
          ),
          'home_hero_secondary_url' => 
          array (
            'type' => 'text',
            'label' => 'Home hero secondary url',
            'default' => '/biens',
          ),
          'home_services_section_label' => 
          array (
            'type' => 'text',
            'label' => 'Home services section label',
            'default' => 'Nos services',
          ),
          'home_services_section_title' => 
          array (
            'type' => 'text',
            'label' => 'Home services section title',
            'default' => 'Vente, achat, estimation : un accompagnement clair.',
          ),
          'home_reality_section_label' => 
          array (
            'type' => 'text',
            'label' => 'Home reality section label',
            'default' => 'Votre réalité immobilière',
          ),
          'home_reality_section_title' => 
          array (
            'type' => 'text',
            'label' => 'Home reality section title',
            'default' => 'Vous avez un projet sérieux.<br>Vous méritez un accompagnement à la hauteur.',
          ),
          'home_reality_section_subtitle' => 
          array (
            'type' => 'text',
            'label' => 'Home reality section subtitle',
            'default' => 'Vendre au bon prix, acheter au bon moment et éviter les erreurs inutiles : ce sont les vraies préoccupations d’un projet immobilier.',
          ),
          'home_comparison_section_label' => 
          array (
            'type' => 'text',
            'label' => 'Home comparison section label',
            'default' => 'Ce qui change vraiment',
          ),
          'home_comparison_section_title' => 
          array (
            'type' => 'text',
            'label' => 'Home comparison section title',
            'default' => 'Avec ou sans un conseiller indépendant : la différence concrète.',
          ),
          'home_comparison_section_subtitle' => 
          array (
            'type' => 'text',
            'label' => 'Home comparison section subtitle',
            'default' => 'Pas une question de discours. Une question de résultat, de sécurité et de tranquillité d’esprit.',
          ),
          'home_about_section_label' => 
          array (
            'type' => 'text',
            'label' => 'Home about section label',
            'default' => 'Votre conseiller',
          ),
          'home_about_cta_label' => 
          array (
            'type' => 'text',
            'label' => 'Home about cta label',
            'default' => 'Découvrir son parcours',
          ),
          'home_about_cta_url' => 
          array (
            'type' => 'text',
            'label' => 'Home about cta url',
            'default' => '/a-propos',
          ),
          'home_method_section_title' => 
          array (
            'type' => 'text',
            'label' => 'Home method section title',
            'default' => 'Une méthode claire en 5 étapes pour sécuriser votre projet.',
          ),
          'home_method_section_subtitle' => 
          array (
            'type' => 'text',
            'label' => 'Home method section subtitle',
            'default' => 'Chaque étape a une fonction précise. Rien n’est improvisé.',
          ),
          'home_method_primary_cta_label' => 
          array (
            'type' => 'text',
            'label' => 'Home method primary cta label',
            'default' => 'Réserver un rendez-vous',
          ),
          'home_method_primary_cta_url' => 
          array (
            'type' => 'text',
            'label' => 'Home method primary cta url',
            'default' => '/contact',
          ),
          'home_method_secondary_cta_label' => 
          array (
            'type' => 'text',
            'label' => 'Home method secondary cta label',
            'default' => 'Consulter les secteurs',
          ),
          'home_method_secondary_cta_url' => 
          array (
            'type' => 'text',
            'label' => 'Home method secondary cta url',
            'default' => '/secteurs',
          ),
          'home_testimonials_section_title' => 
          array (
            'type' => 'text',
            'label' => 'Home testimonials section title',
            'default' => 'Des résultats concrets, des avis authentiques.',
          ),
          'home_testimonials_cta_label' => 
          array (
            'type' => 'text',
            'label' => 'Home testimonials cta label',
            'default' => 'Voir tous les avis clients',
          ),
          'home_testimonials_cta_url' => 
          array (
            'type' => 'text',
            'label' => 'Home testimonials cta url',
            'default' => '/avis-clients',
          ),
          'home_featured_section_label' => 
          array (
            'type' => 'text',
            'label' => 'Home featured section label',
            'default' => 'Biens sélectionnés',
          ),
          'home_featured_section_subtitle' => 
          array (
            'type' => 'text',
            'label' => 'Home featured section subtitle',
            'default' => 'Chaque bien est présenté avec ses informations clés pour vous permettre une décision rapide et éclairée.',
          ),
          'home_featured_item_cta_label' => 
          array (
            'type' => 'text',
            'label' => 'Home featured item cta label',
            'default' => 'Voir le bien',
          ),
          'home_featured_item_cta_url' => 
          array (
            'type' => 'text',
            'label' => 'Home featured item cta url',
            'default' => '/biens',
          ),
          'home_featured_section_cta_label' => 
          array (
            'type' => 'text',
            'label' => 'Home featured section cta label',
            'default' => 'Voir tous les biens disponibles',
          ),
          'home_featured_section_cta_url' => 
          array (
            'type' => 'text',
            'label' => 'Home featured section cta url',
            'default' => '/biens',
          ),
          'home_market_section_label' => 
          array (
            'type' => 'text',
            'label' => 'Home market section label',
            'default' => 'Le marché local',
          ),
          'home_market_section_subtitle' => 
          array (
            'type' => 'text',
            'label' => 'Home market section subtitle',
            'default' => 'Le marché immobilier local demande de la lecture, du timing et une bonne compréhension des attentes acheteurs.',
          ),
          'home_market_cta_label' => 
          array (
            'type' => 'text',
            'label' => 'Home market cta label',
            'default' => 'Obtenir une estimation de mon bien',
          ),
          'home_market_cta_url' => 
          array (
            'type' => 'text',
            'label' => 'Home market cta url',
            'default' => '/estimation-gratuite',
          ),
          'home_sell_section_label' => 
          array (
            'type' => 'text',
            'label' => 'Home sell section label',
            'default' => 'Vendre sereinement',
          ),
          'home_sell_section_subtitle' => 
          array (
            'type' => 'text',
            'label' => 'Home sell section subtitle',
            'default' => 'Une vente réussie ne s’improvise pas. Chaque étape demande méthode, disponibilité et clarté.',
          ),
          'home_sell_cta_label' => 
          array (
            'type' => 'text',
            'label' => 'Home sell cta label',
            'default' => 'Demander un avis de valeur gratuit',
          ),
          'home_sell_cta_url' => 
          array (
            'type' => 'text',
            'label' => 'Home sell cta url',
            'default' => '/avis-de-valeur',
          ),
          'home_faq_section_label' => 
          array (
            'type' => 'text',
            'label' => 'Home faq section label',
            'default' => 'Questions fréquentes',
          ),
          'home_faq_section_subtitle' => 
          array (
            'type' => 'text',
            'label' => 'Home faq section subtitle',
            'default' => 'Les questions que posent le plus souvent les vendeurs et acheteurs.',
          ),
          'home_final_primary_cta_label' => 
          array (
            'type' => 'text',
            'label' => 'Home final primary cta label',
            'default' => 'Demander une estimation gratuite',
          ),
          'home_final_primary_cta_url' => 
          array (
            'type' => 'text',
            'label' => 'Home final primary cta url',
            'default' => '/estimation-gratuite',
          ),
          'home_final_secondary_cta_label' => 
          array (
            'type' => 'text',
            'label' => 'Home final secondary cta label',
            'default' => 'Prendre contact',
          ),
          'home_final_secondary_cta_url' => 
          array (
            'type' => 'text',
            'label' => 'Home final secondary cta url',
            'default' => '/contact',
          ),
          'home_final_third_cta_label' => 
          array (
            'type' => 'text',
            'label' => 'Home final third cta label',
            'default' => 'Voir les biens',
          ),
          'home_final_third_cta_url' => 
          array (
            'type' => 'text',
            'label' => 'Home final third cta url',
            'default' => '/biens',
          ),
          'home_final_fourth_cta_label' => 
          array (
            'type' => 'text',
            'label' => 'Home final fourth cta label',
            'default' => 'Consulter les secteurs',
          ),
          'home_final_fourth_cta_url' => 
          array (
            'type' => 'text',
            'label' => 'Home final fourth cta url',
            'default' => '/secteurs',
          ),
          'home_final_fifth_cta_label' => 
          array (
            'type' => 'text',
            'label' => 'Home final fifth cta label',
            'default' => 'Avis clients',
          ),
          'home_final_fifth_cta_url' => 
          array (
            'type' => 'text',
            'label' => 'Home final fifth cta url',
            'default' => '/avis-clients',
          ),
          'home_final_cta_text' => 
          array (
            'type' => 'text',
            'label' => 'Home final cta text',
            'default' => 'Choisissez votre prochain pas selon où vous en êtes.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-core-plan-du-site' => 
  array (
    'label' => 'core › plan — du — site',
    'template' => 'pages/core/plan-du-site',
    'route_slug' => 'plan-du-site',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => 'Plan du site',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Plan du site HTML de {$advisorName} : accès rapide à toutes les pages principales.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-estimation-estimation-gratuite' => 
  array (
    'label' => 'estimation › estimation — gratuite',
    'template' => 'pages/estimation/estimation-gratuite',
    'route_slug' => 'estimation-gratuite',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => 'Estimation gratuite — Eduardo Desul | Conseiller Immobilier Bordeaux',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Obtenez une fourchette d\'estimation basée sur les ventes réelles de Bordeaux et sa métropole (33). Gratuit, instantané, sans inscription.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-estimation-instantanee' => 
  array (
    'label' => 'estimation — instantanee',
    'template' => 'pages/estimation-instantanee',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => 'Estimation instantanée — Eduardo Desul Immobilier',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Estimation immobilière instantanée basée sur les données DVF.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-estimation-merc-estimation' => 
  array (
    'label' => 'estimation › merc — estimation',
    'template' => 'pages/estimation/merc-estimation',
    'route_slug' => 'merci-estimation',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => 'Merci — Votre demande a bien été reçue',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Votre demande d\'estimation a été transmise. Eduardo Desul vous recontactera sous 24h.',
          ),
        ),
      ),
    ),
    'tier' => 'secondary',
  ),
  'pages-estimation-resultat' => 
  array (
    'label' => 'estimation › resultat',
    'template' => 'pages/estimation/resultat',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-estimation-tunnel' => 
  array (
    'label' => 'estimation › tunnel',
    'template' => 'pages/estimation/tunnel',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => 'Estimation immobilière gratuite — Bordeaux Métropole (33) | Eduardo Desul',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Obtenez une fourchette de prix en 60 secondes. Basée sur les références de marché bordelais. Sans inscription, sans engagement.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-financement-financement' => 
  array (
    'label' => 'financement › financement',
    'template' => 'pages/financement/financement',
    'route_slug' => 'financement',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => 'Financement immobilier à Bordeaux — Accompagnement personnalisé',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Demandez un accompagnement au financement immobilier à Bordeaux : projet clarifié, dossier simplifié, retour rapide et humain.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-guide-local-annuaire-ville' => 
  array (
    'label' => 'guide — local › annuaire — ville',
    'template' => 'pages/guide-local/annuaire-ville',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-guide-local-index' => 
  array (
    'label' => 'guide — local › index',
    'template' => 'pages/guide-local/index',
    'route_slug' => 'guides-locaux',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-guide-local-ville' => 
  array (
    'label' => 'guide — local › ville',
    'template' => 'pages/guide-local/ville',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-guides-guide-acheteur' => 
  array (
    'label' => 'guides › guide — acheteur',
    'template' => 'pages/guides/guide-acheteur',
    'route_slug' => 'guide-acheteur',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => 'Guide Complet Acheteur — Acheter votre bien immobilier à Bordeaux',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-guides-guide-vendeur' => 
  array (
    'label' => 'guides › guide — vendeur',
    'template' => 'pages/guides/guide-vendeur',
    'route_slug' => 'guide-vendeur',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => 'Guide Complet Vendeur — Vendre votre bien immobilier à Bordeaux',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-legal-cgv' => 
  array (
    'label' => 'legal › cgv',
    'template' => 'pages/legal/cgv',
    'route_slug' => 'cgv',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-legal-mentions-legales' => 
  array (
    'label' => 'legal › mentions — legales',
    'template' => 'pages/legal/mentions-legales',
    'route_slug' => 'mentions-legales',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-legal-plan-du-site' => 
  array (
    'label' => 'legal › plan — du — site',
    'template' => 'pages/legal/plan-du-site',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-legal-politique-confidentialite' => 
  array (
    'label' => 'legal › politique — confidentialite',
    'template' => 'pages/legal/politique-confidentialite',
    'route_slug' => 'politique-confidentialite',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Politique de confidentialité et protection des données personnelles.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-legal-politique-cookies' => 
  array (
    'label' => 'legal › politique — cookies',
    'template' => 'pages/legal/politique-cookies',
    'route_slug' => 'politique-cookies',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-plan-du-site' => 
  array (
    'label' => 'plan — du — site',
    'template' => 'pages/plan-du-site',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => 'Plan du site — Eduardo Desul Immobilier',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Navigation rapide vers toutes les pages principales du site.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-prendre-rendez-vous' => 
  array (
    'label' => 'prendre — rendez — vous',
    'template' => 'pages/prendre-rendez-vous',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => 'Prendre rendez-vous — Estimation affinée',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Demandez une estimation immobilière affinée avec un conseiller.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-ressources-guide' => 
  array (
    'label' => 'ressources › guide',
    'template' => 'pages/ressources/guide',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-ressources-guide-acheteur' => 
  array (
    'label' => 'ressources › guide — acheteur',
    'template' => 'pages/ressources/guide-acheteur',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Guide complet pour acheter votre premier bien immobilier à Bordeaux : financement, recherche, offre, signature.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-ressources-guide-vendeur' => 
  array (
    'label' => 'ressources › guide — vendeur',
    'template' => 'pages/ressources/guide-vendeur',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Guide complet pour vendre votre bien immobilier à Bordeaux : estimation, diagnostics, home staging, négociation.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-ressources-guides-data' => 
  array (
    'label' => 'ressources › guides — data',
    'template' => 'pages/ressources/guides-data',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-ressources-index' => 
  array (
    'label' => 'ressources › index',
    'template' => 'pages/ressources/index',
    'route_slug' => 'ressources',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Guides gratuits pour acheter ou vendre votre bien immobilier : guide vendeur, guide acheteur, check-lists, simulateurs.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-secteurs-index' => 
  array (
    'label' => 'secteurs › index',
    'template' => 'pages/secteurs/index',
    'route_slug' => 'secteurs',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-services-estimation' => 
  array (
    'label' => 'services › estimation',
    'template' => 'pages/services/estimation',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => 'Estimation gratuite — <?= ADVISOR_NAME ?> | Expert Immobilier 360° Bordeaux',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Estimez gratuitement votre bien immobilier à Bordeaux avec <?= ADVISOR_NAME ?>. Réponse personnalisée sous 48h.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-services-services' => 
  array (
    'label' => 'services › services',
    'template' => 'pages/services/services',
    'route_slug' => 'services',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-social-proof-avis' => 
  array (
    'label' => 'social — proof › avis',
    'template' => 'pages/social-proof/avis',
    'route_slug' => 'avis',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-_ville-secteur' => 
  array (
    'label' => 'zones › _ville — secteur',
    'template' => 'pages/zones/_ville-secteur',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-quartiers-bacalan' => 
  array (
    'label' => 'zones › quartiers › bacalan',
    'template' => 'pages/zones/quartiers/bacalan',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-quartiers-bastide' => 
  array (
    'label' => 'zones › quartiers › bastide',
    'template' => 'pages/zones/quartiers/bastide',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-quartiers-belcier' => 
  array (
    'label' => 'zones › quartiers › belcier',
    'template' => 'pages/zones/quartiers/belcier',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-quartiers-bordeaux-centre' => 
  array (
    'label' => 'zones › quartiers › bordeaux — centre',
    'template' => 'pages/zones/quartiers/bordeaux-centre',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-quartiers-capucins' => 
  array (
    'label' => 'zones › quartiers › capucins',
    'template' => 'pages/zones/quartiers/capucins',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-quartiers-cauderan' => 
  array (
    'label' => 'zones › quartiers › cauderan',
    'template' => 'pages/zones/quartiers/cauderan',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-quartiers-centre-ville' => 
  array (
    'label' => 'zones › quartiers › centre — ville',
    'template' => 'pages/zones/quartiers/centre-ville',
    'route_slug' => 'quartier/centre-ville',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Expert immobilier au centre-ville d\'Bordeaux. Appartements anciens, biens rénovés, commerces — <?= ADVISOR_NAME ?> vous accompagne pour votre projet en hyper-centre.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-quartiers-chartrons' => 
  array (
    'label' => 'zones › quartiers › chartrons',
    'template' => 'pages/zones/quartiers/chartrons',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-quartiers-jas-de-bouffan' => 
  array (
    'label' => 'zones › quartiers › jas — de — bouffan',
    'template' => 'pages/zones/quartiers/jas-de-bouffan',
    'route_slug' => 'quartier/jas-de-bouffan',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Expert immobilier dans le quartier Jas de Bouffan à Bordeaux. Quartier résidentiel familial avec maisons et appartements, proche des écoles et commerces.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-quartiers-les-milles' => 
  array (
    'label' => 'zones › quartiers › les — milles',
    'template' => 'pages/zones/quartiers/les-milles',
    'route_slug' => 'quartier/les-milles',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Expert immobilier aux Milles, secteur résidentiel et d\'activités au sud d\'Bordeaux. Appartements, maisons et locaux professionnels à proximité du technopôle.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-quartiers-luynes' => 
  array (
    'label' => 'zones › quartiers › luynes',
    'template' => 'pages/zones/quartiers/luynes',
    'route_slug' => 'quartier/luynes',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Expert immobilier à Luynes, quartier résidentiel à l\'ouest d\'Bordeaux. Maisons pavillonnaires, résidences et appartements dans un cadre calme et bien desservi.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-quartiers-mazarin' => 
  array (
    'label' => 'zones › quartiers › mazarin',
    'template' => 'pages/zones/quartiers/mazarin',
    'route_slug' => 'quartier/mazarin',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Expert immobilier dans le quartier Mazarin à Bordeaux. Le quartier le plus prestigieux d\'Aix — hôtels particuliers, appartements haussmanniens, rues arborées.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-quartiers-nansouty' => 
  array (
    'label' => 'zones › quartiers › nansouty',
    'template' => 'pages/zones/quartiers/nansouty',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-quartiers-puyricard' => 
  array (
    'label' => 'zones › quartiers › puyricard',
    'template' => 'pages/zones/quartiers/puyricard',
    'route_slug' => 'quartier/puyricard',
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Expert immobilier à Puyricard, quartier résidentiel premium au nord d\'Bordeaux. Villas, maisons avec piscine, environnement calme et verdoyant.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-quartiers-saint-augustin' => 
  array (
    'label' => 'zones › quartiers › saint — augustin',
    'template' => 'pages/zones/quartiers/saint-augustin',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-quartiers-saint-michel' => 
  array (
    'label' => 'zones › quartiers › saint — michel',
    'template' => 'pages/zones/quartiers/saint-michel',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-quartiers-saint-seurin' => 
  array (
    'label' => 'zones › quartiers › saint — seurin',
    'template' => 'pages/zones/quartiers/saint-seurin',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-villes-blanquefort' => 
  array (
    'label' => 'zones › villes › blanquefort',
    'template' => 'pages/zones/villes/blanquefort',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-villes-bordeaux' => 
  array (
    'label' => 'zones › villes › bordeaux',
    'template' => 'pages/zones/villes/bordeaux',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => 'Immobilier Bordeaux (33000) - Conseiller immobilier | Eduardo Desul',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Achetez, vendez ou estimez votre bien à Bordeaux avec Eduardo Desul, conseiller immobilier. Capitale de la Gironde, ville dynamique et attractive au cœur de la métropole.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-villes-bouliac' => 
  array (
    'label' => 'zones › villes › bouliac',
    'template' => 'pages/zones/villes/bouliac',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-villes-carbon-blanc' => 
  array (
    'label' => 'zones › villes › carbon — blanc',
    'template' => 'pages/zones/villes/carbon-blanc',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-villes-eysines' => 
  array (
    'label' => 'zones › villes › eysines',
    'template' => 'pages/zones/villes/eysines',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-villes-floirac' => 
  array (
    'label' => 'zones › villes › floirac',
    'template' => 'pages/zones/villes/floirac',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-villes-lormont' => 
  array (
    'label' => 'zones › villes › lormont',
    'template' => 'pages/zones/villes/lormont',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-villes-merignac' => 
  array (
    'label' => 'zones › villes › merignac',
    'template' => 'pages/zones/villes/merignac',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => 'Immobilier Mérignac (33700) - Conseiller immobilier | Eduardo Desul',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Achetez, vendez ou estimez votre bien à Mérignac avec Eduardo Desul, conseiller immobilier. 2ème ville de Gironde, dynamique et bien desservie, aux portes de Bordeaux.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-villes-pessac' => 
  array (
    'label' => 'zones › villes › pessac',
    'template' => 'pages/zones/villes/pessac',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-villes-saint-medard' => 
  array (
    'label' => 'zones › villes › saint — medard',
    'template' => 'pages/zones/villes/saint-medard',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-villes-talence' => 
  array (
    'label' => 'zones › villes › talence',
    'template' => 'pages/zones/villes/talence',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => 'Immobilier Talence (33400) - Conseiller immobilier | Eduardo Desul',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => 'Achetez, vendez ou estimez votre bien à Talence avec Eduardo Desul, conseiller immobilier. Ville estudiantine et résidentielle, aux portes de Bordeaux, prisée des familles et investisseurs.',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
  'pages-zones-villes-villenave-dornon' => 
  array (
    'label' => 'zones › villes › villenave — dornon',
    'template' => 'pages/zones/villes/villenave-dornon',
    'route_slug' => NULL,
    'sections' => 
    array (
      'seo' => 
      array (
        'title' => 'SEO & métadonnées',
        'fields' => 
        array (
          'page_title' => 
          array (
            'type' => 'text',
            'label' => 'Titre de la page (balise title)',
            'default' => '',
          ),
          'meta_description' => 
          array (
            'type' => 'textarea',
            'label' => 'Meta description',
            'default' => '',
          ),
        ),
      ),
    ),
    'tier' => 'primary',
  ),
);

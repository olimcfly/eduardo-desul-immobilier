<?php

declare(strict_types=1);

return [
    'advisor' => [
        'name' => trim((string) setting('advisor_fullname', ADVISOR_NAME)),
        'city' => trim((string) setting('city_name', 'Aix-en-Provence')),
        'zone' => trim((string) setting('advisor_zone', 'Pays d\'Aix')),
        'phone' => trim((string) setting('contact_phone', defined('APP_PHONE') ? APP_PHONE : '+33 6 67 19 83 66')),
        'email' => trim((string) setting('contact_email', defined('APP_EMAIL') ? APP_EMAIL : 'contact@pascalhamm.fr')),
    ],
    'locales' => [
        'fr' => [
            'slug' => '/fr/estimation-immobiliere-aix-en-provence',
            'html_lang' => 'fr',
            'og_locale' => 'fr_FR',
            'meta' => [
                'title' => 'Estimation immobilière Aix-en-Provence | Accompagnement local premium',
                'description' => 'Estimation immobilière à Aix-en-Provence avec accompagnement humain pour vendre ou acheter sereinement dans le Pays d\'Aix.',
            ],
            'hero' => [
                'label' => 'Estimation immobilière à Aix-en-Provence',
                'title' => 'Vendre ou acheter à Aix-en-Provence commence par une estimation juste.',
                'subtitle' => 'Un accompagnement local, transparent et humain pour prendre la bonne décision au bon moment.',
                'primaryCta' => 'Demander une estimation',
                'secondaryCta' => 'Prendre rendez-vous',
                'trust' => ['Conseiller local unique', 'Réponse sous 24h', 'Approche sans promesse irréaliste'],
            ],
            'motivation' => [
                'title' => 'Vous connaissez votre projet. Nous apportons la lecture du marché aixois.',
                'text' => 'Sur Aix-en-Provence, deux rues proches peuvent afficher des écarts de valeur importants. Sans repères locaux, il est facile de surévaluer, sous-évaluer ou rater une opportunité.',
            ],
            'positioning' => [
                'title' => 'Un conseiller de proximité pour un projet exigeant',
                'text' => '<?= ADVISOR_NAME ?> accompagne vendeurs, acheteurs et expatriés avec une méthode claire : données de marché, stratégie personnalisée et suivi continu.',
                'points' => [
                    'Connaissance terrain : Aix-en-Provence et Pays d\'Aix.',
                    'Interlocuteur unique, du premier échange à la signature.',
                    'Communication claire, sans jargon ni pression commerciale.',
                ],
            ],
            'services' => [
                'title' => 'Ce que vous obtenez concrètement',
                'items' => [
                    ['title' => 'Estimation fiable', 'text' => 'Avis de valeur argumenté à partir de comparables locaux.'],
                    ['title' => 'Accompagnement vente', 'text' => 'Positionnement, mise en marché, qualification des acheteurs.'],
                    ['title' => 'Accompagnement achat', 'text' => 'Sélection ciblée et sécurisation de l\'offre au bon prix.'],
                    ['title' => 'Stratégie locale', 'text' => 'Lecture fine des quartiers et de la dynamique du Pays d\'Aix.'],
                    ['title' => 'Fluidité internationale', 'text' => 'Cadre clair pour clients non résidents ou mobiles à l\'étranger.'],
                ],
            ],
            'method' => [
                'title' => 'Une méthode simple en 3 étapes',
                'steps' => [
                    ['title' => '1. Diagnostic', 'text' => 'Nous cadrons votre situation, vos délais et vos objectifs.'],
                    ['title' => '2. Recommandation', 'text' => 'Vous recevez une stratégie claire et un plan d\'action réaliste.'],
                    ['title' => '3. Exécution', 'text' => 'Nous pilotons les actions clés jusqu\'au rendez-vous ou à la mise en vente.'],
                ],
            ],
            'reassurance' => [
                'title' => 'Des signaux de confiance clairs',
                'testimonials' => [
                    '“Estimation précise et explications très pédagogiques. Nous avons vendu sereinement.” — Claire, Aix-en-Provence',
                    '“Excellent accompagnement en tant qu\'expatriés. Processus simple et humain.” — David & Emma, London',
                ],
                'faq' => [
                    ['q' => 'L\'estimation est-elle gratuite ?', 'a' => 'Oui, le premier échange et l\'avis de valeur sont sans engagement.'],
                    ['q' => 'Intervenez-vous hors centre-ville ?', 'a' => 'Oui, sur Aix-en-Provence et les communes principales du Pays d\'Aix.'],
                ],
            ],
            'finalCta' => [
                'title' => 'Parlons de votre projet immobilier à Aix-en-Provence',
                'text' => 'Obtenez une estimation claire et les prochaines étapes adaptées à votre situation.',
                'button' => 'Réserver un rendez-vous',
            ],
        ],
        'en' => [
            'slug' => '/en/property-valuation-aix-en-provence',
            'html_lang' => 'en',
            'og_locale' => 'en_US',
            'meta' => [
                'title' => 'Property valuation in Aix-en-Provence | Local guidance for international clients',
                'description' => 'Get a local property valuation in Aix-en-Provence with premium, human support for sellers, buyers and expatriates.',
            ],
            'hero' => [
                'label' => 'Aix-en-Provence Property Valuation',
                'title' => 'Make confident property decisions in Aix-en-Provence.',
                'subtitle' => 'Local market insight, clear communication and tailored support for international sellers and buyers.',
                'primaryCta' => 'Request a valuation',
                'secondaryCta' => 'Book a consultation',
                'trust' => ['One local advisor', '24h first response', 'Transparent, realistic advice'],
            ],
            'motivation' => [
                'title' => 'Knowing the city is not the same as reading its micro-markets.',
                'text' => 'In Aix-en-Provence, pricing can shift significantly between nearby streets and neighborhoods. International buyers and expats need local context to avoid costly mistakes.',
            ],
            'positioning' => [
                'title' => 'A local advisor with an international mindset',
                'text' => '<?= ADVISOR_NAME ?> provides structured guidance for sales and acquisitions, with straightforward communication and reliable local execution.',
                'points' => [
                    'On-the-ground knowledge of Aix-en-Provence and the Pays d\'Aix area.',
                    'Single point of contact from strategy to signature.',
                    'No inflated promises, only data-backed recommendations.',
                ],
            ],
            'services' => [
                'title' => 'What you can expect',
                'items' => [
                    ['title' => 'Accurate valuation', 'text' => 'A reasoned valuation based on local comparables.'],
                    ['title' => 'Sales support', 'text' => 'Positioning, launch strategy and buyer qualification.'],
                    ['title' => 'Buying support', 'text' => 'Targeted opportunities and offer strategy at the right level.'],
                    ['title' => 'Local strategy', 'text' => 'Neighborhood-level advice for better timing and decisions.'],
                    ['title' => 'International flow', 'text' => 'Smooth communication for remote or non-resident clients.'],
                ],
            ],
            'method' => [
                'title' => 'A clear 3-step process',
                'steps' => [
                    ['title' => '1. Brief', 'text' => 'We define your goals, timing and constraints.'],
                    ['title' => '2. Strategy', 'text' => 'You receive a realistic plan and valuation framework.'],
                    ['title' => '3. Action', 'text' => 'We move forward with the right next step: appointment, sale or acquisition.'],
                ],
            ],
            'reassurance' => [
                'title' => 'Reliable and human by design',
                'testimonials' => [
                    '“Clear valuation and excellent follow-up. We felt in control throughout.” — Olivia, Paris',
                    '“As overseas buyers, we needed clarity. The process was smooth and professional.” — Carlos & Ana, Madrid',
                ],
                'faq' => [
                    ['q' => 'Is the initial valuation free?', 'a' => 'Yes. The first consultation and initial valuation are free of charge.'],
                    ['q' => 'Do you support remote clients?', 'a' => 'Yes. We regularly assist international and expatriate clients remotely.'],
                ],
            ],
            'finalCta' => [
                'title' => 'Start with a local valuation you can trust',
                'text' => 'Get practical guidance for your next move in Aix-en-Provence.',
                'button' => 'Schedule a consultation',
            ],
        ],
        'es' => [
            'slug' => '/es/valoracion-inmobiliaria-aix-en-provence',
            'html_lang' => 'es',
            'og_locale' => 'es_ES',
            'meta' => [
                'title' => 'Valoración inmobiliaria en Aix-en-Provence | Acompañamiento local',
                'description' => 'Solicita una valoración inmobiliaria en Aix-en-Provence con asesoramiento cercano para vendedores, compradores y clientes internacionales.',
            ],
            'hero' => [
                'label' => 'Valoración inmobiliaria en Aix-en-Provence',
                'title' => 'Tu proyecto inmobiliario merece una valoración local y realista.',
                'subtitle' => 'Acompañamiento profesional y humano para vender o comprar con seguridad en Aix-en-Provence.',
                'primaryCta' => 'Solicitar valoración',
                'secondaryCta' => 'Pedir cita',
                'trust' => ['Asesor local único', 'Respuesta en 24h', 'Enfoque claro y honesto'],
            ],
            'motivation' => [
                'title' => 'Sin contexto local, es fácil perder valor o tiempo.',
                'text' => 'En Aix-en-Provence hay diferencias de precio importantes entre zonas muy cercanas. Para un comprador o vendedor internacional, la orientación local marca la diferencia.',
            ],
            'positioning' => [
                'title' => 'Asesoramiento cercano, criterio profesional',
                'text' => '<?= ADVISOR_NAME ?> acompaña operaciones de compraventa con una metodología clara, sin promesas exageradas y con seguimiento continuo.',
                'points' => [
                    'Conocimiento real del mercado de Aix-en-Provence y Pays d\'Aix.',
                    'Un solo interlocutor durante todo el proceso.',
                    'Comunicación fluida para clientes expatriados o internacionales.',
                ],
            ],
            'services' => [
                'title' => 'Servicios orientados a resultados',
                'items' => [
                    ['title' => 'Valoración precisa', 'text' => 'Estimación razonada basada en comparables locales.'],
                    ['title' => 'Acompañamiento en venta', 'text' => 'Estrategia de precio, salida al mercado y selección de compradores.'],
                    ['title' => 'Acompañamiento en compra', 'text' => 'Búsqueda enfocada y apoyo en negociación.'],
                    ['title' => 'Estrategia local', 'text' => 'Lectura por barrios para decidir mejor y con menos riesgo.'],
                    ['title' => 'Proceso internacional', 'text' => 'Coordinación simple para clientes a distancia.'],
                ],
            ],
            'method' => [
                'title' => 'Método en 3 pasos',
                'steps' => [
                    ['title' => '1. Diagnóstico', 'text' => 'Definimos objetivos, plazos y situación del proyecto.'],
                    ['title' => '2. Plan de acción', 'text' => 'Recibes una estrategia adaptada y una valoración coherente.'],
                    ['title' => '3. Acompañamiento', 'text' => 'Te guiamos hasta la siguiente acción clave con total claridad.'],
                ],
            ],
            'reassurance' => [
                'title' => 'Confianza para decidir con tranquilidad',
                'testimonials' => [
                    '“Muy profesional y cercano. La valoración fue clara y útil para vender bien.” — Laura, Aix-en-Provence',
                    '“Vivimos fuera de Francia y todo fue fácil de coordinar.” — Miguel y Sofía, Barcelona',
                ],
                'faq' => [
                    ['q' => '¿La valoración inicial tiene coste?', 'a' => 'No. La primera valoración y la reunión inicial son sin compromiso.'],
                    ['q' => '¿Trabaja con clientes internacionales?', 'a' => 'Sí, especialmente con compradores y vendedores no residentes.'],
                ],
            ],
            'finalCta' => [
                'title' => 'Da el primer paso con una valoración fiable',
                'text' => 'Hablemos de tu proyecto inmobiliario en Aix-en-Provence.',
                'button' => 'Reservar una cita',
            ],
        ],
    ],
];

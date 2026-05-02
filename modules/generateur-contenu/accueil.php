<?php

declare(strict_types=1);

$pageTitle = 'Générateur de contenu';
$pageDescription = 'Créez du contenu basé sur votre positionnement';

function renderContent(): void {
    ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        .generator-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .generator-header {
            background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
            border-radius: 16px;
            padding: 36px 40px;
            color: #fff;
            margin-bottom: 32px;
            box-shadow: 0 4px 20px rgba(15,34,55,.18);
        }

        .generator-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .generator-header p {
            font-size: 15px;
            color: rgba(255,255,255,.7);
            line-height: 1.65;
        }

        .generator-positioning-summary {
            background: #f8fbff;
            border: 2px solid #dbeafe;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
        }

        .generator-summary-title {
            font-size: 14px;
            font-weight: 700;
            color: #3b82f6;
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-bottom: 16px;
        }

        .generator-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .generator-summary-item {
            padding: 12px;
            background: #fff;
            border-radius: 8px;
            border-left: 3px solid #3b82f6;
        }

        .generator-summary-label {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 4px;
        }

        .generator-summary-value {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }

        .generator-content-types {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
            margin-bottom: 32px;
        }

        .generator-content-type {
            background: #fff;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
        }

        .generator-content-type:hover {
            border-color: #c9a84c;
            background: #fffaf0;
        }

        .generator-content-type.selected {
            border-color: #10b981;
            background: #f0fdf4;
        }

        .generator-content-type-icon {
            font-size: 32px;
            margin-bottom: 12px;
            display: block;
        }

        .generator-content-type-name {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .generator-content-type-desc {
            font-size: 12px;
            color: #64748b;
        }

        .generator-results {
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
            margin-top: 32px;
        }

        .generator-content-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 6px rgba(0,0,0,.07);
        }

        .generator-content-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .generator-content-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #dbeafe;
            color: #3b82f6;
            font-weight: 700;
            font-size: 14px;
        }

        .generator-content-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            flex: 1;
        }

        .generator-content-type-badge {
            display: inline-block;
            background: #f1f5f9;
            color: #64748b;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .generator-content-body {
            padding: 20px;
            font-size: 14px;
            color: #475569;
            line-height: 1.7;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .generator-content-footer {
            border-top: 1px solid #e2e8f0;
            padding: 12px 20px;
            display: flex;
            gap: 8px;
        }

        .generator-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .generator-btn-copy {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #dbe2ea;
            flex: 1;
        }

        .generator-btn-copy:hover {
            background: #eef2f7;
        }

        .generator-btn-copy.copied {
            background: #dcfce7;
            color: #166534;
            border-color: #86efac;
        }

        .generator-btn-edit {
            background: #3b82f6;
            color: #fff;
            border: none;
        }

        .generator-btn-edit:hover {
            background: #2563eb;
        }

        .generator-actions {
            display: flex;
            gap: 12px;
            margin-top: 32px;
            flex-wrap: wrap;
        }

        .generator-btn-primary {
            padding: 12px 24px;
            background: #10b981;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .generator-btn-primary:hover {
            background: #059669;
        }

        .generator-btn-secondary {
            padding: 12px 24px;
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #dbe2ea;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .generator-btn-secondary:hover {
            background: #eef2f7;
        }

        .generator-empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #f8fafc;
            border-radius: 12px;
            border: 2px dashed #e2e8f0;
        }

        .generator-empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.3;
        }

        .generator-empty-state-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .generator-empty-state-text {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
            max-width: 400px;
            margin: 0 auto;
        }

        .generator-loading {
            text-align: center;
            padding: 40px 20px;
        }

        .generator-spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid #e2e8f0;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .generator-networks {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .generator-network-card {
            background: #fff;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .generator-network-card:hover {
            border-color: #c9a84c;
        }

        .generator-network-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
        }

        .generator-network-name {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
        }

        .generator-network-badge {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: 4px 8px;
            border-radius: 6px;
        }

        .generator-network-badge--active {
            background: #dcfce7;
            color: #166534;
        }

        .generator-network-badge--passif {
            background: #e0e7ff;
            color: #3730a3;
        }

        .generator-network-desc {
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 14px;
        }

        .generator-ideas-list {
            list-style: none;
            margin: 0 0 16px 0;
            padding: 0;
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
        }

        .generator-ideas-list li {
            display: grid;
            grid-template-columns: 20px 1fr;
            gap: 8px;
            font-size: 12px;
            color: #334155;
            line-height: 1.45;
            margin-bottom: 10px;
        }

        .generator-ideas-n {
            font-weight: 800;
            color: #3b82f6;
        }

        .generator-network-generate {
            margin-top: auto;
        }

        @media (max-width: 600px) {
            .generator-header {
                padding: 24px 20px;
            }

            .generator-content-types {
                grid-template-columns: 1fr;
            }

            .generator-summary-grid {
                grid-template-columns: 1fr;
            }

            .generator-actions {
                flex-direction: column;
            }

            .generator-btn-primary,
            .generator-btn-secondary {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <div class="generator-container">
        <div class="generator-header">
            <h1>Générez votre contenu</h1>
            <p>5 idées d’angle par réseau (un niveau de conscience chacune), puis générez les 5 textes prêts à coller sur le réseau choisi. Répétez pour d’autres canaux si besoin.</p>
        </div>

        <div id="mainContent">
            <!-- Section résumé du positionnement -->
            <div class="generator-positioning-summary" id="summarySection" style="display: none;">
                <div class="generator-summary-title">Votre positionnement</div>
                <div class="generator-summary-grid">
                    <div class="generator-summary-item">
                        <div class="generator-summary-label">Persona / cible</div>
                        <div class="generator-summary-value" id="summaryPersona">-</div>
                    </div>
                    <div class="generator-summary-item">
                        <div class="generator-summary-label">Problème principal</div>
                        <div class="generator-summary-value" id="summaryProbleme">-</div>
                    </div>
                    <div class="generator-summary-item">
                        <div class="generator-summary-label">Objectif</div>
                        <div class="generator-summary-value" id="summaryObjectif">-</div>
                    </div>
                    <div class="generator-summary-item">
                        <div class="generator-summary-label">Urgence / confiance cible</div>
                        <div class="generator-summary-value" id="summaryConfiance">-</div>
                    </div>
                    <div class="generator-summary-item" style="grid-column: 1 / -1;">
                        <div class="generator-summary-label">Zone</div>
                        <div class="generator-summary-value" id="summaryZone">-</div>
                    </div>
                </div>
            </div>

            <div>
                <div style="margin-bottom: 8px;">
                    <h2 style="font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Tunnel : 1 idée (puis 1 post) par niveau de conscience</h2>
                    <p style="font-size: 14px; color: #64748b; line-height: 1.55;">
                        <strong>Google Business</strong> capte l’<strong>intention locale</strong> (recherche active). <strong>Facebook</strong> et <strong>LinkedIn</strong> travaillent plutôt en <strong>veille passive</strong> (fil d’actu) pour faire monter la prise de conscience. En pratique, concentrez souvent 1 à 2 canaux : les cartes rappellent l’ordre logique des 5 messages.
                    </p>
                </div>
                <div class="generator-networks" id="networkCards"></div>
            </div>

            <div style="margin-top: 28px; display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
                <a href="/admin?module=positionnement" class="generator-btn-secondary" style="text-decoration: none;">
                    <i class="fas fa-arrow-left"></i> Retour positionnement
                </a>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const personaLabels = {
            'jeune_couple': 'Couple jeune (primo-accédants)',
            'famille': 'Famille avec enfants',
            'investisseur': 'Investisseur',
            'celibataire': 'Personne seule',
            'retraite': 'Retraité',
            'entrepreneur': 'Entrepreneur',
            'parent_solo': 'Parent solo',
            'couple_sans_enfants': 'Résidence secondaire',
            'etranger': 'Nouveaux arrivants',
            'situation_difficile': 'Situation difficile / urgence'
        };

        const problemeLabels = {
            'pas_visites': 'Pas de visites',
            'prix_complique': 'Prix compliqué',
            'trop_concurrence': 'Trop de concurrence',
            'hesitent': 'Hésitation'
        };

        const objectifLabels = {
            'vendre_vite': 'Vendre vite',
            'meilleur_prix': 'Vendre au meilleur prix',
            'rassure': 'Être rassuré',
            'comprendre': 'Comprendre le marché'
        };

        const AWARENESS = [
            { n: 1, key: 'decouverte', name: 'Découverte' },
            { n: 2, key: 'probleme', name: 'Problème conscient' },
            { n: 3, key: 'solution', name: 'Recherche de solution' },
            { n: 4, key: 'comparaison', name: 'Comparaison' },
            { n: 5, key: 'intention', name: 'Intention locale' }
        ];

        const data = JSON.parse(sessionStorage.getItem('positionnement_data') || '{}');

        if (Object.keys(data).length === 0) {
            document.getElementById('mainContent').innerHTML = `
                <div class="generator-empty-state">
                    <div class="generator-empty-state-icon">⚠️</div>
                    <div class="generator-empty-state-title">Aucun positionnement trouvé</div>
                    <div class="generator-empty-state-text">
                        Veuillez d'abord compléter le module de positionnement.
                    </div>
                    <button class="generator-btn-secondary" onclick="window.location.href='/admin/?module=positionnement'" style="margin-top: 20px;">
                        <i class="fas fa-arrow-left"></i> Aller au positionnement
                    </button>
                </div>
            `;
            return;
        }

        const zone = (data.zone || 'votre secteur').trim();
        const persona = personaLabels[data.persona] || 'votre cible';
        const probleme = problemeLabels[data.probleme] || 'un frein sur le projet';
        const objectif = objectifLabels[data.objectif] || 'avancer sereinement';
        const pensee = (data.pensee || 'une inquiétude liée au marché').trim();
        const conf = data.confiance || 3;
        const confText = 'Niveau ' + conf + ' / 5 (urgence & confiance de la cible)';

        document.getElementById('summarySection').style.display = 'block';
        document.getElementById('summaryPersona').textContent = persona;
        document.getElementById('summaryProbleme').textContent = probleme;
        document.getElementById('summaryObjectif').textContent = objectif;
        document.getElementById('summaryConfiance').textContent = confText;
        document.getElementById('summaryZone').textContent = zone;

        function buildIdeasForNetwork(net) {
            const z = zone;
            const P = pensee;
            const out = {
                gmb: [
                    `Rappel local : 3 choses à connaître sur le marché de ${z} (sans jargon).`,
                    `Mini-FAQ : « On me dit X à propos de ${probleme} — est-ce que c’est vrai ici ? » (réponse courte).`,
                    `Post « preuve d’expertise » : ce que j’observe chez les ${persona} sur ${z}.`,
                    `Avis & fiabilité : pourquoi choisir un pro qui a déjà géré un cas proche (sans nommer).`,
                    `CTA clair : « Projet de vente à ${z} ? » + invitation à contacter (appel, message, rdv).`
                ],
                facebook: [
                    `Story de quartier : un détail concret de ${z} qui parle à ${persona} (pas une annonce de bien).`,
                    `Post émotion : « Vous avez l’impression que ${P} » + validation sans vendre.`,
                    `Carrousel simple : 4 signes que le blocage n’est pas que le prix, adapté à ${probleme}.`,
                    `Témoignage (format anonymisé) : retour d’une situation comparable sur ${z}.`,
                    `Sondage + commentaire : qu’est-ce qui bloque en priorité aujourd’hui (choix) — enchaîner en DM.`
                ],
                linkedin: [
                    `Mise en contexte pro : le marché immobilier de ${z} en une lecture utile (pas politique, pas buzz).`,
                    `Analyse courte : le vrai enjeu derrière ${probleme} pour un propriétaire.`,
                    `Méthode : 3 étapes qu’un vendeur de ${z} peut appliquer cette semaine (concret, modeste).`,
                    `Différenciation : mon cadre d’accompagnement (sans promesse irréaliste) + pourquoi la zone.`,
                    `Call to action pro : 15 min d’échange cadré, pour qui, comment prendre contact.`
                ]
            };
            return out[net] || out.facebook;
        }

        function buildContentsForNetwork(net) {
            const z = zone;
            const P = pensee;
            const gmb = [
                `【${AWARENESS[0].name} — fiche & posts locaux】\n\n🏠 Marché de ${z} : 3 idées retenues aujourd’hui\n— ce qui bouge, ce qui stagne, ce qui surprend\n— sans sensationnel : pour vous aider à cadrer votre projet\n\n#${z.replace(/\s+/g, '')} #Immobilier`,
                `【${AWARENESS[1].name} — Q&R courte】\n\nQuestion fréquente : « J’entends parler de ${P} — c’est vrai sur ${z} ? »\n\nRéalité : chaque situation est différente, mais on voit souvent le même schéma quand on parle de : ${probleme}.\n\n👉 Écrivez-moi le contexte (type de bien + timing), je réponds en message.`,
                `【${AWARENESS[2].name} — expertise locale】\n\nCôté ${z}, ce qu’on observe souvent quand l’objectif c’est de ${objectif} :\n1) cadrer le vrai sujet (pas seulement le visible)\n2) aligner présentation & attentes du marché\n3) ajuster le calendrier plutôt que de paniquer\n\nRéponse aux commentaires 24h ouvrées.`,
                `【${AWARENESS[3].name} — comparaison & preuve】\n\nVous comparez des conseillers ? Très sain. Voici 3 repères concrets (pas des promesses) :\n— process clair, sans jargon\n— suivi proactif, pas de radio silence\n— lecture locale (pas France entière) pour ${z}\n\nDemandez 15 min : on voit si la méthode colle à VOTRE situation.`,
                `【${AWARENESS[4].name} — intention & contact】\n\nProjet de vente à ${z} ?\n\nJe suis [nom], spécialisé auprès de profils type « ${persona} ». Si votre priorité est de ${objectif}, envoyez : « 1 type de bien + 1 calendrier ». Je rappelle rapidement.`
            ];
            const facebook = [
                `【${AWARENESS[0].name}】\n\nUn détail (vrai) sur ${z} aujourd’hui — parce qu’on parle souvent d’immo de façon abstraite.\n\nCe n’est pas un post pour vendre un bien, c’est pour aligner le regard sur le terrain. 💬\n\nSi ça parle, dites en com comment vous voyez le marché chez vous.`,
                `【${AWARENESS[1].name}】\n\n« ${P} »\n\nSi c’est un peu ce que vous vous dites en secret, c’est normal. On ne règle pas un projet immeuble comme une recette toute faite — surtout quand c’est ${probleme}.\n\n👉 Répondez en MP avec 1 phrase sur votre contexte, je vous dis ce que je ferais en premier (sans engagement).`,
                `【${AWARENESS[2].name}】\n\n3 pistes (simples) pour avancer, si vous ciblez ${objectif} sur ${z} :\n1) recadrer l’essentiel (le vrai sujet, pas l’inconfort autour)\n2) vérifier la cohérence offre / attentes (sans s’auto-culpabiliser)\n3) un plan sur 2–4 semaines, pas 6 mois de flou\n\nSauvegardez, ça sert quand l’impression s’envole.`,
                `【${AWARENESS[3].name}】\n\nAvant d’en choisir un, voici le genre de choses qu’un accompagnement sérieux doit assumer à ${z} : transparence, rythme, explications quand c’est flou, et jamais de sur-promesse sur le chiffre.\n\nC’est le genre de sujet qu’on voit en situation réelle, pas en slide LinkedIn. 😉`,
                `【${AWARENESS[4].name}】\n\nVous êtes prêt·e à passer un cap sur ${z} ?\n\n➡️ Dites « RAPPEL» en commentaire ou envoyez un message. Je ne réponds qu’à des demandes cadrées (projet, timing, type de profil) pour être utile, pas en spam.`
            ];
            const linkedin = [
                `【${AWARENESS[0].name}】\n\nMarché immo local (${z}) : ce n’est jamais « la même courbe » partout, même en intra-métropole.\n\nCôté pratique, je partage 2 lectures utiles côté propriétaire (sans fuite d’info client) : tendance d’implication des acheteurs, et l’importance d’un positionnement clair tôt plutôt que de « rattraper en urgence ».\n\n#Immobilier #${z.split(/\s+/)[0]}`,
                `【${AWARENESS[1].name}】\n\nUn motif récurrent côté vendeurs : ${probleme}.\n\nEn accompagnement, l’enjeu n’est presque jamais « un truc caché » : c’est souvent l’écart entre la narration du bien, la réalité du fil d’acheteur, et le calendrier. À ${z}, je commence par reposer ces trois points avant de proposer un plan.`,
                `【${AWARENESS[2].name}】\n\n3 actions concrètes quand l’objectif est clairement de ${objectif} :\n— reformuler l’essentiel en une page (bénéfice / contrainte / calendrier)\n— vérifier la cohérence canaux (visite, contenu, message)\n— mesure simple d’arbitrage (visibilité, pas vanity metrics)\n\nApplicables cette semaine, adaptées ${z}.`,
                `【${AWARENESS[3].name}】\n\nComparer des professionnels, c’est comparer un cadre de travail, pas seulement un taux. Ce que j’apporte, c’est de la clarté de process sur ${z} + une lecture terrain des profils cibles, en particulier quand on parle de ${persona}.\n\nLe contrat, c’est la pédagogie, pas l’injonction de vendre n’importe comment.`,
                `【${AWARENESS[4].name}】\n\nPrise de contact pro : sujet, zone (${z}), horizon de décision, et 1 seule question cadrant — « qu’est-ce qui crée le plus de doute aujourd’hui ? »\n\n→ Échange 15 min, sans pitch PowerPoint, pour voir s’il y a adéquation.`
            ];
            if (net === 'gmb') return gmb;
            if (net === 'linkedin') return linkedin;
            return facebook;
        }

        const networks = [
            {
                id: 'gmb',
                label: 'Google Business',
                sub: 'Recherche locale, avis, posts « fiche établissement » — fort sur l’intention (mode actif).',
                mode: 'active',
                modeLabel: 'Actif (intention locale)'
            },
            {
                id: 'facebook',
                label: 'Facebook',
                sub: 'Fil d’actu, groupes, commentaires — veille, réveil, social proof (mode passif).',
                mode: 'passif',
                modeLabel: 'Passif (veille & scroll)'
            },
            {
                id: 'linkedin',
                label: 'LinkedIn',
                sub: 'Crédibilité, expertise, réseau pro — trafic souvent moins “urgent” que Google (mode passif).',
                mode: 'passif',
                modeLabel: 'Passif (expertise & fil pro)'
            }
        ];

        const networkCards = document.getElementById('networkCards');
        networkCards.innerHTML = networks.map((n) => {
            const ideas = buildIdeasForNetwork(n.id);
            const ideasHtml = ideas.map((txt, i) => `
                <li>
                    <span class="generator-ideas-n">${i + 1}</span>
                    <span>${escapeHtml(txt)}</span>
                </li>
            `).join('');
            const badge = n.mode === 'active'
                ? 'generator-network-badge--active'
                : 'generator-network-badge--passif';
            return `
                <div class="generator-network-card" data-network="${n.id}">
                    <div class="generator-network-top">
                        <div>
                            <div class="generator-network-name">${n.label}</div>
                        </div>
                        <span class="generator-network-badge ${badge}">${n.modeLabel}</span>
                    </div>
                    <p class="generator-network-desc">${n.sub}</p>
                    <p style="font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">5 idées d’angle (1 par niveau de conscience)</p>
                    <ul class="generator-ideas-list">${ideasHtml}</ul>
                    <div class="generator-network-generate">
                        <button type="button" class="generator-btn-primary" style="width:100%;" data-generate="${n.id}">
                            <i class="fas fa-sparkles"></i> Générer les 5 contenus ${n.label}
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        networkCards.querySelectorAll('[data-generate]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-generate');
                runGenerate(id);
            });
        });

        function runGenerate(net) {
            const nlabel = networks.find((x) => x.id === net).label;
            document.getElementById('mainContent').innerHTML = `
                <div class="generator-loading">
                    <div class="generator-spinner"></div>
                    <p style="margin-top: 16px; color: #64748b;">Rédaction des 5 contenus — ${nlabel}…</p>
                </div>
            `;
            setTimeout(() => {
                const blocks = buildContentsForNetwork(net);
                const contents = blocks.map((content, i) => ({
                    number: i + 1,
                    label: AWARENESS[i].name,
                    type: nlabel,
                    content
                }));
                displayResults(contents, net);
            }, 1000);
        }

        function displayResults(contents, net) {
            const nlabel = networks.find((x) => x.id === net).label;
            document.getElementById('mainContent').innerHTML = `
                <div id="resultsSection" style="display: block;">
                    <div style="margin-bottom: 24px;">
                        <h2 style="font-size: 20px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">5 contenus — ${nlabel}</h2>
                        <p style="font-size: 14px; color: #64748b;">Un texte par niveau de conscience. Copiez-collez puis adaptez signature et mentions légales.</p>
                    </div>

                    <div class="generator-results" id="resultsContainer">
                        ${contents.map((c) => `
                            <div class="generator-content-card">
                                <div class="generator-content-header">
                                    <div class="generator-content-number">${c.number}</div>
                                    <div class="generator-content-title">Niveau : ${c.label}</div>
                                    <div class="generator-content-type-badge">${c.type}</div>
                                </div>
                                <div class="generator-content-body">${escapeHtml(c.content)}</div>
                                <div class="generator-content-footer">
                                    <button class="generator-btn generator-btn-copy" data-copy="${c.number}">
                                        <i class="fas fa-copy"></i> Copier
                                    </button>
                                </div>
                            </div>
                        `).join('')}
                    </div>

                    <div class="generator-actions" style="margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 32px;">
                        <button class="generator-btn-primary" id="dlAll">
                            <i class="fas fa-download"></i> Télécharger (ce réseau)
                        </button>
                        <button class="generator-btn-secondary" id="backNetworks">
                            <i class="fas fa-layer-group"></i> Autre réseau
                        </button>
                        <a href="/admin?module=positionnement" class="generator-btn-secondary" style="text-decoration: none;">
                            <i class="fas fa-arrow-left"></i> Positionnement
                        </a>
                    </div>
                </div>
            `;
            const bodyMap = {};
            contents.forEach((c) => { bodyMap[c.number] = c.content; });
            document.querySelectorAll('[data-copy]').forEach((b) => {
                b.addEventListener('click', function () {
                    const k = this.getAttribute('data-copy');
                    copyToClipboard(this, bodyMap[k]);
                });
            });
            document.getElementById('dlAll').addEventListener('click', () => {
                const text = contents.map((c) => `— ${c.label} —\n\n${c.content}`).join('\n\n==========\n\n');
                const element = document.createElement('a');
                element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
                element.setAttribute('download', 'contenus_' + net + '_' + zone.replace(/[^\w-]+/g, '_') + '.txt');
                document.body.appendChild(element);
                element.click();
                document.body.removeChild(element);
            });
            document.getElementById('backNetworks').addEventListener('click', () => {
                location.reload();
            });
        }

        function copyToClipboard(btn, text) {
            navigator.clipboard.writeText(text).then(() => {
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Copié !';
                btn.classList.add('copied');
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('copied');
                }, 2000);
            });
        }

        function escapeHtml(s) {
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }
    })();
    </script>
    <?php
}

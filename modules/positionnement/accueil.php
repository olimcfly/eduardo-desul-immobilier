<?php

declare(strict_types=1);

$pageTitle = 'Positionnement';
$pageDescription = 'Comprendre pourquoi vous n\'attirez pas de vendeurs';

function renderContent(): void {
    ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        .positioning-container {
            max-width: 700px;
            margin: 0 auto;
        }

        .positioning-header {
            background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
            border-radius: 16px;
            padding: 36px 40px;
            color: #fff;
            margin-bottom: 32px;
            box-shadow: 0 4px 20px rgba(15,34,55,.18);
        }

        .positioning-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
            line-height: 1.25;
        }

        .positioning-header p {
            font-size: 15px;
            color: rgba(255,255,255,.7);
            line-height: 1.65;
        }

        .positioning-progress {
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            margin-bottom: 32px;
            overflow: hidden;
        }

        .positioning-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6 0%, #10b981 100%);
            transition: width 0.3s ease;
        }

        .positioning-step {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .positioning-step.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .positioning-step-number {
            font-size: 12px;
            font-weight: 700;
            color: #8a95a3;
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-bottom: 16px;
        }

        .positioning-question {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .positioning-micro-text {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 24px;
            line-height: 1.6;
            font-style: italic;
        }

        .positioning-options {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 32px;
        }

        .positioning-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 32px;
        }

        .positioning-option {
            background: #fff;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 18px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 15px;
            color: #1e293b;
            text-align: left;
        }

        .positioning-option.grid {
            text-align: center;
            padding: 20px 16px;
        }

        .positioning-option:hover {
            border-color: #c9a84c;
            background: #fffaf0;
        }

        .positioning-option.selected {
            border-color: #3b82f6;
            background: #f8fbff;
            font-weight: 600;
        }

        .positioning-option-icon {
            font-size: 28px;
            margin-bottom: 8px;
            display: block;
        }

        .positioning-option-name {
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 2px;
        }

        .positioning-option-desc {
            font-size: 11px;
            color: #64748b;
        }

        .positioning-input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            font-family: inherit;
            margin-bottom: 32px;
            transition: border-color 0.2s ease;
        }

        .positioning-input:focus {
            outline: none;
            border-color: #3b82f6;
            background: #f8fbff;
        }

        .positioning-input::placeholder {
            color: #94a3b8;
        }

        .positioning-result {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .positioning-result.active {
            display: block;
        }

        .positioning-insight {
            background: #f8fbff;
            border-left: 4px solid #3b82f6;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .positioning-insight-title {
            font-size: 13px;
            font-weight: 700;
            color: #3b82f6;
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-bottom: 12px;
        }

        .positioning-insight-text {
            font-size: 15px;
            color: #1e293b;
            line-height: 1.7;
        }

        .positioning-message {
            background: #fffaf0;
            border-left: 4px solid #c9a84c;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
        }

        .positioning-message-title {
            font-size: 13px;
            font-weight: 700;
            color: #c9a84c;
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-bottom: 12px;
        }

        .positioning-message-text {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            line-height: 1.6;
        }

        .positioning-controls {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }

        .positioning-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .positioning-btn-primary {
            background: #0f2237;
            color: #fff;
            flex: 1;
        }

        .positioning-btn-primary:hover {
            background: #193757;
        }

        .positioning-btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .positioning-btn-secondary {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #dbe2ea;
        }

        .positioning-btn-secondary:hover {
            background: #eef2f7;
        }

        .positioning-level-meter {
            display: flex;
            gap: 8px;
            margin-top: 16px;
            margin-bottom: 32px;
        }

        .positioning-level {
            flex: 1;
            height: 8px;
            border-radius: 4px;
            background: #e2e8f0;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .positioning-level.active {
            background: linear-gradient(90deg, #3b82f6 0%, #10b981 100%);
        }

        .positioning-level-labels {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #64748b;
            margin-top: 8px;
        }

        .positioning-funnel-steps {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .positioning-funnel-step {
            display: grid;
            grid-template-columns: 22px 1fr auto;
            align-items: start;
            gap: 10px;
            background: #fff;
            border-radius: 8px;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
        }

        .positioning-funnel-n {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #0f2237;
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .positioning-funnel-step-body {
            min-width: 0;
        }

        .positioning-funnel-step-title {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }

        .positioning-funnel-step-sub {
            font-size: 12px;
            color: #64748b;
            line-height: 1.45;
            margin-top: 2px;
        }

        .positioning-funnel-chan {
            text-align: right;
        }

        .positioning-funnel-chan-name {
            font-size: 12px;
            font-weight: 700;
            color: #1e40af;
            white-space: nowrap;
        }

        .positioning-funnel-chan-mode {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #64748b;
            margin-top: 2px;
        }

        @media (max-width: 600px) {
            .positioning-header {
                padding: 24px 20px;
            }

            .positioning-question {
                font-size: 18px;
            }

            .positioning-controls {
                flex-direction: column-reverse;
            }

            .positioning-btn {
                width: 100%;
            }

            .positioning-grid-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="positioning-container">
        <div class="positioning-header">
            <h1>Comprendre votre positionnement</h1>
            <p>Répondez à 6 questions simples et découvrez pourquoi vous n'attirez pas les vendeurs que vous ciblez.</p>
        </div>

        <div class="positioning-progress">
            <div class="positioning-progress-bar" id="progressBar" style="width: 0%;"></div>
        </div>

        <form id="positioningForm">
            <!-- ÉTAPE 1: PERSONA -->
            <div class="positioning-step active" data-step="1">
                <div class="positioning-step-number">Étape 1 sur 6</div>
                <div class="positioning-question">Votre cible est-elle vendeuse ou acheteuse ?</div>
                <div class="positioning-micro-text">Commencez ici. Ensuite, tout le formulaire s'adapte automatiquement pour éviter toute hésitation.</div>
                <div class="positioning-grid-2" style="margin-bottom:16px;">
                    <button type="button" class="positioning-option grid" data-field="profile" data-value="vendeur">
                        <span class="positioning-option-icon">🏠</span>
                        <div class="positioning-option-name">Vendeur</div>
                        <div class="positioning-option-desc">Projet de vente</div>
                    </button>
                    <button type="button" class="positioning-option grid" data-field="profile" data-value="acheteur">
                        <span class="positioning-option-icon">🔑</span>
                        <div class="positioning-option-name">Acheteur</div>
                        <div class="positioning-option-desc">Projet d'achat</div>
                    </button>
                </div>
                <div class="positioning-micro-text" style="margin-bottom:16px;">Puis choisissez le type de client :</div>
                <div class="positioning-grid-2">
                    <button type="button" class="positioning-option grid persona-option" data-profile="acheteur" data-field="persona" data-value="jeune_couple">
                        <span class="positioning-option-icon">👨‍👩‍👧</span>
                        <div class="positioning-option-name">Couple jeune</div>
                        <div class="positioning-option-desc">Primo-accédants</div>
                    </button>
                    <button type="button" class="positioning-option grid persona-option" data-profile="vendeur" data-field="persona" data-value="famille">
                        <span class="positioning-option-icon">👨‍👩‍👧‍👦</span>
                        <div class="positioning-option-name">Famille</div>
                        <div class="positioning-option-desc">Naissance, bien trop petit</div>
                    </button>
                    <button type="button" class="positioning-option grid persona-option" data-profile="both" data-field="persona" data-value="investisseur">
                        <span class="positioning-option-icon">💰</span>
                        <div class="positioning-option-name">Investisseur</div>
                        <div class="positioning-option-desc">Rentabilité</div>
                    </button>
                    <button type="button" class="positioning-option grid persona-option" data-profile="acheteur" data-field="persona" data-value="celibataire">
                        <span class="positioning-option-icon">👤</span>
                        <div class="positioning-option-name">Personne seule</div>
                        <div class="positioning-option-desc">Premier achat</div>
                    </button>
                    <button type="button" class="positioning-option grid persona-option" data-profile="both" data-field="persona" data-value="retraite">
                        <span class="positioning-option-icon">🏖️</span>
                        <div class="positioning-option-name">Retraité</div>
                        <div class="positioning-option-desc">Nouveau projet de vie</div>
                    </button>
                    <button type="button" class="positioning-option grid persona-option" data-profile="both" data-field="persona" data-value="entrepreneur">
                        <span class="positioning-option-icon">💼</span>
                        <div class="positioning-option-name">Entrepreneur</div>
                        <div class="positioning-option-desc">Bureau + habitation</div>
                    </button>
                    <button type="button" class="positioning-option grid persona-option" data-profile="both" data-field="persona" data-value="parent_solo">
                        <span class="positioning-option-icon">👨‍👧</span>
                        <div class="positioning-option-name">Parent solo</div>
                        <div class="positioning-option-desc">Projet prioritaire</div>
                    </button>
                    <button type="button" class="positioning-option grid persona-option" data-profile="both" data-field="persona" data-value="couple_sans_enfants">
                        <span class="positioning-option-icon">👨‍❤️‍👨</span>
                        <div class="positioning-option-name">Résidence secondaire</div>
                        <div class="positioning-option-desc">Résidence secondaire</div>
                    </button>
                    <button type="button" class="positioning-option grid persona-option" data-profile="both" data-field="persona" data-value="etranger">
                        <span class="positioning-option-icon">🌍</span>
                        <div class="positioning-option-name">Étranger</div>
                        <div class="positioning-option-desc">Nouveau arrivant</div>
                    </button>
                    <button type="button" class="positioning-option grid persona-option" data-profile="vendeur" data-field="persona" data-value="situation_difficile">
                        <span class="positioning-option-icon">⚠️</span>
                        <div class="positioning-option-name">Situation difficile</div>
                        <div class="positioning-option-desc">Urgence</div>
                    </button>
                </div>
            </div>

            <!-- ÉTAPE 2: URGENCE / CONFIANCE -->
            <div class="positioning-step" data-step="2">
                <div class="positioning-step-number">Étape 2 sur 6</div>
                <div class="positioning-question">Quel est le niveau d'urgence / de confiance du/des <span class="audience-word">vendeurs</span> ?</div>
                <div class="positioning-micro-text">De "pas du tout prêt" à "décidé et confiant".</div>
                <div class="positioning-level-meter" id="levelMeter">
                    <div class="positioning-level" data-level="1" title="Très peu confiant"></div>
                    <div class="positioning-level" data-level="2" title="Peu confiant"></div>
                    <div class="positioning-level" data-level="3" title="Neutre"></div>
                    <div class="positioning-level" data-level="4" title="Confiant"></div>
                    <div class="positioning-level" data-level="5" title="Très confiant"></div>
                </div>
                <div class="positioning-level-labels">
                    <span>Très peu confiant</span>
                    <span>Très confiant</span>
                </div>
            </div>

            <!-- ÉTAPE 3: PROBLÈME -->
            <div class="positioning-step" data-step="3">
                <div class="positioning-step-number">Étape 3 sur 6</div>
                <div class="positioning-question">Quel est le principal blocage du/des <span class="audience-word">vendeurs</span> ?</div>
                <div class="positioning-options">
                    <button type="button" class="positioning-option" data-field="probleme" data-value="pas_visites">
                        Pas de visites
                    </button>
                    <button type="button" class="positioning-option" data-field="probleme" data-value="prix_complique">
                        Difficultés de prix
                    </button>
                    <button type="button" class="positioning-option" data-field="probleme" data-value="trop_concurrence">
                        Trop de concurrence
                    </button>
                    <button type="button" class="positioning-option" data-field="probleme" data-value="hesitent">
                        Hésitation, doute
                    </button>
                </div>
            </div>

            <!-- ÉTAPE 4: PENSÉE -->
            <div class="positioning-step" data-step="4">
                <div class="positioning-step-number">Étape 4 sur 6</div>
                <div class="positioning-question">Qu'est-ce que se dit en ce moment le(s) <span class="audience-word">vendeur(s)</span> ?</div>
                <div class="positioning-micro-text">Écrivez exactement sa préoccupation principale.</div>
                <input type="text" class="positioning-input" id="pensee" placeholder="Exemple: Le marché est compliqué en ce moment" required>
            </div>

            <!-- ÉTAPE 5: OBJECTIF -->
            <div class="positioning-step" data-step="5">
                <div class="positioning-step-number">Étape 5 sur 6</div>
                <div class="positioning-question">Qu'est-ce que veut vraiment le/la <span class="audience-word">vendeur(se)</span> ?</div>
                <div class="positioning-options">
                    <button type="button" class="positioning-option" data-field="objectif" data-value="vendre_vite">
                        Vendre vite
                    </button>
                    <button type="button" class="positioning-option" data-field="objectif" data-value="meilleur_prix">
                        Vendre au meilleur prix
                    </button>
                    <button type="button" class="positioning-option" data-field="objectif" data-value="rassure">
                        Être rassuré(e)
                    </button>
                    <button type="button" class="positioning-option" data-field="objectif" data-value="comprendre">
                        Comprendre le marché
                    </button>
                </div>
            </div>

            <!-- ÉTAPE 6: ZONE -->
            <div class="positioning-step" data-step="6">
                <div class="positioning-step-number">Étape 6 sur 6</div>
                <div class="positioning-question">Sur quelle zone géographique ?</div>
                <input type="text" class="positioning-input" id="zone" placeholder="Votre ville ou secteur" required>
            </div>

            <!-- RÉSULTAT -->
            <div class="positioning-result" id="resultSection">
                <div class="positioning-insight">
                    <div class="positioning-insight-title">Votre prise de conscience</div>
                    <div class="positioning-insight-text" id="insightText"></div>
                </div>

                <div class="positioning-message">
                    <div class="positioning-message-title">Votre message principal</div>
                    <div class="positioning-message-text" id="messageText"></div>
                </div>

                <div class="positioning-funnel-block" style="background: #fefce8; border: 1px solid #fde047; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <div style="font-size: 13px; font-weight: 700; color: #a16207; text-transform: uppercase; letter-spacing: .07em; margin-bottom: 10px;">Tunnel : 1 publication par niveau de conscience</div>
                    <p style="font-size: 13px; color: #713f12; line-height: 1.6; margin-bottom: 16px;">Chaque étape du parcours d’achat ne réagit pas sur le même canal : en découverte, la veille se fait plutôt en mode <strong>passif</strong> (scroll Facebook, fil LinkedIn). Quand l’intention est forte, le mode <strong>actif</strong> (recherche Google, fiche Google Business) prend le relais.</p>
                    <div id="funnelHint" style="font-size: 13px; color: #0f172a; background: #fff; border-radius: 8px; padding: 12px; margin-bottom: 14px; border: 1px solid #e2e8f0;"></div>
                    <div class="positioning-funnel-steps" id="funnelSteps"></div>
                </div>

                <div style="background: #f0f9ff; border-left: 4px solid #10b981; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                    <div style="font-size: 13px; font-weight: 700; color: #10b981; text-transform: uppercase; letter-spacing: .07em; margin-bottom: 8px;">Prochaine étape</div>
                    <div style="font-size: 14px; color: #1e293b; line-height: 1.6;">Dans le générateur, vous verrez <strong>5 idées d’angle par réseau</strong> (Google Business, Facebook, LinkedIn). Cliquez le réseau choisi pour générer les <strong>5 contenus</strong> alignés sur le tunnel (un par niveau de conscience).</div>
                </div>
            </div>

            <!-- CONTRÔLES -->
            <div class="positioning-controls" id="controls">
                <button type="button" class="positioning-btn positioning-btn-secondary" id="prevBtn" style="display: none;">
                    <i class="fas fa-chevron-left"></i> Précédent
                </button>
                <button type="button" class="positioning-btn positioning-btn-primary" id="nextBtn">
                    Suivant <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <div class="positioning-controls" id="resultControls" style="display: none; margin-top: 32px;">
                <a href="/admin?module=generateur-contenu" class="positioning-btn positioning-btn-primary" style="text-decoration: none; width: 100%;">
                    <i class="fas fa-sparkles"></i> Générer 5 contenus avec ce positionnement
                </a>
            </div>
        </form>
    </div>

    <script>
    (function () {
        const form = document.getElementById('positioningForm');
        const steps = document.querySelectorAll('.positioning-step');
        const resultSection = document.getElementById('resultSection');
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        const controls = document.getElementById('controls');
        const resultControls = document.getElementById('resultControls');
        const progressBar = document.getElementById('progressBar');

        let currentStep = 1;
        const data = { profile: 'vendeur' };

        const personaLabels = {
            'jeune_couple': 'Couple jeune (primo-accédants)',
            'famille': 'Famille avec enfants',
            'investisseur': 'Investisseur immobilier',
            'celibataire': 'Personne seule',
            'retraite': 'Retraité',
            'entrepreneur': 'Entrepreneur',
            'parent_solo': 'Parent solo',
            'couple_sans_enfants': 'Couple sans enfants',
            'etranger': 'Étranger',
            'situation_difficile': 'Situation difficile'
        };

        function updateAudienceWord(profile) {
            const map = {
                vendeur: {
                    plural: 'vendeurs',
                    singular: 'vendeur(se)',
                    singularAlt: 'vendeur(s)',
                },
                acheteur: {
                    plural: 'acheteurs',
                    singular: 'acheteur(se)',
                    singularAlt: 'acheteur(s)',
                }
            };
            const audience = map[profile] || map.vendeur;
            document.querySelectorAll('.audience-word').forEach((el) => {
                if (el.textContent.includes('(s)')) {
                    el.textContent = audience.singularAlt;
                } else if (el.textContent.includes('se)')) {
                    el.textContent = audience.singular;
                } else {
                    el.textContent = audience.plural;
                }
            });
        }

        function updatePersonaVisibility(profile) {
            document.querySelectorAll('.persona-option').forEach((option) => {
                const optionProfile = option.dataset.profile || 'both';
                const visible = optionProfile === 'both' || optionProfile === profile;
                option.style.display = visible ? '' : 'none';
                if (!visible) {
                    option.classList.remove('selected');
                    if (data.persona === option.dataset.value) {
                        delete data.persona;
                    }
                }
            });
        }

        function updateProgress() {
            const progress = (currentStep / 6) * 100;
            progressBar.style.width = progress + '%';
        }

        function showStep(step) {
            steps.forEach(s => s.classList.remove('active'));
            document.querySelector(`[data-step="${step}"]`).classList.add('active');

            prevBtn.style.display = step > 1 ? 'block' : 'none';
            nextBtn.textContent = step === 6 ? 'Voir le résultat' : 'Suivant';
            nextBtn.innerHTML = step === 6 ? '<i class="fas fa-arrow-right"></i> Voir le résultat' : 'Suivant <i class="fas fa-chevron-right"></i>';

            updateProgress();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function canProceed() {
            if (currentStep === 1 && (!data.profile || !data.persona)) return false;
            if (currentStep === 4 && !document.getElementById('pensee').value.trim()) return false;
            if (currentStep === 6 && !document.getElementById('zone').value.trim()) return false;

            const selectedOption = document.querySelector(`[data-step="${currentStep}"] .positioning-option.selected`);
            const selectedLevel = document.querySelector('.positioning-level.active');

            if (currentStep === 1 && (!data.profile || !data.persona)) return false;
            if (currentStep === 2 && !selectedLevel) return false;
            if (currentStep === 3 && !selectedOption) return false;
            if (currentStep === 5 && !selectedOption) return false;

            return true;
        }

        function generateResult() {
            const problemeLabels = {
                'pas_visites': 'pas de visites',
                'prix_complique': 'difficultés de prix',
                'trop_concurrence': 'concurrence',
                'hesitent': 'hésitation'
            };

            const objectifLabels = {
                'vendre_vite': 'vendre vite',
                'meilleur_prix': 'vendre au meilleur prix',
                'rassure': 'être rassuré',
                'comprendre': 'comprendre le marché'
            };

            const persona = personaLabels[data.persona] || 'un vendeur';
            const probleme = problemeLabels[data.probleme] || 'un problème';
            const pensee = data.pensee || 'une crainte';
            const objectif = objectifLabels[data.objectif] || 'un objectif';
            const zone = data.zone || 'votre zone';
            const confiance = data.confiance || 3;

            const insight = `Vous parlez à tous les vendeurs de ${zone}. Mais le ${persona} qui rencontre ${probleme} ne se reconnaît pas dans votre message. Surtout quand il / elle pense: "${pensee}".`;

            const messages = {
                'jeune_couple': 'Vous hésitez à vous lancer ? C\'est normal. Parlons de vos vrais doutes.',
                'famille': 'Agrandir sa maison, c\'est une décision importante. Nous vous l\'expliquons.',
                'investisseur': 'Investir c\'est une question de rentabilité. Montrez-moi vos objectifs.',
                'celibataire': 'Acheter seul(e) n\'est pas plus compliqué. Démonstration.',
                'retraite': 'Réduire votre surface sans vous perdre. C\'est possible.',
                'entrepreneur': 'Bureau + habitation. Un seul bien peut les réunir.',
                'parent_solo': 'Vous méritez un bien qui vous ressemble vraiment.',
                'couple_sans_enfants': 'La 2e résidence, c\'est un luxe ou un investissement ?',
                'etranger': 'Vous découvrez notre marché. Laissez-nous vous le rendre simple.',
                'situation_difficile': 'Vous avez besoin de solutions rapides. Nous les avons.'
            };

            const message = messages[data.persona] || 'Parlons de ce qui vous préoccupe vraiment.';

            document.getElementById('insightText').textContent = insight;
            document.getElementById('messageText').textContent = message;

            const tunnel = [
                { n: 1, title: 'Découverte', sub: 'Elle ne sait pas encore formuler le besoin : réveil, contexte, tendances locales.', ch: 'Facebook', mode: 'Passif' },
                { n: 2, title: 'Problème conscient', sub: 'Elle reconnait la friction (visites, prix, doute) sans avoir choisi d’outil ni d’expert.', ch: 'Facebook', mode: 'Passif' },
                { n: 3, title: 'Recherche de solution', sub: 'Elle cherche des méthodes, repères, preuves : positionnez-vous comme spécialiste crédible.', ch: 'LinkedIn', mode: 'Passif' },
                { n: 4, title: 'Comparaison', sub: 'Elle compare des professionnels, lit les avis, cherche un pro de sa zone.', ch: 'Google Business', mode: 'Actif' },
                { n: 5, title: 'Intention locale forte', sub: 'Elle veut l’appel, le message, le rendez-vous : bénéficie des requêtes « près de moi ».', ch: 'Google Business', mode: 'Actif' }
            ];

            const conf = data.confiance || 3;
            const hint = document.getElementById('funnelHint');
            if (hint) {
                if (conf <= 2) {
                    hint.innerHTML = '<strong>Selon l’urgence / confiance indiqué(e) :</strong> priorisez d’abord <strong>Facebook</strong> (niveaux 1 et 2), puis <strong>LinkedIn</strong> pour cadrer l’expertise, et gardez <strong>Google Business</strong> pour capter dès qu’il y a de l’intention locale.';
                } else if (conf === 3) {
                    hint.innerHTML = '<strong>Profil intermédiaire :</strong> alternez <strong>Facebook</strong> + <strong>LinkedIn</strong> pour nourrir, et <strong>Google Business</strong> pour convertir (niveaux 4–5). Vous pouvez ne rien lâcher sur 1 ou 2 canaux seulement à la suite.';
                } else {
                    hint.innerHTML = '<strong>Profil plutôt mûr :</strong> poussez vite les contenus d’<strong>intention</strong> sur <strong>Google Business</strong> (niveaux 4–5), et utilisez <strong>LinkedIn</strong> / <strong>Facebook</strong> en relais d’autorité ou retargeting.';
                }
            }

            const fs = document.getElementById('funnelSteps');
            if (fs) {
                fs.innerHTML = tunnel.map((row) => `
                    <div class="positioning-funnel-step">
                        <div class="positioning-funnel-n">${row.n}</div>
                        <div class="positioning-funnel-step-body">
                            <div class="positioning-funnel-step-title">${row.title}</div>
                            <div class="positioning-funnel-step-sub">${row.sub}</div>
                        </div>
                        <div class="positioning-funnel-chan">
                            <div class="positioning-funnel-chan-name">${row.ch}</div>
                            <div class="positioning-funnel-chan-mode">${row.mode}</div>
                        </div>
                    </div>
                `).join('');
            }

            sessionStorage.setItem('positionnement_data', JSON.stringify(data));
        }

        // Clic sur les options
        document.querySelectorAll('.positioning-option').forEach(option => {
            option.addEventListener('click', (e) => {
                e.preventDefault();
                const field = option.dataset.field;
                const value = option.dataset.value;

                if (field === 'profile') {
                    document.querySelectorAll(`[data-field="${field}"]`).forEach(o => {
                        o.classList.remove('selected');
                    });
                    option.classList.add('selected');
                    data[field] = value;
                    updateAudienceWord(value);
                    updatePersonaVisibility(value);
                    return;
                }

                document.querySelectorAll(`[data-field="${field}"]`).forEach(o => {
                    o.classList.remove('selected');
                });
                option.classList.add('selected');
                data[field] = value;
            });
        });

        // Clic sur les niveaux de confiance
        document.querySelectorAll('.positioning-level').forEach(level => {
            level.addEventListener('click', (e) => {
                e.preventDefault();
                const levelValue = level.dataset.level;

                document.querySelectorAll('.positioning-level').forEach(l => {
                    l.classList.remove('active');
                });

                for (let i = 1; i <= levelValue; i++) {
                    document.querySelector(`.positioning-level[data-level="${i}"]`).classList.add('active');
                }

                data.confiance = parseInt(levelValue);
            });
        });

        // Bouton Suivant
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();

            if (!canProceed()) {
                alert('Veuillez remplir cette question pour continuer.');
                return;
            }

            if (currentStep === 6) {
                data.pensee = document.getElementById('pensee').value;
                data.zone = document.getElementById('zone').value;
                generateResult();
                steps.forEach(s => s.classList.remove('active'));
                resultSection.classList.add('active');
                controls.style.display = 'none';
                resultControls.style.display = 'flex';
                updateProgress();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                if (currentStep === 4) data.pensee = document.getElementById('pensee').value;
                currentStep++;
                showStep(currentStep);
            }
        });

        // Bouton Précédent
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            currentStep--;
            showStep(currentStep);
        });

        updateAudienceWord(data.profile);
        updatePersonaVisibility(data.profile);
        const defaultProfileBtn = document.querySelector('[data-field="profile"][data-value="vendeur"]');
        if (defaultProfileBtn) {
            defaultProfileBtn.classList.add('selected');
        }
        showStep(1);
    })();
    </script>
    <?php
}

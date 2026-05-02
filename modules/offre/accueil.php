<?php

declare(strict_types=1);

$pageTitle = 'Offre';
$pageDescription = 'Clarifier ce que vous proposez réellement';

function renderContent(): void {
    ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        .offre-container {
            max-width: 700px;
            margin: 0 auto;
        }

        .offre-header {
            background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
            border-radius: 16px;
            padding: 36px 40px;
            color: #fff;
            margin-bottom: 32px;
            box-shadow: 0 4px 20px rgba(15,34,55,.18);
        }

        .offre-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
            line-height: 1.25;
        }

        .offre-header p {
            font-size: 15px;
            color: rgba(255,255,255,.7);
            line-height: 1.65;
        }

        .offre-progress {
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            margin-bottom: 32px;
            overflow: hidden;
        }

        .offre-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6 0%, #10b981 100%);
            transition: width 0.3s ease;
        }

        .offre-step {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .offre-step.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .offre-step-number {
            font-size: 12px;
            font-weight: 700;
            color: #8a95a3;
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-bottom: 16px;
        }

        .offre-question {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .offre-micro-text {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 24px;
            line-height: 1.6;
            font-style: italic;
        }

        .offre-options {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 32px;
        }

        .offre-option {
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

        .offre-option:hover {
            border-color: #c9a84c;
            background: #fffaf0;
        }

        .offre-option.selected {
            border-color: #3b82f6;
            background: #f8fbff;
            font-weight: 600;
        }

        .offre-input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            font-family: inherit;
            margin-bottom: 32px;
            transition: border-color 0.2s ease;
        }

        .offre-input:focus {
            outline: none;
            border-color: #3b82f6;
            background: #f8fbff;
        }

        .offre-input::placeholder {
            color: #94a3b8;
        }

        .offre-result {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .offre-result.active {
            display: block;
        }

        .offre-insight {
            background: #f8fbff;
            border-left: 4px solid #3b82f6;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .offre-insight-title {
            font-size: 13px;
            font-weight: 700;
            color: #3b82f6;
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-bottom: 12px;
        }

        .offre-insight-text {
            font-size: 15px;
            color: #1e293b;
            line-height: 1.7;
        }

        .offre-message {
            background: #fffaf0;
            border-left: 4px solid #c9a84c;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .offre-message-label {
            font-size: 13px;
            font-weight: 700;
            color: #c9a84c;
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-bottom: 12px;
        }

        .offre-message-title {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .offre-message-short {
            background: #f0f9ff;
            border-left: 4px solid #10b981;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .offre-message-short-label {
            font-size: 13px;
            font-weight: 700;
            color: #10b981;
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-bottom: 8px;
        }

        .offre-message-short-text {
            font-size: 15px;
            color: #1e293b;
            line-height: 1.6;
        }

        .offre-controls {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }

        .offre-btn {
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

        .offre-btn-primary {
            background: #0f2237;
            color: #fff;
            flex: 1;
        }

        .offre-btn-primary:hover {
            background: #193757;
        }

        .offre-btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .offre-btn-secondary {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #dbe2ea;
        }

        .offre-btn-secondary:hover {
            background: #eef2f7;
        }

        @media (max-width: 600px) {
            .offre-header {
                padding: 24px 20px;
            }

            .offre-question {
                font-size: 18px;
            }

            .offre-controls {
                flex-direction: column-reverse;
            }

            .offre-btn {
                width: 100%;
            }
        }
    </style>

    <div class="offre-container">
        <div class="offre-header">
            <h1>Clarifier votre offre</h1>
            <p>Répondez à 4 questions et découvrez la phrase percutante que vous pouvez utiliser immédiatement.</p>
        </div>

        <div class="offre-progress">
            <div class="offre-progress-bar" id="progressBar" style="width: 0%;"></div>
        </div>

        <form id="offreForm">
            <!-- ÉTAPE 1: SITUATION -->
            <div class="offre-step active" data-step="1">
                <div class="offre-step-number">Étape 1 sur 4</div>
                <div class="offre-question">Dans quelle situation vous aidez vos vendeurs ?</div>
                <div class="offre-micro-text">Choisissez celle qui correspond le mieux à votre principal défi.</div>
                <div class="offre-options">
                    <button type="button" class="offre-option" data-field="situation" data-value="bien_sans_resultat">
                        Bien en vente sans résultat
                    </button>
                    <button type="button" class="offre-option" data-field="situation" data-value="vente_bloquee">
                        Vente bloquée
                    </button>
                    <button type="button" class="offre-option" data-field="situation" data-value="vendre_rapidement">
                        Besoin de vendre rapidement
                    </button>
                    <button type="button" class="offre-option" data-field="situation" data-value="premiere_vente">
                        Première vente
                    </button>
                </div>
            </div>

            <!-- ÉTAPE 2: PROBLÈME -->
            <div class="offre-step" data-step="2">
                <div class="offre-step-number">Étape 2 sur 4</div>
                <div class="offre-question">Quel problème vous résolvez concrètement ?</div>
                <div class="offre-micro-text">Le vrai problème, pas la conséquence.</div>
                <div class="offre-options">
                    <button type="button" class="offre-option" data-field="probleme" data-value="pas_visites">
                        Pas de visites
                    </button>
                    <button type="button" class="offre-option" data-field="probleme" data-value="prix_mal_positionne">
                        Prix mal positionné
                    </button>
                    <button type="button" class="offre-option" data-field="probleme" data-value="manque_clarte">
                        Manque de clarté
                    </button>
                    <button type="button" class="offre-option" data-field="probleme" data-value="vente_trop_longue">
                        Vente trop longue
                    </button>
                </div>
            </div>

            <!-- ÉTAPE 3: DIFFÉRENCE -->
            <div class="offre-step" data-step="3">
                <div class="offre-step-number">Étape 3 sur 4</div>
                <div class="offre-question">Qu'est-ce que vous faites différemment ?</div>
                <div class="offre-micro-text">Votre méthode, votre approche unique.</div>
                <div class="offre-options">
                    <button type="button" class="offre-option" data-field="difference" data-value="analyse_prix">
                        Analyse précise du prix
                    </button>
                    <button type="button" class="offre-option" data-field="difference" data-value="strategie_marche">
                        Stratégie de mise en marché
                    </button>
                    <button type="button" class="offre-option" data-field="difference" data-value="accompagnement">
                        Accompagnement étape par étape
                    </button>
                    <button type="button" class="offre-option" data-field="difference" data-value="methode_rapide">
                        Méthode pour vendre plus rapidement
                    </button>
                </div>
            </div>

            <!-- ÉTAPE 4: RÉSULTAT -->
            <div class="offre-step" data-step="4">
                <div class="offre-step-number">Étape 4 sur 4</div>
                <div class="offre-question">Quel résultat vous apportez ?</div>
                <div class="offre-micro-text">Le bénéfice concret pour le vendeur.</div>
                <div class="offre-options">
                    <button type="button" class="offre-option" data-field="resultat" data-value="vendre_vite">
                        Vendre plus vite
                    </button>
                    <button type="button" class="offre-option" data-field="resultat" data-value="bon_prix">
                        Vendre au bon prix
                    </button>
                    <button type="button" class="offre-option" data-field="resultat" data-value="rassurant">
                        Être rassuré
                    </button>
                    <button type="button" class="offre-option" data-field="resultat" data-value="eviter_erreurs">
                        Éviter les erreurs
                    </button>
                </div>
            </div>

            <!-- RÉSULTAT -->
            <div class="offre-result" id="resultSection">
                <div class="offre-insight">
                    <div class="offre-insight-title">Votre promesse en une phrase</div>
                    <div class="offre-insight-text" id="insightText"></div>
                </div>

                <div class="offre-message">
                    <div class="offre-message-label">Version longue</div>
                    <div class="offre-message-title" id="messageText"></div>
                </div>

                <div class="offre-message-short">
                    <div class="offre-message-short-label">Version courte pour les contacts</div>
                    <div class="offre-message-short-text" id="shortText"></div>
                </div>

                <div style="background: #f0f9ff; border-left: 4px solid #10b981; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                    <div style="font-size: 13px; font-weight: 700; color: #10b981; text-transform: uppercase; letter-spacing: .07em; margin-bottom: 8px;">Prochaine étape</div>
                    <div style="font-size: 14px; color: #1e293b; line-height: 1.6;">Utilisez cette offre pour créer du contenu et attirer les vendeurs qui ont ce problème.</div>
                </div>
            </div>

            <!-- CONTRÔLES -->
            <div class="offre-controls" id="controls">
                <button type="button" class="offre-btn offre-btn-secondary" id="prevBtn" style="display: none;">
                    <i class="fas fa-chevron-left"></i> Précédent
                </button>
                <button type="button" class="offre-btn offre-btn-primary" id="nextBtn">
                    Suivant <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <div class="offre-controls" id="resultControls" style="display: none; margin-top: 32px;">
                <a href="/admin?module=generateur-contenu" class="offre-btn offre-btn-primary" style="text-decoration: none; width: 100%;">
                    <i class="fas fa-sparkles"></i> Créer du contenu avec cette offre
                </a>
            </div>
        </form>
    </div>

    <script>
        const form = document.getElementById('offreForm');
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        const progressBar = document.getElementById('progressBar');
        const controls = document.getElementById('controls');
        const resultControls = document.getElementById('resultControls');
        const resultSection = document.getElementById('resultSection');

        const totalSteps = 4;
        let currentStep = 1;
        let formData = {};

        // Templates pour générer les messages
        const situationLabels = {
            bien_sans_resultat: 'bien ne se vend pas',
            vente_bloquee: 'vente est bloquée',
            vendre_rapidement: 'vendeur a besoin de vendre rapidement',
            premiere_vente: 'vendeur en est à sa première vente'
        };

        const problemeLabels = {
            pas_visites: 'générer des visites',
            prix_mal_positionne: 'repositionner le prix',
            manque_clarte: 'clarifier l\'offre',
            vente_trop_longue: 'accélérer la vente'
        };

        const differenceLabels = {
            analyse_prix: 'je fais une analyse précise du prix',
            strategie_marche: 'je mets en place une stratégie de mise en marché ciblée',
            accompagnement: 'j\'accompagne étape par étape',
            methode_rapide: 'j\'utilise une méthode éprouvée pour vendre plus rapidement'
        };

        const resultatLabels = {
            vendre_vite: 'vendre plus vite',
            bon_prix: 'vendre au bon prix',
            rassurant: 'être rassuré et confiant',
            eviter_erreurs: 'éviter les erreurs coûteuses'
        };

        // Event listeners pour les options
        const options = form.querySelectorAll('.offre-option');
        options.forEach(option => {
            option.addEventListener('click', (e) => {
                e.preventDefault();
                const field = option.dataset.field;
                const value = option.dataset.value;

                // Désélectionner les autres dans ce groupe
                document.querySelectorAll(`[data-field="${field}"]`).forEach(opt => {
                    opt.classList.remove('selected');
                });

                // Sélectionner celui-ci
                option.classList.add('selected');
                formData[field] = value;
                updateNextButton();
            });
        });

        // Navigation
        function updateProgress() {
            const percentage = (currentStep - 1) / (totalSteps - 1) * 100;
            progressBar.style.width = percentage + '%';
        }

        function showStep(step) {
            document.querySelectorAll('.offre-step').forEach(el => {
                el.classList.remove('active');
            });
            document.querySelector(`.offre-step[data-step="${step}"]`).classList.add('active');

            prevBtn.style.display = step === 1 ? 'none' : 'flex';
            nextBtn.style.display = 'flex';

            if (step < totalSteps) {
                nextBtn.innerHTML = 'Suivant <i class="fas fa-chevron-right"></i>';
            } else {
                nextBtn.innerHTML = 'Voir ma phrase <i class="fas fa-check"></i>';
            }

            updateProgress();
            updateNextButton();
        }

        nextBtn.addEventListener('click', () => {
            if (!isStepValid()) {
                return;
            }
            if (currentStep < totalSteps) {
                currentStep++;
                showStep(currentStep);
            } else {
                showResults();
            }
        });

        prevBtn.addEventListener('click', () => {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        });

        // Générer les résultats
        function generateMessage() {
            const situation = situationLabels[formData.situation] || '';
            const probleme = problemeLabels[formData.probleme] || '';
            const difference = differenceLabels[formData.difference] || '';
            const resultat = resultatLabels[formData.resultat] || '';

            // Message long
            const longMessage = `J'aide les vendeurs dont le ${situation} à ${probleme}. ${difference}. Résultat : ${resultat}.`;

            // Message court - plus percutant
            let shortMessage = '';
            if (formData.situation === 'bien_sans_resultat') {
                shortMessage = `Votre bien ne se vend pas ? Il y a une raison. Et souvent, ce n'est pas celle que vous pensez.`;
            } else if (formData.situation === 'vente_bloquee') {
                shortMessage = `La vente est bloquée ? Je sais exactement comment débloquer ça.`;
            } else if (formData.situation === 'vendre_rapidement') {
                shortMessage = `Vous devez vendre rapidement ? C'est justement mon domaine.`;
            } else if (formData.situation === 'premiere_vente') {
                shortMessage = `C'est votre première vente ? Je vous guide pas à pas pour éviter les pièges.`;
            }

            return {
                long: longMessage,
                short: shortMessage,
                insight: `Vous aidez les vendeurs en situation de ${situation} en leur permettant de ${resultat.toLowerCase()}.`
            };
        }

        function showResults() {
            const messages = generateMessage();

            document.getElementById('insightText').textContent = messages.insight;
            document.getElementById('messageText').textContent = messages.long;
            document.getElementById('shortText').textContent = messages.short;

            controls.style.display = 'none';
            resultControls.style.display = 'flex';
            resultSection.classList.add('active');

            // Scroll vers le résultat
            resultSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Vérifier la complétion du formulaire
        function isStepValid() {
            const currentFields = {
                1: 'situation',
                2: 'probleme',
                3: 'difference',
                4: 'resultat'
            };
            return formData[currentFields[currentStep]] !== undefined;
        }

        // Désactiver le bouton suivant si l'étape n'est pas complète
        function updateNextButton() {
            nextBtn.disabled = !isStepValid();
        }

        // Au chargement, vérifier et mettre à jour
        updateProgress();
        updateNextButton();
    </script>
    <?php
}

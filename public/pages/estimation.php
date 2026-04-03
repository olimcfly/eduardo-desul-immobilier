<?php
$pageTitle = 'Estimation gratuite — Eduardo Desul Immobilier';
$metaDesc  = 'Estimez gratuitement votre bien immobilier à Bordeaux avec Eduardo Desul. Réponse personnalisée sous 48h.';
$extraCss  = ['/assets/css/estimation.css'];
$extraJs   = ['/assets/js/estimation.js'];
?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb" aria-label="Fil d'Ariane">
            <a href="/">Accueil</a><span>Estimation</span>
        </nav>
        <h1>Estimation gratuite</h1>
        <p>Obtenez une évaluation précise de votre bien en quelques minutes. Entièrement gratuit et sans engagement.</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="estimation-layout">

            <!-- Formulaire multi-étapes -->
            <div class="estimation-form">
                <h2>Évaluez votre bien</h2>
                <p class="lead">Remplissez ce formulaire pour recevoir une estimation personnalisée.</p>

                <!-- Stepper -->
                <div class="stepper">
                    <div class="step active"><span class="step-label">Votre bien</span></div>
                    <div class="step"><span class="step-label">Caractéristiques</span></div>
                    <div class="step"><span class="step-label">Contact</span></div>
                </div>

                <form id="estimation-form" action="/estimation-gratuite" method="POST" novalidate>
                    <?= csrfField() ?>
                    <input type="hidden" id="type-bien" name="type_bien" value="">

                    <!-- Étape 1 : Type de bien -->
                    <div class="estimation-step">
                        <h3 style="margin-bottom:1.25rem">Quel type de bien souhaitez-vous estimer ?</h3>
                        <div class="type-buttons">
                            <button type="button" class="type-btn" data-value="appartement" onclick="document.getElementById('type-bien').value=this.dataset.value; document.querySelectorAll('.type-btn').forEach(b=>b.classList.remove('selected')); this.classList.add('selected')">
                                <span>🏢</span>Appartement
                            </button>
                            <button type="button" class="type-btn" data-value="maison" onclick="document.getElementById('type-bien').value=this.dataset.value; document.querySelectorAll('.type-btn').forEach(b=>b.classList.remove('selected')); this.classList.add('selected')">
                                <span>🏠</span>Maison
                            </button>
                            <button type="button" class="type-btn" data-value="terrain" onclick="document.getElementById('type-bien').value=this.dataset.value; document.querySelectorAll('.type-btn').forEach(b=>b.classList.remove('selected')); this.classList.add('selected')">
                                <span>🌿</span>Terrain
                            </button>
                            <button type="button" class="type-btn" data-value="local" onclick="document.getElementById('type-bien').value=this.dataset.value; document.querySelectorAll('.type-btn').forEach(b=>b.classList.remove('selected')); this.classList.add('selected')">
                                <span>🏪</span>Local comm.
                            </button>
                            <button type="button" class="type-btn" data-value="immeuble" onclick="document.getElementById('type-bien').value=this.dataset.value; document.querySelectorAll('.type-btn').forEach(b=>b.classList.remove('selected')); this.classList.add('selected')">
                                <span>🏗️</span>Immeuble
                            </button>
                            <button type="button" class="type-btn" data-value="autre" onclick="document.getElementById('type-bien').value=this.dataset.value; document.querySelectorAll('.type-btn').forEach(b=>b.classList.remove('selected')); this.classList.add('selected')">
                                <span>📦</span>Autre
                            </button>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="adresse">Adresse du bien <span>*</span></label>
                            <input type="text" id="adresse" name="adresse" class="form-control" placeholder="12 rue des Chartrons, Bordeaux" required autocomplete="street-address">
                        </div>
                        <div style="text-align:right">
                            <button type="button" class="btn btn--primary next-btn">Continuer →</button>
                        </div>
                    </div>

                    <!-- Étape 2 : Caractéristiques -->
                    <div class="estimation-step" hidden>
                        <h3 style="margin-bottom:1.5rem">Caractéristiques du bien</h3>

                        <div class="range-group">
                            <div class="range-display">
                                <span>Surface habitable</span>
                                <span class="range-val" id="surface-val">80 m²</span>
                            </div>
                            <input type="range" id="surface" name="surface" min="10" max="500" value="80" data-format="surface" aria-valuetext="80 m²">
                        </div>

                        <div class="range-group">
                            <div class="range-display">
                                <span>Nombre de pièces</span>
                                <span class="range-val" id="pieces-val">3</span>
                            </div>
                            <input type="range" id="pieces" name="pieces" min="1" max="15" value="3" data-format="count" aria-valuetext="3">
                        </div>

                        <div class="form-row" style="margin-top:1.5rem">
                            <div class="form-group">
                                <label class="form-label">Étage</label>
                                <select name="etage" class="form-control">
                                    <option>Rez-de-chaussée</option>
                                    <option>1er</option>
                                    <option>2ème</option>
                                    <option>3ème</option>
                                    <option>4ème et +</option>
                                    <option>Dernier étage</option>
                                    <option>Non applicable</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Année de construction</label>
                                <select name="annee_construction" class="form-control">
                                    <option>Avant 1950</option>
                                    <option>1950–1970</option>
                                    <option>1971–1990</option>
                                    <option>1991–2010</option>
                                    <option>Après 2010</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <fieldset>
                                <legend class="form-label">État général</legend>
                                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.5rem">
                                    <?php foreach (['À rénover', 'Passable', 'Bon état', 'Neuf / Refait'] as $etat): ?>
                                    <label style="display:flex;flex-direction:column;align-items:center;gap:.4rem;padding:.75rem;border:1.5px solid var(--clr-border);border-radius:var(--radius);cursor:pointer;font-size:.8rem;font-weight:500;transition:var(--transition)">
                                        <input type="radio" name="etat" value="<?= e($etat) ?>" style="accent-color:var(--clr-primary)">
                                        <?= e($etat) ?>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </fieldset>
                        </div>

                        <div style="display:flex;justify-content:space-between;margin-top:1rem">
                            <button type="button" class="btn btn--outline prev-btn">← Retour</button>
                            <button type="button" class="btn btn--primary next-btn">Continuer →</button>
                        </div>
                    </div>

                    <!-- Étape 3 : Contact -->
                    <div class="estimation-step" hidden>
                        <h3 style="margin-bottom:1.5rem">Vos coordonnées</h3>
                        <p style="color:var(--clr-text-muted);margin-bottom:1.5rem">Pour recevoir votre estimation personnalisée par email.</p>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="est-prenom">Prénom <span>*</span></label>
                                <input type="text" id="est-prenom" name="prenom" class="form-control" required autocomplete="given-name">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="est-nom">Nom <span>*</span></label>
                                <input type="text" id="est-nom" name="nom" class="form-control" required autocomplete="family-name">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="est-email">Email <span>*</span></label>
                            <input type="email" id="est-email" name="email" class="form-control" required autocomplete="email">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="est-tel">Téléphone</label>
                            <input type="tel" id="est-tel" name="telephone" class="form-control" autocomplete="tel">
                            <div class="form-hint">Pour un échange direct avec Eduardo.</div>
                        </div>
                        <div class="form-group">
                            <label style="display:flex;gap:.6rem;align-items:flex-start;font-size:.85rem;cursor:pointer">
                                <input type="checkbox" name="demande_rdv" value="1" style="margin-top:.2rem;flex-shrink:0">
                                <span>Je souhaite un rendez-vous après réception de mon estimation.</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="est-creneau">Créneau préféré (optionnel)</label>
                            <select id="est-creneau" name="creneau_prefere" class="form-control">
                                <option value="">— Sélectionner —</option>
                                <option value="matin">Matin (9h - 12h)</option>
                                <option value="midi">Midi (12h - 14h)</option>
                                <option value="apres-midi">Après-midi (14h - 18h)</option>
                                <option value="soir">Soir (18h - 20h)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label style="display:flex;gap:.6rem;align-items:flex-start;font-size:.85rem;cursor:pointer">
                                <input type="checkbox" name="rgpd" required style="margin-top:.2rem;flex-shrink:0">
                                <span>J'accepte la <a href="/politique-confidentialite" target="_blank" style="color:var(--clr-primary)">politique de confidentialité</a>. <span style="color:var(--clr-danger)">*</span></span>
                            </label>
                        </div>

                        <div style="display:flex;justify-content:space-between;margin-top:1rem">
                            <button type="button" class="btn btn--outline prev-btn">← Retour</button>
                            <button type="submit" class="btn btn--accent btn--lg">Recevoir mon estimation ✓</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Sidebar -->
            <div class="estimation-sidebar">
                <div class="why-estimate">
                    <h3>Pourquoi estimer avec moi ?</h3>
                    <div class="why-item">
                        <span class="why-icon">🎯</span>
                        <div class="why-text">
                            <strong>Précision garantie</strong>
                            <p>Analyse basée sur les transactions réelles du marché bordelais.</p>
                        </div>
                    </div>
                    <div class="why-item">
                        <span class="why-icon">⚡</span>
                        <div class="why-text">
                            <strong>Réponse sous 48h</strong>
                            <p>Un rapport personnalisé dans votre boîte mail rapidement.</p>
                        </div>
                    </div>
                    <div class="why-item">
                        <span class="why-icon">🔒</span>
                        <div class="why-text">
                            <strong>Gratuit & sans engagement</strong>
                            <p>Aucune obligation de vendre, aucune surprise.</p>
                        </div>
                    </div>
                    <div class="why-item">
                        <span class="why-icon">🤝</span>
                        <div class="why-text">
                            <strong>Accompagnement humain</strong>
                            <p>Eduardo vous recontacte personnellement pour affiner l'estimation.</p>
                        </div>
                    </div>
                </div>

                <div class="advisor-box">
                    <div class="advisor-avatar">👤</div>
                    <h4><?= e(ADVISOR_NAME) ?></h4>
                    <div class="role">Conseiller immobilier indépendant</div>
                    <p style="font-size:.85rem;color:var(--clr-text-muted);margin-bottom:1rem">Plus de 15 ans d'expérience sur le marché bordelais. 200+ transactions réussies.</p>
                    <?php if (APP_PHONE): ?>
                    <a href="tel:<?= e(preg_replace('/\s+/', '', APP_PHONE)) ?>" class="btn btn--outline btn--sm btn--full">📞 <?= e(APP_PHONE) ?></a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</section>

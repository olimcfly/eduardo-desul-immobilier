<?php
$pageTitle  = 'Estimation gratuite de votre bien — Eduardo Desul';
$metaDesc   = 'Estimez gratuitement votre bien immobilier à Bordeaux. Résultat personnalisé sous 48h par Eduardo Desul.';
$metaRobots = 'noindex';
$extraCss   = ['/assets/css/estimation.css'];
$extraJs    = ['/assets/js/estimation.js'];
$bodyClass  = 'page-capture';
?>
<section style="min-height:calc(100vh - var(--header-h));display:flex;align-items:center;padding-block:3rem;background:linear-gradient(135deg,var(--clr-primary) 0%,#0f2644 100%)">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 480px;gap:3rem;align-items:center">
            <div style="color:white">
                <span class="section-label" style="color:var(--clr-accent)">100% gratuit • Sans engagement</span>
                <h1 style="color:white;font-size:clamp(1.75rem,4vw,3rem);margin-bottom:1rem">Quelle est la valeur<br>de votre bien ?</h1>
                <p style="opacity:.85;font-size:1.1rem;margin-bottom:2rem">Obtenez une estimation personnalisée de votre bien à Bordeaux, basée sur les données réelles du marché.</p>
                <div style="display:flex;flex-direction:column;gap:.75rem">
                    <?php foreach (['Estimation basée sur les transactions récentes','Rapport détaillé envoyé sous 48h','Eduardo vous rappelle pour affiner l\'estimation','Gratuit, sans engagement, sans spam'] as $item): ?>
                    <div style="display:flex;gap:.75rem;align-items:center;font-size:.9rem">
                        <span style="color:var(--clr-accent);font-weight:700;font-size:1.1rem">✓</span>
                        <?= e($item) ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div style="display:flex;align-items:center;gap:1rem;margin-top:2.5rem;padding-top:2rem;border-top:1px solid rgba(255,255,255,.2)">
                    <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:1.75rem;border:2px solid var(--clr-accent)">👤</div>
                    <div>
                        <div style="font-weight:600"><?= e(ADVISOR_NAME) ?></div>
                        <div style="font-size:.8rem;opacity:.7">Conseiller immobilier indépendant · Bordeaux</div>
                    </div>
                </div>
            </div>

            <div style="background:white;border-radius:var(--radius-xl);padding:2.5rem;box-shadow:var(--shadow-lg)">
                <h2 style="margin-bottom:.5rem;font-size:1.5rem">Estimez mon bien</h2>
                <p style="color:var(--clr-text-muted);font-size:.9rem;margin-bottom:1.75rem">Remplissez ce formulaire — réponse sous 48h.</p>

                <form action="/estimation-gratuite" method="POST">
                    <?= csrfField() ?>
                    <div class="form-group">
                        <label class="form-label">Type de bien <span>*</span></label>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.5rem;margin-bottom:.5rem">
                            <?php foreach (['🏢 Appartement' => 'appartement', '🏠 Maison' => 'maison', '🌿 Terrain' => 'terrain'] as $label => $val): ?>
                            <label style="display:flex;flex-direction:column;align-items:center;gap:.25rem;padding:.75rem .5rem;border:1.5px solid var(--clr-border);border-radius:var(--radius);cursor:pointer;font-size:.8rem;font-weight:500;text-align:center;transition:var(--transition)">
                                <input type="radio" name="type_bien" value="<?= e($val) ?>" style="display:none">
                                <?= $label ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="cap-adresse">Adresse du bien <span>*</span></label>
                        <input type="text" id="cap-adresse" name="adresse" class="form-control" placeholder="12 rue des Chartrons, Bordeaux" required>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                        <div class="form-group">
                            <label class="form-label" for="cap-surface">Surface (m²)</label>
                            <input type="number" id="cap-surface" name="surface" class="form-control" placeholder="75" min="1" max="2000">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="cap-pieces">Nb de pièces</label>
                            <select id="cap-pieces" name="pieces" class="form-control">
                                <option value="">—</option>
                                <?php for ($i = 1; $i <= 10; $i++): ?><option><?= $i ?></option><?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="cap-prenom">Prénom <span>*</span></label>
                        <input type="text" id="cap-prenom" name="prenom" class="form-control" required autocomplete="given-name">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="cap-email">Email <span>*</span></label>
                        <input type="email" id="cap-email" name="email" class="form-control" required autocomplete="email">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="cap-tel">Téléphone</label>
                        <input type="tel" id="cap-tel" name="telephone" class="form-control" autocomplete="tel">
                    </div>
                    <div class="form-group">
                        <label style="display:flex;gap:.5rem;align-items:flex-start;font-size:.82rem;cursor:pointer">
                            <input type="checkbox" name="rgpd" required style="margin-top:.2rem;flex-shrink:0">
                            <span>J'accepte la <a href="/politique-confidentialite" target="_blank" style="color:var(--clr-primary)">politique de confidentialité</a>. <span style="color:var(--clr-danger)">*</span></span>
                        </label>
                    </div>
                    <button type="submit" class="btn btn--accent btn--lg btn--full" style="margin-top:.5rem">
                        Recevoir mon estimation gratuitement →
                    </button>
                    <p style="text-align:center;font-size:.78rem;color:var(--clr-text-muted);margin-top:.75rem">🔒 Données 100% confidentielles — Aucun spam</p>
                </form>
            </div>
        </div>
    </div>
</section>

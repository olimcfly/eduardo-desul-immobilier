<?php include __DIR__ . '/_noah_styles.php'; ?>

<div style="--tool-color:#e67e22">

    <a href="?module=construire" class="noah-back">
        <i class="fas fa-arrow-left"></i> Retour à Construire
    </a>

    <div class="noah-tool-header">
        <div class="noah-tool-icon" style="background:#fef5e7; color:#e67e22">
            <i class="fas fa-layer-group"></i>
        </div>
        <div>
            <h2 class="noah-tool-title">Synthèse Stratégique</h2>
            <p class="noah-tool-sub">Résumez votre situation et votre stratégie en 100 mots percutants</p>
        </div>
        <span class="noah-tool-badge">Noah IA</span>
    </div>

    <div class="noah-form-card">
        <form id="form-synthese">
            <input type="hidden" name="tool" value="synthese">
            <div class="noah-form-grid">
                <div class="noah-field">
                    <label class="noah-label">Votre activité</label>
                    <input class="noah-input" type="text" name="activite" placeholder="ex : agent indépendant depuis 2 ans" required>
                </div>
                <div class="noah-field">
                    <label class="noah-label">Positionnement</label>
                    <input class="noah-input" type="text" name="positionnement" placeholder="ex : spécialiste maisons familiales" required>
                </div>
                <div class="noah-field">
                    <label class="noah-label">Persona principal</label>
                    <input class="noah-input" type="text" name="persona" placeholder="ex : familles avec enfants" required>
                </div>
                <div class="noah-field">
                    <label class="noah-label">Votre offre</label>
                    <input class="noah-input" type="text" name="offre" placeholder="ex : accompagnement complet vendeur" required>
                </div>
                <div class="noah-field full">
                    <label class="noah-label">Zone géographique</label>
                    <input class="noah-input" type="text" name="zone" placeholder="ex : Mérignac, Pessac, Talence" required>
                </div>
            </div>
            <button class="noah-submit" type="submit">
                <i class="fas fa-wand-magic-sparkles"></i> Générer ma synthèse avec Noah
            </button>
            <div class="noah-result-box">
                <div class="noah-result-label"><i class="fas fa-sparkles"></i> Résultat Noah IA</div>
                <div class="noah-result-content"></div>
            </div>
            <div class="noah-error-box"></div>
        </form>
    </div>

</div>
<script>initNoahForm('form-synthese', '#e67e22');</script>

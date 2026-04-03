<?php include __DIR__ . '/_noah_styles.php'; ?>

<div style="--tool-color:#27ae60">

    <a href="?module=construire" class="noah-back">
        <i class="fas fa-arrow-left"></i> Retour à Construire
    </a>

    <div class="noah-tool-header">
        <div class="noah-tool-icon" style="background:#eafaf1; color:#27ae60">
            <i class="fas fa-briefcase"></i>
        </div>
        <div>
            <h2 class="noah-tool-title">Offre Conseiller — Formulation</h2>
            <p class="noah-tool-sub">Construisez votre pitch commercial en 3 versions adaptées à votre persona</p>
        </div>
        <span class="noah-tool-badge">Noah IA</span>
    </div>

    <div class="noah-form-card">
        <form id="form-offre">
            <input type="hidden" name="tool" value="offre">
            <div class="noah-form-grid">
                <div class="noah-field">
                    <label class="noah-label">Votre métier</label>
                    <input class="noah-input" type="text" name="metier" placeholder="ex : agent immobilier" required>
                </div>
                <div class="noah-field">
                    <label class="noah-label">Persona ciblé</label>
                    <input class="noah-input" type="text" name="persona" placeholder="ex : vendeurs pressés" required>
                </div>
                <div class="noah-field">
                    <label class="noah-label">Objectif du client</label>
                    <input class="noah-input" type="text" name="objectif_client" placeholder="ex : vendre vite et au bon prix" required>
                </div>
                <div class="noah-field">
                    <label class="noah-label">Vos points forts</label>
                    <input class="noah-input" type="text" name="points_forts" placeholder="ex : réactivité, réseau local, photos pro" required>
                </div>
            </div>
            <button class="noah-submit" type="submit">
                <i class="fas fa-wand-magic-sparkles"></i> Formuler mon offre avec Noah
            </button>
            <div class="noah-result-box">
                <div class="noah-result-label"><i class="fas fa-sparkles"></i> Résultat Noah IA</div>
                <div class="noah-result-content"></div>
            </div>
            <div class="noah-error-box"></div>
        </form>
    </div>

</div>
<script>initNoahForm('form-offre', '#27ae60');</script>

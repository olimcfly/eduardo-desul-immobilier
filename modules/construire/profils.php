<?php include __DIR__ . '/_noah_styles.php'; ?>

<div style="--tool-color:#3498db">

    <a href="?module=construire" class="noah-back">
        <i class="fas fa-arrow-left"></i> Retour à Construire
    </a>

    <div class="noah-tool-header">
        <div class="noah-tool-icon" style="background:#e3f2fd; color:#3498db">
            <i class="fas fa-brain"></i>
        </div>
        <div>
            <h2 class="noah-tool-title">NeuroPersona — Profils Clients</h2>
            <p class="noah-tool-sub">Identifiez vos 3 profils clients prioritaires sur votre territoire</p>
        </div>
        <span class="noah-tool-badge">Noah IA</span>
    </div>

    <div class="noah-form-card">
        <form id="form-profils">
            <input type="hidden" name="tool" value="profils">
            <div class="noah-form-grid">
                <div class="noah-field full">
                    <label class="noah-label">Votre activité</label>
                    <input class="noah-input" type="text" name="activite" placeholder="ex : conseiller en immobilier indépendant depuis 3 ans" required>
                </div>
                <div class="noah-field">
                    <label class="noah-label">Zone géographique</label>
                    <input class="noah-input" type="text" name="zone" placeholder="ex : Aix-en-Provence Sud" required>
                </div>
                <div class="noah-field">
                    <label class="noah-label">Objectif mensuel</label>
                    <input class="noah-input" type="text" name="objectif" placeholder="ex : 3 mandats par mois" required>
                </div>
            </div>
            <button class="noah-submit" type="submit">
                <i class="fas fa-wand-magic-sparkles"></i> Identifier mes profils clients avec Noah
            </button>
            <div class="noah-result-box">
                <div class="noah-result-label"><i class="fas fa-sparkles"></i> Résultat Noah IA</div>
                <div class="noah-result-content"></div>
            </div>
            <div class="noah-error-box"></div>
        </form>
    </div>

</div>
<script>initNoahForm('form-profils', '#3498db');</script>

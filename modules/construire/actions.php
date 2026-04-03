<?php include __DIR__ . '/_noah_styles.php'; ?>

<div style="--tool-color:#16a085">

    <a href="?module=construire" class="noah-back">
        <i class="fas fa-arrow-left"></i> Retour à Construire
    </a>

    <div class="noah-tool-header">
        <div class="noah-tool-icon" style="background:#e8f8f5; color:#16a085">
            <i class="fas fa-bolt"></i>
        </div>
        <div>
            <h2 class="noah-tool-title">Actions du Jour</h2>
            <p class="noah-tool-sub">Obtenez 3 à 5 actions concrètes et mesurables pour aujourd'hui</p>
        </div>
        <span class="noah-tool-badge">Noah IA</span>
    </div>

    <div class="noah-form-card">
        <form id="form-actions">
            <input type="hidden" name="tool" value="actions">
            <div class="noah-form-grid">
                <div class="noah-field">
                    <label class="noah-label">Niveau d'expérience</label>
                    <input class="noah-input" type="text" name="experience" placeholder="ex : 2 ans en immobilier" required>
                </div>
                <div class="noah-field">
                    <label class="noah-label">Objectif mensuel</label>
                    <input class="noah-input" type="text" name="objectif" placeholder="ex : 3 mandats signés" required>
                </div>
                <div class="noah-field">
                    <label class="noah-label">Biens en portefeuille</label>
                    <input class="noah-input" type="text" name="biens" placeholder="ex : 8 biens actifs" required>
                </div>
                <div class="noah-field">
                    <label class="noah-label">Activité actuelle</label>
                    <input class="noah-input" type="text" name="activite" placeholder="ex : peu de prospection terrain" required>
                </div>
            </div>
            <button class="noah-submit" type="submit">
                <i class="fas fa-wand-magic-sparkles"></i> Générer mes actions du jour avec Noah
            </button>
            <div class="noah-result-box">
                <div class="noah-result-label"><i class="fas fa-sparkles"></i> Résultat Noah IA</div>
                <div class="noah-result-content"></div>
            </div>
            <div class="noah-error-box"></div>
        </form>
    </div>

</div>
<script>initNoahForm('form-actions', '#16a085');</script>

<?php include __DIR__ . '/_noah_styles.php'; ?>

<div style="--tool-color:#8e44ad">

    <a href="?module=construire" class="noah-back">
        <i class="fas fa-arrow-left"></i> Retour à Construire
    </a>

    <div class="noah-tool-header">
        <div class="noah-tool-icon" style="background:#f5eef8; color:#8e44ad">
            <i class="fas fa-map-marked-alt"></i>
        </div>
        <div>
            <h2 class="noah-tool-title">Zone de Prospection — Stratégie</h2>
            <p class="noah-tool-sub">Délimitez votre territoire de prospection en 3 niveaux stratégiques</p>
        </div>
        <span class="noah-tool-badge">Noah IA</span>
    </div>

    <div class="noah-form-card">
        <form id="form-zone">
            <input type="hidden" name="tool" value="zone">
            <div class="noah-form-grid">
                <div class="noah-field">
                    <label class="noah-label">Ville principale</label>
                    <input class="noah-input" type="text" name="ville" placeholder="ex : Mérignac" required>
                </div>
                <div class="noah-field">
                    <label class="noah-label">Type de biens</label>
                    <input class="noah-input" type="text" name="type_biens" placeholder="ex : maisons 4 pièces" required>
                </div>
                <div class="noah-field full">
                    <label class="noah-label">Objectif de mandats</label>
                    <input class="noah-input" type="text" name="objectif" placeholder="ex : 5 mandats actifs" required>
                </div>
            </div>
            <button class="noah-submit" type="submit">
                <i class="fas fa-wand-magic-sparkles"></i> Définir ma zone avec Noah
            </button>
            <div class="noah-result-box">
                <div class="noah-result-label"><i class="fas fa-sparkles"></i> Résultat Noah IA</div>
                <div class="noah-result-content"></div>
            </div>
            <div class="noah-error-box"></div>
        </form>
    </div>

</div>
<script>initNoahForm('form-zone', '#8e44ad');</script>

<?php
/**
 * TEMPLATE: Formulaire d'Estimation Gratuite
 * /front/templates/estimation-form.php
 *
 * Formulaire moderne pour demande d'estimation
 */

if (!defined('SITE_URL')) define('SITE_URL', 'https://example.com');
if (!defined('SITE_TITLE')) define('SITE_TITLE', 'Mon Site Immobilier');

?>

<style>
.estimation-section {
    background: linear-gradient(135deg, #f8f6f3 0%, #ece8e2 100%);
    padding: 60px 20px;
    margin: 60px 0;
}

.estimation-container {
    max-width: 800px;
    margin: 0 auto;
}

.estimation-header {
    text-align: center;
    margin-bottom: 40px;
}

.estimation-header h2 {
    font-size: 32px;
    font-weight: 700;
    color: #1a4d7a;
    margin-bottom: 12px;
    font-family: "Playfair Display", serif;
}

.estimation-header p {
    font-size: 16px;
    color: #718096;
    max-width: 500px;
    margin: 0 auto;
}

.estimation-form {
    background: white;
    border-radius: 12px;
    padding: 40px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-grid.full {
    grid-template-columns: 1fr;
}

@media (max-width: 640px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    color: #1a4d7a;
    margin-bottom: 8px;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-group label .required {
    color: #F44336;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 12px 16px;
    border: 1px solid #e2d9ce;
    border-radius: 6px;
    font-family: inherit;
    font-size: 14px;
    color: #2d3748;
    background: white;
    transition: all 0.3s ease;
}

.form-group input::placeholder {
    color: #a0aec0;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #1a4d7a;
    box-shadow: 0 0 0 3px rgba(26, 77, 122, 0.1);
}

.form-group input.field-error,
.form-group select.field-error,
.form-group textarea.field-error {
    border-color: #F44336;
    background-color: #ffebee;
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.form-section-title {
    font-size: 16px;
    font-weight: 700;
    color: #1a4d7a;
    margin-top: 30px;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #d4a574;
    grid-column: 1 / -1;
}

.form-submit {
    margin-top: 30px;
    display: flex;
    gap: 12px;
    justify-content: center;
}

.btn-primary {
    background: linear-gradient(135deg, #1a4d7a 0%, #0e3a5c 100%);
    color: white;
    padding: 14px 48px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(26, 77, 122, 0.3);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.form-help-text {
    font-size: 12px;
    color: #718096;
    margin-top: 4px;
}

/* Alerts */
.form-alert {
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 14px;
    animation: slideDown 0.3s ease-out;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border-left: 4px solid #F44336;
}

.alert-info {
    background-color: #d1ecf1;
    color: #0c5460;
    border-left: 4px solid #17a2b8;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.spinner {
    display: inline-block;
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<div class="estimation-section">
    <div class="estimation-container">
        <div class="estimation-header">
            <h2>Estimez votre bien immobilier</h2>
            <p>Obtenez une estimation gratuite et sans engagement en moins d'une minute</p>
        </div>

        <form id="estimation-form" class="estimation-form">

            <!-- Infos Personnelles -->
            <div class="form-section-title">Vos informations</div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="first_name">
                        Prénom <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        id="first_name"
                        name="first_name"
                        placeholder="Jean"
                        required
                    />
                </div>

                <div class="form-group">
                    <label for="last_name">
                        Nom <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        id="last_name"
                        name="last_name"
                        placeholder="Dupont"
                    />
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="email">
                        Email <span class="required">*</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="jean@example.com"
                        required
                    />
                </div>

                <div class="form-group">
                    <label for="phone">
                        Téléphone <span class="required">*</span>
                    </label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        placeholder="06 XX XX XX XX"
                        required
                    />
                </div>
            </div>

            <!-- Infos Bien -->
            <div class="form-section-title">Votre bien immobilier</div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="property_type">
                        Type de bien <span class="required">*</span>
                    </label>
                    <select id="property_type" name="property_type" required>
                        <option value="">-- Sélectionner --</option>
                        <option value="Appartement">Appartement</option>
                        <option value="Maison">Maison</option>
                        <option value="Villa">Villa</option>
                        <option value="Terrain">Terrain</option>
                        <option value="Immeuble">Immeuble</option>
                        <option value="Bureau">Bureau</option>
                        <option value="Commercial">Commercial</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="rooms">
                        Nombre de pièces
                    </label>
                    <input
                        type="number"
                        id="rooms"
                        name="rooms"
                        placeholder="3"
                        min="0"
                    />
                </div>
            </div>

            <div class="form-grid full">
                <div class="form-group">
                    <label for="address">
                        Adresse <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        id="address"
                        name="address"
                        placeholder="123 Rue de la Paix, 75001 Paris"
                        required
                    />
                    <span class="form-help-text">
                        Adresse complète (rue, code postal, ville)
                    </span>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="surface">
                        Surface (m²) <span class="required">*</span>
                    </label>
                    <input
                        type="number"
                        id="surface"
                        name="surface"
                        placeholder="65"
                        required
                        min="1"
                    />
                </div>

                <div class="form-group">
                    <label for="year_built">
                        Année de construction
                    </label>
                    <input
                        type="number"
                        id="year_built"
                        name="year_built"
                        placeholder="2010"
                        min="1800"
                        max="2100"
                    />
                </div>
            </div>

            <!-- Notes -->
            <div class="form-grid full">
                <div class="form-group">
                    <label for="notes">
                        Notes supplémentaires
                    </label>
                    <textarea
                        id="notes"
                        name="notes"
                        placeholder="État du bien, travaux à prévoir, points forts..."
                    ></textarea>
                    <span class="form-help-text">
                        Décrivez l'état de votre bien pour une estimation plus précise
                    </span>
                </div>
            </div>

            <!-- Submit -->
            <div class="form-submit">
                <button type="submit" class="btn-primary">
                    📊 Obtenir mon estimation gratuite
                </button>
            </div>

            <p style="text-align: center; font-size: 12px; color: #718096; margin-top: 20px;">
                Vos données sont confidentielles et ne seront partagées avec personne.
            </p>

        </form>

    </div>
</div>

<!-- Charger le handler JavaScript -->
<script src="<?php echo SITE_URL; ?>/front/assets/js/estimation-handler.js"></script>

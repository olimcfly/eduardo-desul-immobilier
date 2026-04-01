/**
 * ESTIMATION FORM HANDLER
 * /front/assets/js/estimation-handler.js
 *
 * Gère le formulaire d'estimation gratuite
 * - Validation côté client
 * - Soumission AJAX
 * - Messages d'erreur/succès
 */

class EstimationHandler {
    constructor(options = {}) {
        this.formSelector = options.formSelector || '#estimation-form';
        this.form = document.querySelector(this.formSelector);

        if (!this.form) {
            console.warn('EstimationHandler: Formulaire non trouvé - ' + this.formSelector);
            return;
        }

        this.apiUrl = options.apiUrl || '/admin/api/estimation/submit';
        this.setupListeners();
    }

    /**
     * Setup event listeners
     */
    setupListeners() {
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));

        // Validation temps réel
        const inputs = this.form.querySelectorAll('[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('change', () => this.clearFieldError(input));
        });
    }

    /**
     * Valider un champ individuel
     */
    validateField(input) {
        const name = input.name;
        const value = input.value.trim();
        const error = this.getFieldError(name, value);

        if (error) {
            this.showFieldError(input, error);
        } else {
            this.clearFieldError(input);
        }

        return !error;
    }

    /**
     * Obtenir le message d'erreur pour un champ
     */
    getFieldError(name, value) {
        if (!value) {
            return 'Ce champ est requis';
        }

        switch (name) {
            case 'email':
                return !this.validateEmail(value) ? 'Email invalide' : null;
            case 'phone':
                return !this.validatePhone(value) ? 'Téléphone invalide' : null;
            case 'surface':
                return !this.validateNumber(value) ? 'Doit être un nombre' : null;
            case 'rooms':
            case 'year_built':
                return value && !this.validateNumber(value) ? 'Doit être un nombre' : null;
        }

        return null;
    }

    /**
     * Validations
     */
    validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    validatePhone(phone) {
        return /^[\d\s\-\+\.()]{10,}$/.test(phone);
    }

    validateNumber(value) {
        return !isNaN(value) && value !== '';
    }

    /**
     * Afficher erreur sur champ
     */
    showFieldError(input, message) {
        this.clearFieldError(input);

        input.classList.add('field-error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error-message';
        errorDiv.textContent = message;

        input.parentNode.insertBefore(errorDiv, input.nextSibling);
    }

    /**
     * Supprimer erreur sur champ
     */
    clearFieldError(input) {
        input.classList.remove('field-error');
        const errorMsg = input.parentNode.querySelector('.field-error-message');
        if (errorMsg) {
            errorMsg.remove();
        }
    }

    /**
     * Valider le formulaire complet
     */
    validateForm() {
        let isValid = true;
        const required_fields = ['first_name', 'email', 'phone', 'property_type', 'address', 'surface'];

        required_fields.forEach(fieldName => {
            const input = this.form.querySelector(`[name="${fieldName}"]`);
            if (!this.validateField(input)) {
                isValid = false;
            }
        });

        return isValid;
    }

    /**
     * Traiter la soumission du formulaire
     */
    async handleSubmit(e) {
        e.preventDefault();

        // Validation
        if (!this.validateForm()) {
            this.showAlert('error', '❌ Veuillez corriger les erreurs du formulaire');
            return;
        }

        // Afficher loading
        const submitBtn = this.form.querySelector('[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span> Traitement...';

        try {
            // Préparer les données
            const formData = new FormData(this.form);

            // Envoyer la requête
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const json = await response.json();

            if (!response.ok) {
                if (json.errors) {
                    // Erreurs validation
                    Object.entries(json.errors).forEach(([field, message]) => {
                        const input = this.form.querySelector(`[name="${field}"]`);
                        if (input) {
                            this.showFieldError(input, message);
                        }
                    });
                    this.showAlert('error', '❌ ' + (json.error || 'Erreurs de validation'));
                } else {
                    this.showAlert('error', '❌ ' + (json.error || 'Erreur lors du traitement'));
                }
                return;
            }

            // Succès
            this.showAlert('success', '✅ ' + json.message);
            this.form.reset();

            // Afficher les détails de référence
            if (json.data && json.data.reference) {
                this.showAlert('info', `Référence de votre demande: <strong>${json.data.reference}</strong>`);
            }

            // Optionnel: redirection après succès
            setTimeout(() => {
                // window.location.href = '/estimation-confirmation';
            }, 2000);

        } catch (err) {
            console.error('Erreur réseau:', err);
            this.showAlert('error', '❌ Erreur réseau: ' + err.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    }

    /**
     * Afficher une alerte
     */
    showAlert(type, message) {
        // Supprimer ancienne alerte
        const oldAlert = this.form.querySelector('.form-alert');
        if (oldAlert) {
            oldAlert.remove();
        }

        // Créer nouvelle alerte
        const alert = document.createElement('div');
        alert.className = `form-alert alert-${type}`;
        alert.innerHTML = message;

        this.form.insertBefore(alert, this.form.firstChild);

        // Auto-supprimer après 5s
        if (type !== 'error') {
            setTimeout(() => alert.remove(), 5000);
        }
    }
}

/**
 * AUTO-INIT - Initialiser au chargement du document
 */
document.addEventListener('DOMContentLoaded', () => {
    window.estimationHandler = new EstimationHandler();
});

/**
 * CSS Styling pour erreurs
 */
if (document.head) {
    const style = document.createElement('style');
    style.textContent = `
        .field-error {
            border-color: #F44336 !important;
            background-color: #ffebee !important;
        }

        .field-error-message {
            color: #F44336;
            font-size: 12px;
            margin-top: 4px;
            display: block;
        }

        .form-alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
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

        .spinner {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 6px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
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
    `;
    document.head.appendChild(style);
}

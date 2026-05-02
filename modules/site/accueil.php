<?php
$pageTitle = 'Parametres du site';
$pageDescription = 'Configure l\'apparence et les informations de ton site';

function renderContent()
{
    ?>
    <style>
    .config-container {
        max-width: 900px;
        margin: 0 auto;
    }
    .config-hero {
        background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
        border-radius: 16px;
        padding: 36px 40px;
        color: #fff;
        margin-bottom: 32px;
        box-shadow: 0 4px 20px rgba(15,34,55,.18);
    }
    .config-hero h1 {
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 12px;
    }
    .config-hero p {
        font-size: 15px;
        color: rgba(255,255,255,.7);
        margin: 0;
    }

    .config-section {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
    }
    .config-section-title {
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #f1f5f9;
    }

    .config-form-group {
        margin-bottom: 20px;
    }
    .config-form-group:last-child {
        margin-bottom: 0;
    }

    .config-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }
    .config-input,
    .config-textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        transition: border-color .2s;
    }
    .config-input:focus,
    .config-textarea:focus {
        outline: none;
        border-color: #c9a84c;
        box-shadow: 0 0 0 3px rgba(201,168,76,.1);
    }
    .config-textarea {
        resize: vertical;
        min-height: 80px;
    }

    .config-checkbox-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 12px;
    }
    .config-checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px;
        background: #f9fafb;
        border-radius: 8px;
        cursor: pointer;
    }
    .config-checkbox-item input[type="checkbox"] {
        cursor: pointer;
    }
    .config-checkbox-item label {
        cursor: pointer;
        margin: 0;
    }

    .config-button-group {
        display: flex;
        gap: 12px;
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
    }
    .config-btn {
        padding: 11px 24px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all .2s;
    }
    .config-btn-primary {
        background: #c9a84c;
        color: #0f2237;
    }
    .config-btn-primary:hover {
        background: #b8943d;
        transform: translateY(-1px);
    }
    .config-btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
    }
    .config-btn-secondary:hover {
        background: #e5e7eb;
    }

    .config-helper {
        font-size: 12px;
        color: #6b7280;
        margin-top: 4px;
    }

    @media (max-width: 600px) {
        .config-hero { padding: 24px 20px; }
        .config-section { padding: 16px; }
    }
    </style>

    <div class="config-container">
        <!-- Hero -->
        <div class="config-hero">
            <h1>Parametres de ton site</h1>
            <p>Configure ton branding, tes informations et l'apparence de ton site.</p>
        </div>

        <form method="POST" action="#" class="config-form">

            <!-- SECTION 1: Branding -->
            <div class="config-section">
                <h2 class="config-section-title"><i class="fas fa-palette" style="margin-right:8px;color:#3b82f6;"></i>Branding</h2>

                <div class="config-form-group">
                    <label class="config-label">Nom de l'entreprise</label>
                    <input type="text" class="config-input" name="company_name" value="Eduardo Desul Immobilier" placeholder="Ex: Eduardo Desul Immobilier">
                    <div class="config-helper">Affiche en haut du site et dans le header</div>
                </div>

                <div class="config-form-group">
                    <label class="config-label">Slogan</label>
                    <input type="text" class="config-input" name="slogan" value="Votre expert immobilier local" placeholder="Ex: Votre expert immobilier local">
                    <div class="config-helper">Affiche sous le nom de l'entreprise</div>
                </div>

                <div class="config-form-group">
                    <label class="config-label">Logo (URL)</label>
                    <input type="text" class="config-input" name="logo_url" value="/images/logo.png" placeholder="Ex: /images/logo.png">
                    <div class="config-helper">Lien vers ton logo (relatif ou absolu)</div>
                </div>
            </div>

            <!-- SECTION 2: NAP (Nom, Adresse, Téléphone) -->
            <div class="config-section">
                <h2 class="config-section-title"><i class="fas fa-map-location-dot" style="margin-right:8px;color:#10b981;"></i>Informations NAP (Google My Business)</h2>

                <div class="config-form-group">
                    <label class="config-label">Nom (identique au branding)</label>
                    <input type="text" class="config-input" name="nap_name" value="Eduardo Desul Immobilier" placeholder="Nom de l'entreprise">
                    <div class="config-helper">Doit correspondre a ton GMB</div>
                </div>

                <div class="config-form-group">
                    <label class="config-label">Adresse</label>
                    <input type="text" class="config-input" name="nap_address" value="33 Avenue de Bordeaux, 33000 Bordeaux" placeholder="Adresse complete">
                    <div class="config-helper">Adresse du siege social ou principal</div>
                </div>

                <div class="config-form-group">
                    <label class="config-label">Telephone</label>
                    <input type="tel" class="config-input" name="nap_phone" value="+33 5 XX XX XX XX" placeholder="+33 5 XX XX XX XX">
                    <div class="config-helper">Format international recommande</div>
                </div>

                <div class="config-form-group">
                    <label class="config-label">Email</label>
                    <input type="email" class="config-input" name="nap_email" value="contact@eduardo-desul-immobilier.fr" placeholder="email@example.com">
                    <div class="config-helper">Email de contact principal</div>
                </div>
            </div>

            <!-- SECTION 3: Menu Principal -->
            <div class="config-section">
                <h2 class="config-section-title"><i class="fas fa-bars" style="margin-right:8px;color:#f59e0b;"></i>Menu Principal</h2>
                <p style="color:#6b7280;margin:0 0 16px;">Active ou desactive les pages affichees dans le menu principal</p>

                <div class="config-checkbox-group">
                    <div class="config-checkbox-item">
                        <input type="checkbox" id="menu_dashboard" name="menu_pages[]" value="dashboard" checked>
                        <label for="menu_dashboard">Tableau de bord</label>
                    </div>
                    <div class="config-checkbox-item">
                        <input type="checkbox" id="menu_commencer" name="menu_pages[]" value="commencer" checked>
                        <label for="menu_commencer">Commencer</label>
                    </div>
                    <div class="config-checkbox-item">
                        <input type="checkbox" id="menu_attirer" name="menu_pages[]" value="attirer" checked>
                        <label for="menu_attirer">Attirer</label>
                    </div>
                    <div class="config-checkbox-item">
                        <input type="checkbox" id="menu_capture" name="menu_pages[]" value="capture" checked>
                        <label for="menu_capture">Capture</label>
                    </div>
                    <div class="config-checkbox-item">
                        <input type="checkbox" id="menu_convertir" name="menu_pages[]" value="convertir" checked>
                        <label for="menu_convertir">Convertir</label>
                    </div>
                    <div class="config-checkbox-item">
                        <input type="checkbox" id="menu_optimiser" name="menu_pages[]" value="optimiser" checked>
                        <label for="menu_optimiser">Optimiser</label>
                    </div>
                    <div class="config-checkbox-item">
                        <input type="checkbox" id="menu_cms" name="menu_pages[]" value="cms" checked>
                        <label for="menu_cms">Contenu</label>
                    </div>
                    <div class="config-checkbox-item">
                        <input type="checkbox" id="menu_crm" name="menu_pages[]" value="crm" checked>
                        <label for="menu_crm">Contacts</label>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: Footer -->
            <div class="config-section">
                <h2 class="config-section-title"><i class="fas fa-footer" style="margin-right:8px;color:#ef4444;"></i>Footer</h2>
                <p style="color:#6b7280;margin:0 0 16px;">Configure les liens du footer et les informations affichees</p>

                <div class="config-form-group">
                    <label class="config-label">Texte copyright</label>
                    <input type="text" class="config-input" name="footer_copyright" value="Copyright 2026 Eduardo Desul Immobilier. Tous droits reserves." placeholder="Copyright text">
                    <div class="config-helper">Affiche au bas du footer</div>
                </div>

                <div class="config-form-group">
                    <label class="config-label" style="margin-bottom:12px;">Liens du footer</label>
                    <div class="config-checkbox-group">
                        <div class="config-checkbox-item">
                            <input type="checkbox" id="footer_about" name="footer_links[]" value="about" checked>
                            <label for="footer_about">A propos</label>
                        </div>
                        <div class="config-checkbox-item">
                            <input type="checkbox" id="footer_services" name="footer_links[]" value="services" checked>
                            <label for="footer_services">Services</label>
                        </div>
                        <div class="config-checkbox-item">
                            <input type="checkbox" id="footer_contact" name="footer_links[]" value="contact" checked>
                            <label for="footer_contact">Contact</label>
                        </div>
                        <div class="config-checkbox-item">
                            <input type="checkbox" id="footer_blog" name="footer_links[]" value="blog" checked>
                            <label for="footer_blog">Blog</label>
                        </div>
                        <div class="config-checkbox-item">
                            <input type="checkbox" id="footer_privacy" name="footer_links[]" value="privacy" checked>
                            <label for="footer_privacy">Politique de confidentialite</label>
                        </div>
                        <div class="config-checkbox-item">
                            <input type="checkbox" id="footer_cgv" name="footer_links[]" value="cgv" checked>
                            <label for="footer_cgv">Conditions generales</label>
                        </div>
                        <div class="config-checkbox-item">
                            <input type="checkbox" id="footer_sitemap" name="footer_links[]" value="sitemap">
                            <label for="footer_sitemap">Plan du site</label>
                        </div>
                    </div>
                </div>

                <div class="config-form-group">
                    <label class="config-label">Reseaux sociaux (URLs)</label>
                    <div style="display:grid;gap:12px;">
                        <input type="url" class="config-input" name="social_facebook" placeholder="https://facebook.com/..." value="">
                        <input type="url" class="config-input" name="social_instagram" placeholder="https://instagram.com/..." value="">
                        <input type="url" class="config-input" name="social_linkedin" placeholder="https://linkedin.com/..." value="">
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="config-button-group">
                <button type="submit" class="config-btn config-btn-primary">
                    <i class="fas fa-save" style="margin-right:6px;"></i> Enregistrer les modifications
                </button>
                <button type="reset" class="config-btn config-btn-secondary">
                    <i class="fas fa-redo" style="margin-right:6px;"></i> Reinitialiser
                </button>
            </div>

        </form>
    </div>

    <script>
    document.querySelector('.config-form').addEventListener('submit', function(e) {
        e.preventDefault();
        alert('Les parametres ont ete enregistres avec succes!');
        // Ici on peut ajouter une vraie sauvegarde avec AJAX
    });
    </script>
    <?php
}

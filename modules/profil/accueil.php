<?php
$pageTitle = 'Mon profil';
$pageDescription = 'Infos personnelles et photo de profil';

function renderContent()
{
    ?>
    <style>
    .profil-container {
        max-width: 900px;
        margin: 0 auto;
    }
    .profil-hero {
        background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
        border-radius: 16px;
        padding: 36px 40px;
        color: #fff;
        margin-bottom: 32px;
        box-shadow: 0 4px 20px rgba(15,34,55,.18);
    }
    .profil-hero h1 {
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 12px;
    }
    .profil-hero p {
        font-size: 15px;
        color: rgba(255,255,255,.7);
        margin: 0;
    }

    .profil-section {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
    }
    .profil-section-title {
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #f1f5f9;
    }

    .profil-form-group {
        margin-bottom: 20px;
    }
    .profil-form-group:last-child {
        margin-bottom: 0;
    }
    .profil-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    @media (max-width: 600px) {
        .profil-form-row {
            grid-template-columns: 1fr;
        }
    }

    .profil-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }
    .profil-input,
    .profil-textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        transition: border-color .2s;
    }
    .profil-input:focus,
    .profil-textarea:focus {
        outline: none;
        border-color: #c9a84c;
        box-shadow: 0 0 0 3px rgba(201,168,76,.1);
    }

    .profil-avatar-section {
        display: flex;
        align-items: flex-start;
        gap: 24px;
        margin-bottom: 24px;
    }
    .profil-avatar-box {
        flex-shrink: 0;
    }
    .profil-avatar {
        width: 120px;
        height: 120px;
        border-radius: 12px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        font-weight: 700;
        color: #c9a84c;
        border: 2px solid #e5e7eb;
        overflow: hidden;
    }
    .profil-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .profil-avatar-info {
        flex: 1;
    }
    .profil-avatar-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }
    .profil-avatar-input {
        display: block;
        margin-bottom: 12px;
    }
    .profil-helper {
        font-size: 12px;
        color: #6b7280;
        margin-top: 4px;
    }

    .profil-button-group {
        display: flex;
        gap: 12px;
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
    }
    .profil-btn {
        padding: 11px 24px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all .2s;
    }
    .profil-btn-primary {
        background: #c9a84c;
        color: #0f2237;
    }
    .profil-btn-primary:hover {
        background: #b8943d;
        transform: translateY(-1px);
    }
    .profil-btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
    }
    .profil-btn-secondary:hover {
        background: #e5e7eb;
    }

    @media (max-width: 600px) {
        .profil-hero { padding: 24px 20px; }
        .profil-section { padding: 16px; }
        .profil-avatar-section { flex-direction: column; }
    }
    </style>

    <div class="profil-container">
        <!-- Hero -->
        <div class="profil-hero">
            <h1>Mon profil</h1>
            <p>Mettez a jour vos informations personnelles et votre photo de profil.</p>
        </div>

        <form method="POST" action="#" class="profil-form">

            <!-- SECTION: Photo de profil -->
            <div class="profil-section">
                <h2 class="profil-section-title"><i class="fas fa-camera" style="margin-right:8px;color:#3b82f6;"></i>Photo de profil</h2>

                <div class="profil-avatar-section">
                    <div class="profil-avatar-box">
                        <div class="profil-avatar" id="profil-avatar-preview">
                            ED
                        </div>
                    </div>
                    <div class="profil-avatar-info">
                        <label class="profil-avatar-label">URL de la photo</label>
                        <input type="url" class="profil-input profil-avatar-input" name="profil_photo" placeholder="https://..." value="">
                        <div class="profil-helper">Lien direct vers une image (JPG, PNG). Taille recommandee: 400x400px</div>
                    </div>
                </div>
            </div>

            <!-- SECTION: Informations personnelles -->
            <div class="profil-section">
                <h2 class="profil-section-title"><i class="fas fa-user" style="margin-right:8px;color:#10b981;"></i>Informations personnelles</h2>

                <div class="profil-form-row">
                    <div class="profil-form-group">
                        <label class="profil-label">Prenom</label>
                        <input type="text" class="profil-input" name="profil_prenom" placeholder="Ex: Eduardo" value="">
                    </div>
                    <div class="profil-form-group">
                        <label class="profil-label">Nom</label>
                        <input type="text" class="profil-input" name="profil_nom" placeholder="Ex: Desul" value="">
                    </div>
                </div>

                <div class="profil-form-group">
                    <label class="profil-label">Email</label>
                    <input type="email" class="profil-input" name="profil_email" placeholder="contact@example.com" value="">
                    <div class="profil-helper">Email de contact principal</div>
                </div>
            </div>

            <!-- SECTION: Localisation -->
            <div class="profil-section">
                <h2 class="profil-section-title"><i class="fas fa-map-location-dot" style="margin-right:8px;color:#f59e0b;"></i>Localisation</h2>

                <div class="profil-form-group">
                    <label class="profil-label">Adresse</label>
                    <input type="text" class="profil-input" name="profil_address" placeholder="Ex: 33 Avenue de Bordeaux" value="">
                    <div class="profil-helper">Adresse personnelle ou du bureau</div>
                </div>

                <div class="profil-form-row">
                    <div class="profil-form-group">
                        <label class="profil-label">Ville</label>
                        <input type="text" class="profil-input" name="profil_ville" placeholder="Ex: Bordeaux" value="">
                    </div>
                    <div class="profil-form-group">
                        <label class="profil-label">Telephone</label>
                        <input type="tel" class="profil-input" name="profil_phone" placeholder="+33 5 XX XX XX XX" value="">
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="profil-button-group">
                <button type="submit" class="profil-btn profil-btn-primary">
                    <i class="fas fa-save" style="margin-right:6px;"></i> Enregistrer les modifications
                </button>
                <button type="reset" class="profil-btn profil-btn-secondary">
                    <i class="fas fa-redo" style="margin-right:6px;"></i> Reinitialiser
                </button>
            </div>

        </form>
    </div>

    <script>
    document.querySelector('.profil-form').addEventListener('submit', function(e) {
        e.preventDefault();
        alert('Vos informations ont ete enregistrees avec succes!');
    });
    </script>
    <?php
}

<?php
$pageTitle       = 'Noah — Assistant Stratégique';
$pageDescription = 'Votre assistant IA pour structurer votre activité immobilière';


function renderContent()
{
    ?>
    <div class="page-header">
        <h1><i class="fas fa-robot page-icon"></i> NOAH <span class="page-title-accent">Assistant Stratégique</span></h1>
        <p>Analyse, recommandations et formulations prêtes à utiliser.</p>
    </div>

    <style>
        .noah-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 1.25rem;
            margin-top: 1.5rem;
        }
        .noah-card {
            background: white;
            border-radius: 8px;
            border-left: 4px solid var(--noah-accent, #3498db);
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
            overflow: hidden;
            transition: box-shadow .2s, transform .2s;
        }
        .noah-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,.1); transform: translateY(-2px); }
        .noah-card-header {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: 1.1rem 1.25rem;
            cursor: pointer;
            user-select: none;
        }
        .noah-card-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; flex-shrink: 0;
        }
        .noah-card-title { font-size: .95rem; font-weight: 700; color: #2c3e50; margin: 0; }
        .noah-card-sub   { font-size: .78rem; color: #7f8c8d; margin: .15rem 0 0; }
        .noah-card-chevron { margin-left: auto; color: #bdc3c7; font-size: .8rem; transition: transform .2s; }
        .noah-card.open .noah-card-chevron { transform: rotate(180deg); }

        .noah-form { padding: 0 1.25rem 1.25rem; display: none; border-top: 1px solid #f0f0f0; margin-top: .25rem; padding-top: 1rem; }
        .noah-card.open .noah-form { display: block; }

        .noah-field { margin-bottom: .85rem; }
        .noah-label {
            display: block; font-size: .75rem; font-weight: 600;
            color: #5a6a7a; text-transform: uppercase; letter-spacing: .04em;
            margin-bottom: .3rem;
        }
        .noah-input, .noah-select {
            width: 100%; padding: .6rem .85rem;
            background: #f8fafc; border: 1.5px solid #dde1e7;
            border-radius: 8px; color: #2c3e50; font-size: .88rem;
            outline: none; transition: border-color .15s;
        }
        .noah-input:focus, .noah-select:focus { border-color: #3498db; background: white; }

        .noah-btn {
            width: 100%; padding: .7rem;
            background: var(--noah-accent, #3498db); color: white;
            border: none; border-radius: 8px;
            font-weight: 700; font-size: .9rem;
            cursor: pointer; margin-top: .25rem;
            display: flex; align-items: center; justify-content: center; gap: .5rem;
            transition: opacity .15s;
        }
        .noah-btn:hover { opacity: .9; }
        .noah-btn:disabled { opacity: .6; cursor: not-allowed; }

        .noah-result {
            margin-top: 1rem; padding: 1rem;
            background: #f8fafc;
            border: 1px solid #e8ecf0;
            border-radius: 10px;
            font-size: .88rem; color: #2c3e50;
            white-space: pre-wrap; line-height: 1.65;
            display: none;
        }
        .noah-result.visible { display: block; }
        .noah-error {
            margin-top: .75rem; padding: .7rem 1rem;
            background: #fdedec; border: 1px solid #fadbd8;
            border-radius: 8px; font-size: .85rem; color: #e74c3c;
            display: none;
        }
        .noah-error.visible { display: block; }
        .noah-spinner { animation: spin .8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>

    <div class="noah-grid">

        <?php noahCard('positionnement', 'Positionnement', 'Formulations d\'accroche claires', '#8e44ad', '#f5eef8', 'fa-bullseye', [
            ['name' => 'metier',  'label' => 'Votre métier',        'placeholder' => 'ex : agent immobilier indépendant'],
            ['name' => 'zone',    'label' => 'Zone géographique',   'placeholder' => 'ex : Aix-en-Provence Métropole'],
            ['name' => 'persona', 'label' => 'Type de clients',     'placeholder' => 'ex : primo-accédants 30-45 ans'],
            ['name' => 'objectif','label' => 'Objectif principal',  'placeholder' => 'ex : générer des mandats vendeurs'],
        ]); ?>

        <?php noahCard('profils', 'Profils Clients', 'Identifiez vos clients idéaux', '#3498db', '#e3f2fd', 'fa-users', [
            ['name' => 'activite', 'label' => 'Votre activité',    'placeholder' => 'ex : conseiller en immobilier'],
            ['name' => 'zone',     'label' => 'Zone',              'placeholder' => 'ex : Aix-en-Provence Sud'],
            ['name' => 'objectif', 'label' => 'Objectif',         'placeholder' => 'ex : 3 mandats par mois'],
        ]); ?>

        <?php noahCard('offre', 'Formulation d\'Offre', '3 versions de votre pitch', '#27ae60', '#eafaf1', 'fa-briefcase', [
            ['name' => 'metier',        'label' => 'Votre métier',       'placeholder' => 'ex : agent immobilier'],
            ['name' => 'persona',       'label' => 'Persona ciblé',      'placeholder' => 'ex : vendeurs pressés'],
            ['name' => 'objectif_client','label' => 'Objectif du client', 'placeholder' => 'ex : vendre vite et au bon prix'],
            ['name' => 'points_forts',  'label' => 'Vos points forts',   'placeholder' => 'ex : réactivité, réseau local, photos pro'],
        ]); ?>

        <?php noahCard('zone', 'Zone de Prospection', 'Stratégie territoriale en 3 niveaux', '#e67e22', '#fef5e7', 'fa-map-marked-alt', [
            ['name' => 'ville',      'label' => 'Ville principale',  'placeholder' => 'ex : Mérignac'],
            ['name' => 'type_biens', 'label' => 'Type de biens',     'placeholder' => 'ex : maisons 4 pièces'],
            ['name' => 'objectif',   'label' => 'Objectif',          'placeholder' => 'ex : 5 mandats actifs'],
        ]); ?>

        <?php noahCard('synthese', 'Synthèse Stratégique', 'Résumé de votre situation en 100 mots', '#e74c3c', '#fdedec', 'fa-layer-group', [
            ['name' => 'activite',       'label' => 'Votre activité',    'placeholder' => 'ex : agent indépendant depuis 2 ans'],
            ['name' => 'positionnement', 'label' => 'Positionnement',    'placeholder' => 'ex : spécialiste maisons familiales'],
            ['name' => 'persona',        'label' => 'Persona principal', 'placeholder' => 'ex : familles avec enfants'],
            ['name' => 'offre',          'label' => 'Votre offre',       'placeholder' => 'ex : accompagnement complet vendeur'],
            ['name' => 'zone',           'label' => 'Zone',              'placeholder' => 'ex : Mérignac, Pessac, Talence'],
        ]); ?>

        <?php noahCard('actions', 'Actions du Jour', '3 à 5 actions concrètes et mesurables', '#16a085', '#e8f8f5', 'fa-bolt', [
            ['name' => 'experience', 'label' => 'Niveau d\'expérience', 'placeholder' => 'ex : 2 ans en immobilier'],
            ['name' => 'objectif',   'label' => 'Objectif mensuel',     'placeholder' => 'ex : 3 mandats signés'],
            ['name' => 'biens',      'label' => 'Biens en portefeuille','placeholder' => 'ex : 8 biens actifs'],
            ['name' => 'activite',   'label' => 'Activité actuelle',    'placeholder' => 'ex : peu de prospection terrain'],
        ]); ?>

        <?php noahCard('argumentaire_mandat', 'Argumentaire Mandat', 'Scripts et supports pour signer en exclusivité', '#c0392b', '#fdecea', 'fa-file-signature', [
            ['name' => 'zone',           'label' => 'Zone géographique',       'placeholder' => 'ex : Aix-en-Provence centre'],
            ['name' => 'type_bien',      'label' => 'Type de bien',            'placeholder' => 'ex : appartement T3 avec terrasse'],
            ['name' => 'profil_vendeur', 'label' => 'Profil vendeur',          'placeholder' => 'ex : vendeur prudent, compare plusieurs agences'],
            ['name' => 'objections',     'label' => 'Objections principales',  'placeholder' => 'ex : je veux garder la liberté / vos honoraires sont élevés'],
            ['name' => 'objectif',       'label' => 'Objectif du RDV',         'placeholder' => 'ex : signer un mandat exclusif au 1er rendez-vous'],
        ]); ?>

    </div>

    <script>
    // Toggle cards
    document.querySelectorAll('.noah-card-header').forEach(header => {
        header.addEventListener('click', () => {
            header.closest('.noah-card').classList.toggle('open');
        });
    });

    // Submit forms
    document.querySelectorAll('.noah-form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const card   = form.closest('.noah-card');
            const btn    = form.querySelector('.noah-btn');
            const result = card.querySelector('.noah-result');
            const errBox = card.querySelector('.noah-error');
            const icon   = btn.querySelector('i');

            btn.disabled = true;
            icon.className = 'fas fa-spinner noah-spinner';
            result.classList.remove('visible');
            errBox.classList.remove('visible');

            const fd = new FormData(form);

            try {
                const res  = await fetch('/admin/api/noah', { method: 'POST', body: fd });
                const json = await res.json();

                if (json.success) {
                    result.textContent = json.result;
                    result.classList.add('visible');
                } else {
                    errBox.textContent = json.error || 'Une erreur est survenue.';
                    errBox.classList.add('visible');
                }
            } catch (err) {
                errBox.textContent = 'Impossible de contacter le serveur.';
                errBox.classList.add('visible');
            } finally {
                btn.disabled = false;
                icon.className = 'fas fa-wand-magic-sparkles';
            }
        });
    });
    </script>
    <?php
}

// ── Helper : génère une carte Noah ───────────────────────────
function noahCard(string $tool, string $title, string $sub, string $color, string $iconBg, string $icon, array $fields): void
{
    ?>
    <div class="noah-card" style="--noah-accent:<?= $color ?>">
        <div class="noah-card-header">
            <div class="noah-card-icon" style="background:<?= $iconBg ?>; color:<?= $color ?>">
                <i class="fas <?= $icon ?>"></i>
            </div>
            <div>
                <div class="noah-card-title"><?= htmlspecialchars($title) ?></div>
                <div class="noah-card-sub"><?= htmlspecialchars($sub) ?></div>
            </div>
            <i class="fas fa-chevron-down noah-card-chevron"></i>
        </div>

        <form class="noah-form" method="POST">
            <input type="hidden" name="tool" value="<?= htmlspecialchars($tool) ?>">
            <?php foreach ($fields as $field): ?>
                <div class="noah-field">
                    <label class="noah-label"><?= htmlspecialchars($field['label']) ?></label>
                    <input class="noah-input" type="text" name="<?= htmlspecialchars($field['name']) ?>"
                           placeholder="<?= htmlspecialchars($field['placeholder']) ?>" required>
                </div>
            <?php endforeach; ?>
            <button class="noah-btn" type="submit">
                <i class="fas fa-wand-magic-sparkles"></i> Générer avec Noah
            </button>
            <div class="noah-error"></div>
            <div class="noah-result"></div>
        </form>
    </div>
    <?php
}

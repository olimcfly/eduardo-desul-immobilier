<?php
$pageTitle       = 'Construire';
$pageDescription = 'Posez les bases solides de votre activité';


function renderContent()
{
    ?>
    <div class="page-header">
        <div class="breadcrumb"><a href="/admin/">Accueil</a> &rsaquo; Construire</div>
        <h1><i class="fas fa-layer-group page-icon"></i> HUB <span class="page-title-accent">Construire</span></h1>
        <p>Posez les bases solides de votre activité</p>
    </div>

    <style>
        .noah-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 1.25rem;
            margin-top: 1.5rem;
        }
        .noah-card {
            background: var(--bg-card, #1a3c5e);
            border-radius: 14px;
            border: 1.5px solid rgba(255,255,255,.07);
            overflow: hidden;
            transition: box-shadow .2s;
        }
        .noah-card:hover { box-shadow: 0 8px 32px rgba(0,0,0,.3); }
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
        .noah-card-title { font-size: .95rem; font-weight: 700; color: #e8f1f9; margin: 0; }
        .noah-card-sub   { font-size: .78rem; color: #7a9ab8; margin: .15rem 0 0; }
        .noah-card-badge {
            margin-left: auto;
            font-size: .68rem; font-weight: 700;
            background: rgba(201,168,76,.15); color: #c9a84c;
            border: 1px solid rgba(201,168,76,.3);
            border-radius: 20px; padding: .2rem .55rem;
            white-space: nowrap;
        }
        .noah-card-chevron { color: #7a9ab8; font-size: .8rem; transition: transform .2s; margin-left: .5rem; }
        .noah-card.open .noah-card-chevron { transform: rotate(180deg); }

        .noah-form { padding: 0 1.25rem 1.25rem; display: none; }
        .noah-card.open .noah-form { display: block; }

        .noah-field { margin-bottom: .85rem; }
        .noah-label {
            display: block; font-size: .75rem; font-weight: 600;
            color: #a8c4dc; text-transform: uppercase; letter-spacing: .04em;
            margin-bottom: .3rem;
        }
        .noah-input {
            width: 100%; padding: .6rem .85rem;
            background: #0f2237; border: 1.5px solid #2a5278;
            border-radius: 8px; color: #e8f1f9; font-size: .88rem;
            outline: none; transition: border-color .15s;
        }
        .noah-input:focus { border-color: #c9a84c; }
        .noah-btn {
            width: 100%; min-height: 44px; padding: .7rem;
            background: #c9a84c; color: #0f2237;
            border: none; border-radius: 8px;
            font-weight: 700; font-size: .9rem;
            cursor: pointer; margin-top: .25rem;
            display: flex; align-items: center; justify-content: center; gap: .5rem;
            transition: opacity .15s;
        }
        .noah-btn:disabled { opacity: .6; cursor: not-allowed; }
        .noah-result {
            margin-top: 1rem; padding: 1rem;
            background: rgba(15,34,55,.6);
            border: 1px solid rgba(201,168,76,.2);
            border-radius: 10px;
            font-size: .88rem; color: #c8dae9;
            white-space: pre-wrap; line-height: 1.65;
            display: none;
        }
        .noah-result.visible { display: block; }
        .noah-error {
            margin-top: .75rem; padding: .7rem 1rem;
            background: rgba(220,53,69,.12); border: 1px solid rgba(220,53,69,.3);
            border-radius: 8px; font-size: .85rem; color: #f87171;
            display: none;
        }
        .noah-error.visible { display: block; }
        .noah-spinner { animation: spin .8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>

    <div class="noah-grid">

        <?php noahCard('positionnement', 'Méthode ANCRE+ — Positionnement', 'Générez 3 formulations d\'accroche avec Noah', '#e74c3c', '#fdedec', 'fa-anchor', [
            ['name' => 'metier',  'label' => 'Votre métier',       'placeholder' => 'ex : agent immobilier indépendant'],
            ['name' => 'zone',    'label' => 'Zone géographique',  'placeholder' => 'ex : Bordeaux Métropole'],
            ['name' => 'persona', 'label' => 'Type de clients',    'placeholder' => 'ex : primo-accédants 30-45 ans'],
            ['name' => 'objectif','label' => 'Objectif principal', 'placeholder' => 'ex : générer des mandats vendeurs'],
        ]); ?>

        <?php noahCard('profils', 'NeuroPersona — Profils Clients', 'Identifiez vos 3 profils clients prioritaires', '#3498db', '#e3f2fd', 'fa-brain', [
            ['name' => 'activite', 'label' => 'Votre activité', 'placeholder' => 'ex : conseiller en immobilier'],
            ['name' => 'zone',     'label' => 'Zone',           'placeholder' => 'ex : Bordeaux Sud'],
            ['name' => 'objectif', 'label' => 'Objectif',      'placeholder' => 'ex : 3 mandats par mois'],
        ]); ?>

        <?php noahCard('offre', 'Offre Conseiller — Formulation', 'Construisez votre pitch en 3 versions', '#27ae60', '#eafaf1', 'fa-briefcase', [
            ['name' => 'metier',         'label' => 'Votre métier',       'placeholder' => 'ex : agent immobilier'],
            ['name' => 'persona',        'label' => 'Persona ciblé',      'placeholder' => 'ex : vendeurs pressés'],
            ['name' => 'objectif_client','label' => 'Objectif du client', 'placeholder' => 'ex : vendre vite et au bon prix'],
            ['name' => 'points_forts',   'label' => 'Vos points forts',   'placeholder' => 'ex : réactivité, réseau local, photos pro'],
        ]); ?>

        <?php noahCard('zone', 'Zone de Prospection — Stratégie', 'Délimitez votre territoire en 3 niveaux', '#8e44ad', '#f5eef8', 'fa-map-marked-alt', [
            ['name' => 'ville',      'label' => 'Ville principale', 'placeholder' => 'ex : Mérignac'],
            ['name' => 'type_biens', 'label' => 'Type de biens',    'placeholder' => 'ex : maisons 4 pièces'],
            ['name' => 'objectif',   'label' => 'Objectif',         'placeholder' => 'ex : 5 mandats actifs'],
        ]); ?>

        <?php noahCard('synthese', 'Synthèse Stratégique', 'Résumé de votre situation en 100 mots', '#e67e22', '#fef5e7', 'fa-layer-group', [
            ['name' => 'activite',       'label' => 'Votre activité',    'placeholder' => 'ex : agent indépendant depuis 2 ans'],
            ['name' => 'positionnement', 'label' => 'Positionnement',    'placeholder' => 'ex : spécialiste maisons familiales'],
            ['name' => 'persona',        'label' => 'Persona principal', 'placeholder' => 'ex : familles avec enfants'],
            ['name' => 'offre',          'label' => 'Votre offre',       'placeholder' => 'ex : accompagnement complet vendeur'],
            ['name' => 'zone',           'label' => 'Zone',              'placeholder' => 'ex : Mérignac, Pessac, Talence'],
        ]); ?>

        <?php noahCard('actions', 'Actions du Jour', '3 à 5 actions concrètes et mesurables', '#16a085', '#e8f8f5', 'fa-bolt', [
            ['name' => 'experience', 'label' => 'Niveau d\'expérience',  'placeholder' => 'ex : 2 ans en immobilier'],
            ['name' => 'objectif',   'label' => 'Objectif mensuel',      'placeholder' => 'ex : 3 mandats signés'],
            ['name' => 'biens',      'label' => 'Biens en portefeuille', 'placeholder' => 'ex : 8 biens actifs'],
            ['name' => 'activite',   'label' => 'Activité actuelle',     'placeholder' => 'ex : peu de prospection terrain'],
        ]); ?>

    </div>

    <script>
    document.querySelectorAll('.noah-card-header').forEach(header => {
        header.addEventListener('click', () => {
            header.closest('.noah-card').classList.toggle('open');
        });
    });

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

            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 20000);

                const res  = await fetch('/admin/api/noah', {
                    method: 'POST',
                    body: new FormData(form),
                    signal: controller.signal
                });
                clearTimeout(timeoutId);
                const json = await res.json();

                if (json.success) {
                    result.textContent = json.result;
                    result.classList.add('visible');
                } else {
                    errBox.textContent = json.error || 'Une erreur est survenue.';
                    errBox.classList.add('visible');
                }
            } catch (err) {
                errBox.textContent = err.name === 'AbortError'
                    ? 'Le service IA met trop de temps à répondre. Réessayez dans quelques secondes.'
                    : 'Impossible de contacter le serveur.';
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

function noahCard(string $tool, string $title, string $sub, string $color, string $iconBg, string $icon, array $fields): void
{
    ?>
    <div class="noah-card">
        <div class="noah-card-header">
            <div class="noah-card-icon" style="background:<?= $iconBg ?>; color:<?= $color ?>">
                <i class="fas <?= $icon ?>"></i>
            </div>
            <div>
                <div class="noah-card-title"><?= htmlspecialchars($title) ?></div>
                <div class="noah-card-sub"><?= htmlspecialchars($sub) ?></div>
            </div>
            <span class="noah-card-badge">Noah IA</span>
            <i class="fas fa-chevron-down noah-card-chevron"></i>
        </div>
        <form class="noah-form" method="POST">
            <input type="hidden" name="tool" value="<?= htmlspecialchars($tool) ?>">
            <?= csrfField() ?>
            <?php foreach ($fields as $field): ?>
                <?php $inputId = 'noah-' . $tool . '-' . $field['name']; ?>
                <div class="noah-field">
                    <label class="noah-label" for="<?= htmlspecialchars($inputId) ?>"><?= htmlspecialchars($field['label']) ?></label>
                    <input id="<?= htmlspecialchars($inputId) ?>" class="noah-input" type="text" name="<?= htmlspecialchars($field['name']) ?>"
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

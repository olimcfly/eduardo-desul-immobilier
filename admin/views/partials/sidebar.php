<?php
$currentModule = $module ?? 'construire';
$menuGroups = [
    'Pilotage' => [
        ['module' => 'construire', 'label' => 'Construire', 'hint' => 'Poser les bases', 'icon' => 'fas fa-layer-group'],
        ['module' => 'attirer', 'label' => 'Attirer', 'hint' => 'Générer des vendeurs', 'icon' => 'fas fa-bullseye'],
        ['module' => 'capturer', 'label' => 'Capturer', 'hint' => 'Transformer en contacts', 'icon' => 'fas fa-inbox'],
        ['module' => 'convertir', 'label' => 'Convertir', 'hint' => 'Transformer en clients', 'icon' => 'fas fa-arrow-trend-up'],
        ['module' => 'optimiser', 'label' => 'Optimiser', 'hint' => 'Améliorer les résultats', 'icon' => 'fas fa-chart-line'],
    ],
    'Outils' => [
        ['module' => 'assistant', 'label' => 'Assistant IA', 'hint' => 'IA à votre service', 'icon' => 'fas fa-robot'],
        ['module' => 'biens', 'label' => 'Biens', 'hint' => 'Gestion du portefeuille', 'icon' => 'fas fa-house'],
        ['module' => 'gmb', 'label' => 'Google My Business', 'hint' => 'Avis et visibilité', 'icon' => 'fab fa-google'],
        ['module' => 'seo', 'label' => 'SEO', 'hint' => 'Positionnement Google', 'icon' => 'fas fa-magnifying-glass-chart'],
        ['module' => 'social', 'label' => 'Social', 'hint' => 'Publications & réseaux', 'icon' => 'fas fa-share-nodes'],
    ],
    'Compte' => [
        ['module' => 'parametres', 'label' => 'Paramètres', 'hint' => 'Compte et préférences', 'icon' => 'fas fa-gear'],
    ],
];
?>
<nav class="sidebar-nav">
    <ul class="sidebar-menu">
        <li class="nav-section-label">Pilotage</li>

        <li>
            <a href="/admin?module=construire" class="menu-item active" data-module="construire" data-tooltip="Construire">
                <span class="menu-icon"><i class="fas fa-layer-group"></i></span>
                <span class="menu-label">Construire
                    <small class="menu-hint">Poser les bases</small>
                </span>
            </a>
        </li>
        <li>
            <a href="/admin?module=attirer" class="menu-item" data-module="attirer" data-tooltip="Attirer">
                <span class="menu-icon"><i class="fas fa-bullseye"></i></span>
                <span class="menu-label">Attirer
                    <small class="menu-hint">Générer des vendeurs</small>
                </span>
            </a>
        </li>
        <li>
            <a href="/admin?module=capturer" class="menu-item" data-module="capturer" data-tooltip="Capturer">
                <span class="menu-icon"><i class="fas fa-inbox"></i></span>
                <span class="menu-label">Capturer
                    <small class="menu-hint">Transformer en contacts</small>
                </span>
            </a>
        </li>
        <li>
            <a href="/admin?module=convertir" class="menu-item" data-module="convertir" data-tooltip="Convertir">
                <span class="menu-icon"><i class="fas fa-arrow-trend-up"></i></span>
                <span class="menu-label">Convertir
                    <small class="menu-hint">Transformer en clients</small>
                </span>
            </a>
        </li>
        <li>
            <a href="/admin?module=optimiser" class="menu-item" data-module="optimiser" data-tooltip="Optimiser">
                <span class="menu-icon"><i class="fas fa-chart-line"></i></span>
                <span class="menu-label">Optimiser
                    <small class="menu-hint">Améliorer les résultats</small>
                </span>
            </a>
        </li>

        <li class="nav-section-label">Outils</li>

        <li>
            <a href="/admin?module=assistant" class="menu-item" data-module="assistant" data-tooltip="Assistant IA">
                <span class="menu-icon"><i class="fas fa-robot"></i></span>
                <span class="menu-label">Assistant IA
                    <small class="menu-hint">IA à votre service</small>
                </span>
            </a>
        </li>
        <li>
            <a href="/admin?module=biens" class="menu-item" data-module="biens" data-tooltip="Biens">
                <span class="menu-icon"><i class="fas fa-house"></i></span>
                <span class="menu-label">Biens
                    <small class="menu-hint">Gestion du portefeuille</small>
                </span>
            </a>
        </li>
        <li>
            <a href="/admin?module=gmb" class="menu-item" data-module="gmb" data-tooltip="Google My Business">
                <span class="menu-icon"><i class="fab fa-google"></i></span>
                <span class="menu-label">Google My Business
                    <small class="menu-hint">Avis et visibilité</small>
                </span>
            </a>
        </li>
        <li>
            <a href="/admin?module=seo" class="menu-item" data-module="seo" data-tooltip="SEO">
                <span class="menu-icon"><i class="fas fa-magnifying-glass-chart"></i></span>
                <span class="menu-label">SEO
                    <small class="menu-hint">Positionnement Google</small>
                </span>
            </a>
        </li>
        <li>
            <a href="/admin?module=social" class="menu-item" data-module="social" data-tooltip="Social">
                <span class="menu-icon"><i class="fas fa-share-nodes"></i></span>
                <span class="menu-label">Social
                    <small class="menu-hint">Publications & réseaux</small>
                </span>
            </a>
        </li>

        <li class="nav-section-label">Compte</li>

        <li>
            <a href="/admin?module=parametres" class="menu-item" data-module="parametres" data-tooltip="Paramètres">
                <span class="menu-icon"><i class="fas fa-gear"></i></span>
                <span class="menu-label">Paramètres
                    <small class="menu-hint">Compte et préférences</small>
                </span>
            </a>
        </li>

        <?php $authUser = Auth::user(); ?>
        <?php if (($authUser['role'] ?? '') === 'superadmin'): ?>
        <li>
            <a href="/admin?module=superadmin" class="menu-item" data-module="superadmin" data-tooltip="Superadmin">
                <span class="menu-icon"><i class="fas fa-crown"></i></span>
                <span class="menu-label">Superadmin
                    <small class="menu-hint">Modules & accès live</small>
                </span>
            </a>
        </li>
        <?php endif; ?>

    </ul>
</nav>

<?php
$currentModule = $module ?? 'dashboard';
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';

$moduleExists = static function (string $moduleName): bool {
    $modulePath = ROOT_PATH . '/modules/' . $moduleName . '/accueil.php';

    return is_file($modulePath);
};

/**
 * Titres de section = ton conversationnel (« ce que vous faites »).
 * Libellés d’entrée = courts pour éviter la coupure en sidebar étroite ; le title reprend la formulation complète.
 */
$menuGroups = [
    'Tableau de bord' => [
        ['module' => 'dashboard', 'label' => 'Accueil admin', 'title' => 'Vue globale du tableau de bord', 'icon' => 'fas fa-gauge-high'],
        ['module' => 'dashboard', 'label' => 'Vue globale', 'title' => 'Vue globale, statistiques et raccourcis', 'icon' => 'fas fa-chart-pie'],
        ['module' => 'checklist', 'label' => 'Actions prioritaires', 'title' => 'Checklist et actions à traiter', 'icon' => 'fas fa-bolt'],
        ['module' => 'contacts', 'label' => 'Alertes / tâches', 'title' => 'Alertes, contacts et tâches de suivi', 'icon' => 'fas fa-bell'],
    ],
    'Commencer' => [
        ['module' => 'commencer', 'label' => 'Checklist de démarrage', 'title' => 'Configuration essentielle et premiers réglages', 'icon' => 'fas fa-rocket'],
        ['module' => 'commencer', 'label' => 'Configuration essentielle', 'title' => 'Paramétrage de base pour démarrer proprement', 'icon' => 'fas fa-sliders-h'],
        ['module' => 'dashboard', 'label' => 'État du site', 'title' => 'État général du site et de l’administration', 'icon' => 'fas fa-circle-check'],
        ['module' => 'checklist', 'label' => 'Étapes restantes', 'title' => 'Étapes à finir pour un back-office opérationnel', 'icon' => 'fas fa-list-check'],
    ],
    'Stratégie' => [
        ['module' => 'strategie', 'label' => 'Diagnostic', 'title' => 'Diagnostic stratégique et situation actuelle', 'icon' => 'fas fa-stethoscope'],
        ['module' => 'strategie', 'label' => 'Objectifs', 'title' => 'Objectifs de croissance et priorités', 'icon' => 'fas fa-bullseye'],
        ['module' => 'positionnement', 'label' => 'Positionnement', 'title' => 'Positionnement de marché et différenciation', 'icon' => 'fas fa-layer-group'],
        ['module' => 'optimiser', 'label' => 'Plan d’action', 'title' => 'Plan d’action et routines d’exécution', 'icon' => 'fas fa-diagram-project'],
    ],
    'Positionnement & Offre' => [
        ['module' => 'profil', 'label' => 'Mon profil', 'title' => 'Identité, bio et image professionnelle', 'icon' => 'fas fa-user-gear'],
        ['module' => 'positionnement', 'label' => 'Personas', 'title' => 'Personas et cibles prioritaires', 'icon' => 'fas fa-users'],
        ['module' => 'offre', 'label' => 'Promesse', 'title' => 'Promesse commerciale et message principal', 'icon' => 'fas fa-gift'],
        ['module' => 'offre', 'label' => 'Offre principale', 'title' => 'Offre principale et argumentaire', 'icon' => 'fas fa-briefcase'],
        ['module' => 'positionnement', 'label' => 'Différenciation', 'title' => 'Différenciation locale et expertise', 'icon' => 'fas fa-shapes'],
        ['module' => 'convertir', 'label' => 'Scripts commerciaux', 'title' => 'Scripts d’appel, objections et relances', 'icon' => 'fas fa-comments'],
    ],
    'Site & contenus' => [
        ['module' => 'site-contenus', 'label' => 'Site public', 'title' => 'Hub du site public et des contenus', 'icon' => 'fas fa-globe'],
        ['module' => 'cms-hub', 'label' => 'Pages du site', 'title' => 'Pages CMS du site', 'icon' => 'fas fa-file-lines'],
        ['module' => 'secteurs', 'label' => 'Pages secteurs', 'title' => 'Pages secteurs et zones marketing', 'icon' => 'fas fa-map-location-dot'],
        ['module' => 'blog', 'label' => 'Blog / Articles', 'title' => 'Articles de blog et contenus éditoriaux', 'icon' => 'fas fa-pen-fancy'],
        ['module' => 'annuaire-local', 'label' => 'Guides locaux', 'title' => 'Guides locaux, points d’intérêt et annuaire', 'icon' => 'fas fa-book-open'],
        ['module' => 'annuaire-local', 'label' => 'Annuaire local', 'title' => 'Annuaire local et partenaires', 'icon' => 'fas fa-store'],
        ['module' => 'telecharger', 'label' => 'Médias / images', 'title' => 'Bibliothèque médias et fichiers à télécharger', 'icon' => 'fas fa-images'],
    ],
    'Attirer des prospects' => [
        ['module' => 'attirer', 'label' => 'SEO local', 'title' => 'Référencement local et acquisition organique', 'icon' => 'fas fa-magnifying-glass-chart'],
        ['module' => 'gmb', 'label' => 'Google Business Profile', 'title' => 'Fiche Google Business Profile', 'icon' => 'fas fa-map-pin'],
        ['module' => 'gmb', 'label' => 'Publications GMB', 'title' => 'Publications et posts Google Business Profile', 'icon' => 'fas fa-rectangle-list'],
        ['module' => 'seo-spider', 'label' => 'Mots-clés locaux', 'title' => 'Audit SEO technique et mots-clés locaux', 'icon' => 'fas fa-spider'],
        ['module' => 'scraper', 'label' => 'Scraping commerces', 'title' => 'Scraping de commerces et guides locaux', 'icon' => 'fas fa-bug'],
        ['module' => 'attirer', 'label' => 'Référencement local', 'title' => 'Référencement local et visibilité durable', 'icon' => 'fas fa-bullseye'],
    ],
    'Biens immobiliers' => [
        ['module' => 'immobilier', 'label' => 'Liste des biens', 'title' => 'Hub des biens immobiliers', 'icon' => 'fas fa-house'],
        ['module' => 'biens', 'url' => '/admin/biens/nouveau', 'label' => 'Ajouter un bien', 'title' => 'Créer une nouvelle fiche bien', 'icon' => 'fas fa-circle-plus'],
        ['module' => 'biens', 'label' => 'Annonces', 'title' => 'Catalogue et annonces de biens', 'icon' => 'fas fa-list'],
        ['module' => 'redaction', 'label' => 'Générateur d’annonces', 'title' => 'Générateur de textes et annonces', 'icon' => 'fas fa-pen-nib'],
        ['module' => 'landing-pages', 'label' => 'Pages biens', 'title' => 'Landing pages et pages de destination', 'icon' => 'fas fa-location-dot'],
        ['module' => 'telecharger', 'label' => 'Export / diffusion', 'title' => 'Exports, médias et diffusion', 'icon' => 'fas fa-share-nodes'],
    ],
    'Publicité' => [
        ['module' => 'publicite', 'label' => 'Accueil publicité', 'title' => 'Hub publicité et structure des campagnes', 'icon' => 'fas fa-bullhorn'],
        ['module' => 'facebook-ads', 'label' => 'Facebook Ads', 'title' => 'Créer une campagne Facebook depuis un bien', 'icon' => 'fas fa-facebook'],
        ['module' => 'facebook-ads', 'label' => 'Générateur campagne', 'title' => 'Générateur de campagne depuis un bien', 'icon' => 'fas fa-wand-magic-sparkles'],
        ['module' => 'publicites-facebook', 'label' => 'Google Ads', 'title' => 'Hub publicitaire et variantes de diffusion', 'icon' => 'fab fa-google'],
        ['module' => 'publicites-facebook', 'label' => 'Audiences', 'title' => 'Audiences et centres d’intérêt', 'icon' => 'fas fa-users-viewfinder'],
        ['module' => 'generateur-contenu', 'label' => 'Textes publicitaires', 'title' => 'Générateur de contenus publicitaires', 'icon' => 'fas fa-pen-fancy'],
        ['module' => 'ai-help-chat', 'label' => 'Créatifs', 'title' => 'Créatifs, angles et idées visuelles', 'icon' => 'fas fa-image'],
        ['module' => 'optimiser', 'label' => 'Tracking campagne', 'title' => 'Tracking, pixels et mesure', 'icon' => 'fas fa-chart-line'],
    ],
    'Capture & conversion' => [
        ['module' => 'capture', 'label' => 'Estimateur', 'title' => 'Estimateur et capture de leads', 'icon' => 'fas fa-calculator'],
        ['module' => 'landing-pages', 'label' => 'Formulaires', 'title' => 'Formulaires et pages de conversion', 'icon' => 'fas fa-wpforms'],
        ['module' => 'landing-pages', 'label' => 'Landing pages', 'title' => 'Landing pages dédiées à la conversion', 'icon' => 'fas fa-location-dot'],
        ['module' => 'contacts', 'label' => 'Leads entrants', 'title' => 'Leads entrants et contacts qualifiés', 'icon' => 'fas fa-inbox'],
        ['module' => 'convertir', 'label' => 'RDV', 'title' => 'Prise de rendez-vous et conversion', 'icon' => 'fas fa-handshake'],
    ],
    'CRM' => [
        ['module' => 'crm', 'label' => 'Contacts', 'title' => 'Contacts et base relationnelle', 'icon' => 'fas fa-address-book'],
        ['module' => 'crm-hub', 'label' => 'Leads', 'title' => 'Leads et suivi commercial', 'icon' => 'fas fa-user-tag'],
        ['module' => 'crm-hub', 'label' => 'Pipeline', 'title' => 'Pipeline commercial', 'icon' => 'fas fa-route'],
        ['module' => 'messagerie', 'label' => 'Notes d’appel', 'title' => 'Notes d’appel et messages internes', 'icon' => 'fas fa-note-sticky'],
        ['module' => 'crm-hub', 'label' => 'Suivi commercial', 'title' => 'Suivi commercial et état des dossiers', 'icon' => 'fas fa-chart-line'],
        ['module' => 'marketing-hub', 'label' => 'Relances', 'title' => 'Relances et automatisations', 'icon' => 'fas fa-bolt'],
        ['module' => 'crm-hub', 'label' => 'Historique', 'title' => 'Historique des interactions', 'icon' => 'fas fa-clock-rotate-left'],
    ],
    'Prospection' => [
        ['module' => 'prospection', 'label' => 'Import contacts', 'title' => 'Import de contacts et listes de prospection', 'icon' => 'fas fa-file-import'],
        ['module' => 'agents', 'label' => 'Réseaux immobiliers', 'title' => 'Réseaux immobiliers et partenaires', 'icon' => 'fas fa-network-wired'],
        ['module' => 'agents', 'label' => 'Prospection conseillers', 'title' => 'Prospection de conseillers immobiliers', 'icon' => 'fas fa-user-tie'],
        ['module' => 'marketing-hub', 'label' => 'Templates prospection', 'title' => 'Templates de prospection et séquences', 'icon' => 'fas fa-layer-group'],
        ['module' => 'messagerie', 'label' => 'Emails manuels', 'title' => 'Emails manuels et envois ciblés', 'icon' => 'fas fa-envelope-open-text'],
        ['module' => 'marketing-hub', 'label' => 'Séquences prospection', 'title' => 'Séquences de prospection et relances', 'icon' => 'fas fa-diagram-project'],
    ],
    'Email & automatisation' => [
        ['module' => 'email-automatisation', 'label' => 'Campagnes email', 'title' => 'Campagnes email et automation', 'icon' => 'fas fa-paper-plane'],
        ['module' => 'marketing-hub', 'label' => 'Séquences email', 'title' => 'Séquences email et nurturing', 'icon' => 'fas fa-inbox'],
        ['module' => 'messagerie', 'label' => 'Templates email', 'title' => 'Templates email et messages prêts à l’emploi', 'icon' => 'fas fa-envelope'],
        ['module' => 'parametres', 'label' => 'Délivrabilité', 'title' => 'Paramètres de délivrabilité et expéditeur', 'icon' => 'fas fa-shield-halved', 'active_when' => ['module' => 'parametres', 'get' => ['section' => 'smtp']]],
        ['module' => 'parametres', 'label' => 'SMTP', 'title' => 'Configuration SMTP', 'icon' => 'fas fa-server', 'active_when' => ['module' => 'parametres', 'get' => ['section' => 'smtp']]],
        ['module' => 'marketing-hub', 'label' => 'Automatisations', 'title' => 'Automatisations marketing', 'icon' => 'fas fa-robot'],
    ],
    'IA & outils' => [
        ['module' => 'outils', 'label' => 'Assistant IA', 'title' => 'Assistant IA et outils rapides', 'icon' => 'fas fa-robot'],
        ['module' => 'generateur-contenu', 'label' => 'Générateur de contenu', 'title' => 'Générateur de contenu éditorial', 'icon' => 'fas fa-pen-fancy'],
        ['module' => 'gmb', 'label' => 'Générateur GMB', 'title' => 'Générateur pour Google Business Profile', 'icon' => 'fas fa-location-dot'],
        ['module' => 'facebook-ads', 'label' => 'Générateur publicité', 'title' => 'Générateur de publicité depuis un bien', 'icon' => 'fas fa-bullhorn'],
        ['module' => 'ai-help-chat', 'label' => 'Analyse prospect', 'title' => 'Analyse et assistance prospect', 'icon' => 'fas fa-magnifying-glass-chart'],
        ['module' => 'aide', 'label' => 'Prompts / modèles', 'title' => 'Prompts, guides et modèles', 'icon' => 'fas fa-book'],
        ['module' => 'ia-config', 'label' => 'Providers IA', 'title' => 'Configuration des providers IA', 'icon' => 'fas fa-plug'],
    ],
    'Optimisation' => [
        ['module' => 'optimiser', 'label' => 'Analytics', 'title' => 'Analytics et indicateurs de performance', 'icon' => 'fas fa-chart-line'],
        ['module' => 'parametres', 'label' => 'Tracking & pixels', 'title' => 'Tracking, pixels et scripts', 'icon' => 'fas fa-bullseye', 'active_when' => ['module' => 'parametres', 'get' => ['section' => 'tracking']]],
        ['module' => 'seo-spider', 'label' => 'SEO score', 'title' => 'SEO score et audit technique', 'icon' => 'fas fa-spider'],
        ['module' => 'optimiser', 'label' => 'Performance pages', 'title' => 'Performance et vitesse des pages', 'icon' => 'fas fa-tachometer-alt'],
        ['module' => 'optimiser', 'label' => 'Tests', 'title' => 'Tests, AB tests et itérations', 'icon' => 'fas fa-vial'],
        ['module' => 'optimiser', 'label' => 'Logs', 'title' => 'Logs et suivi technique', 'icon' => 'fas fa-file-lines'],
    ],
    'Paramètres' => [
        ['module' => 'compte', 'label' => 'Compte', 'title' => 'Espace compte et réglages globaux', 'icon' => 'fas fa-user-gear'],
        ['module' => 'profil', 'label' => 'Mon profil', 'title' => 'Profil, identité et réseau', 'icon' => 'fas fa-id-card'],
        ['module' => 'site', 'label' => 'Site public', 'title' => 'Réglages du site public', 'icon' => 'fas fa-globe'],
        ['module' => 'parametres', 'label' => 'Zone géographique', 'title' => 'Zone géographique et rayon', 'icon' => 'fas fa-map-location-dot', 'active_when' => ['module' => 'parametres', 'get' => ['section' => 'zone']]],
        ['module' => 'parametres', 'label' => 'Intégrations & API', 'title' => 'Intégrations, API et providers', 'icon' => 'fas fa-plug', 'active_when' => ['module' => 'parametres', 'get' => ['section' => 'api']]],
        ['module' => 'parametres', 'label' => 'Tracking & pixels', 'title' => 'Tracking, pixels et scripts publics', 'icon' => 'fas fa-bullseye', 'active_when' => ['module' => 'parametres', 'get' => ['section' => 'tracking']]],
        ['module' => 'parametres', 'label' => 'Notifications', 'title' => 'Notifications et alertes', 'icon' => 'fas fa-bell', 'active_when' => ['module' => 'parametres', 'get' => ['section' => 'notif']]],
        ['module' => 'parametres', 'label' => 'Email & SMTP', 'title' => 'Expéditeur, serveur et SMTP', 'icon' => 'fas fa-envelope-open-text', 'active_when' => ['module' => 'parametres', 'get' => ['section' => 'smtp']]],
        ['module' => 'telegram', 'label' => 'Bot Telegram', 'title' => 'Configuration du bot Telegram', 'icon' => 'fab fa-telegram'],
        ['module' => 'parametres', 'label' => 'Sécurité', 'title' => 'Sécurité, OTP et sessions', 'icon' => 'fas fa-shield-halved', 'active_when' => ['module' => 'parametres', 'get' => ['section' => 'securite']]],
        ['module' => 'parametres', 'label' => 'Zone de danger', 'title' => 'Désactivation, suppression et risques', 'icon' => 'fas fa-triangle-exclamation', 'active_when' => ['module' => 'parametres', 'get' => ['section' => 'danger']]],
    ],
];

$authUser = Auth::user();
if (($authUser['role'] ?? '') === 'superadmin') {
    $menuGroups['Votre compte'][] = ['module' => 'superadmin', 'label' => 'Superadmin', 'title' => 'Superadmin — modules et accès en direct', 'icon' => 'fas fa-crown'];
}
?>
<nav class="sidebar-nav">
    <ul class="sidebar-menu">
        <?php foreach ($menuGroups as $sectionLabel => $items): ?>
            <?php
            $visibleItems = array_values(array_filter($items, static function (array $item) use ($moduleExists): bool {
                if (isset($item['url'])) {
                    return true;
                }

                return $moduleExists((string) ($item['module'] ?? ''));
            }));

            if ($visibleItems === []) {
                continue;
            }

            $sectionId = 'sidebar-group-' . preg_replace('/[^a-z0-9]+/i', '-', strtolower((string) $sectionLabel));
            $sectionIcon = (string) ($visibleItems[0]['icon'] ?? 'fas fa-folder-open');
            $sectionTitle = (string) ($visibleItems[0]['title'] ?? $sectionLabel);
            ?>
            <li class="sidebar-group" data-sidebar-group="<?= htmlspecialchars($sectionId, ENT_QUOTES, 'UTF-8') ?>">
                <div class="sidebar-group__header">
                    <button type="button"
                            class="sidebar-group__title-btn"
                            aria-controls="<?= htmlspecialchars($sectionId . '-list', ENT_QUOTES, 'UTF-8') ?>"
                            aria-expanded="false"
                            title="<?= htmlspecialchars($sectionTitle, ENT_QUOTES, 'UTF-8') ?>">
                        <span class="sidebar-group__icon"><i class="<?= htmlspecialchars($sectionIcon, ENT_QUOTES, 'UTF-8') ?>"></i></span>
                        <span class="sidebar-group__label"><?= htmlspecialchars($sectionLabel, ENT_QUOTES, 'UTF-8') ?></span>
                    </button>
                    <button type="button"
                            class="sidebar-group__toggle"
                            aria-controls="<?= htmlspecialchars($sectionId . '-list', ENT_QUOTES, 'UTF-8') ?>"
                            aria-expanded="false"
                            aria-label="Afficher ou masquer la section <?= htmlspecialchars($sectionLabel, ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fas fa-chevron-down sidebar-group__chevron" aria-hidden="true"></i>
                    </button>
                </div>

                <ul class="sidebar-group__list" id="<?= htmlspecialchars($sectionId . '-list', ENT_QUOTES, 'UTF-8') ?>">
                    <?php foreach ($visibleItems as $item):
                        $targetUrl = $item['url'] ?? (function_exists('admin_url')
                            ? admin_url(['module' => (string) $item['module']])
                            : (($GLOBALS['admin_query_base'] ?? '/admin/?') . 'module=' . rawurlencode((string) $item['module'])));
                        $aliases = $item['aliases'] ?? [];
                        $isActive = false;
                        if (isset($item['active_when']) && is_array($item['active_when'])) {
                            $aw = $item['active_when'];
                            if (($aw['module'] ?? '') === $currentModule) {
                                $isActive = true;
                                if (!empty($aw['get']) && is_array($aw['get'])) {
                                    foreach ($aw['get'] as $gKey => $gVal) {
                                        $want = (string) $gVal;
                                        $got  = isset($_GET[$gKey]) ? (string) $_GET[$gKey] : '';
                                        /** @see modules/cms/accueil.php : liste « toutes les pages » = st absent ou all */
                                        if ($gKey === 'st' && $want === 'all') {
                                            if ($got !== '' && $got !== 'all') {
                                                $isActive = false;
                                                break;
                                            }
                                        } elseif ($got !== $want) {
                                            $isActive = false;
                                            break;
                                        }
                                    }
                                }
                            }
                        } elseif (isset($item['url'])) {
                            $isActive = (rtrim($currentPath, '/') === rtrim((string) $item['url'], '/'));
                        } else {
                            $isActive = ($currentModule === $item['module'] || in_array($currentModule, $aliases, true));
                        }
                        $tooltip = (string) ($item['title'] ?? $item['label'] ?? '');
                        ?>
                        <li class="sidebar-group__item">
                            <a href="<?= htmlspecialchars($targetUrl, ENT_QUOTES, 'UTF-8') ?>"
                               class="menu-item<?= $isActive ? ' active' : '' ?>"
                               data-module="<?= htmlspecialchars((string) ($item['module'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                               data-tooltip="<?= htmlspecialchars($tooltip, ENT_QUOTES, 'UTF-8') ?>"
                               title="<?= htmlspecialchars($tooltip, ENT_QUOTES, 'UTF-8') ?>">
                                <span class="menu-icon"><i class="<?= htmlspecialchars((string) $item['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span>
                                <span class="menu-label"><?= htmlspecialchars((string) ($item['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>

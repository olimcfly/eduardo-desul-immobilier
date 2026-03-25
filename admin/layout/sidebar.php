<?php
/**
 * SIDEBAR — IMMO LOCAL+
 * /admin/layout/sidebar.php
 */
$activeModule = $activeModule ?? ($_GET['page'] ?? 'dashboard');

// ── Infos conseiller ────────────────────────────────────────
$advisorName = 'Mon espace';
try {
    $r = $pdo->query("SELECT field_value FROM advisor_context WHERE field_key='advisor_name' LIMIT 1")->fetch();
    if ($r) $advisorName = $r['field_value'];
} catch (Exception $e) {}

// ── Structure de navigation ──────────────────────────────────
$sidebarGroups = [
    [
        'id' => 'grp-activity', 'label' => 'Mon activit&eacute;',
        'icon' => 'fa-briefcase', 'color' => '#c9913b',
        'slugs' => ['properties','estimation','rdv','financement','crm','leads','scoring','messagerie','emails'],
        'children' => [
            ['slug'=>'properties',  'icon'=>'fa-house',          'label'=>'Mes biens'],
            ['slug'=>'estimation',  'icon'=>'fa-calculator',     'label'=>'Estimations re&ccedil;ues'],
            ['slug'=>'rdv',         'icon'=>'fa-calendar-check', 'label'=>'Mes rendez-vous'],
            ['slug'=>'financement', 'icon'=>'fa-piggy-bank',     'label'=>'Financement'],
            ['slug'=>'crm',         'icon'=>'fa-address-book',   'label'=>'Mes clients'],
            ['slug'=>'leads',       'icon'=>'fa-user-plus',      'label'=>'Leads entrants'],
            ['slug'=>'scoring',     'icon'=>'fa-star-half-stroke','label'=>'Score prospects'],
            ['slug'=>'messagerie',  'icon'=>'fa-comments',       'label'=>'Messagerie', 'sep'=>true],
            ['slug'=>'emails',      'icon'=>'fa-envelope-open-text','label'=>'Emails automatiques'],
        ],
    ],
    [
        'id' => 'grp-site', 'label' => 'Mon Site',
        'icon' => 'fa-globe', 'color' => '#6366f1',
        'slugs' => ['pages','menus','headers','footers','sections','templates','builder'],
        'children' => [
            ['slug'=>'pages',     'icon'=>'fa-file-lines',       'label'=>'Mes pages'],
            ['slug'=>'menus',     'icon'=>'fa-bars',             'label'=>'Menus'],
            ['slug'=>'headers',   'icon'=>'fa-window-maximize',  'label'=>'Haut de page'],
            ['slug'=>'footers',   'icon'=>'fa-window-minimize',  'label'=>'Bas de page'],
            ['slug'=>'sections',  'icon'=>'fa-cubes',         'label'=>'Mes sections'],
            ['slug'=>'templates', 'icon'=>'fa-layer-group',        'label'=>'Mod&egrave;les de pages'],
            ['slug'=>'builder',   'icon'=>'fa-wand-magic-sparkles','label'=>'&Eacute;diteur de site', 'badge'=>'PRO'],
        ],
    ],
    [
        'id' => 'grp-seo-content', 'label' => 'SEO local &amp; contenu',
        'icon' => 'fa-magnifying-glass', 'color' => '#65a30d',
        'slugs' => ['secteurs','guide-local','articles','journal','ressources','seo-semantic','seo','local-seo','analytics','market-analyzer'],
        'children' => [
            ['slug'=>'secteurs',     'icon'=>'fa-map-pin',          'label'=>'Mes quartiers'],
            ['slug'=>'guide-local',  'icon'=>'fa-map',              'label'=>'Guide du quartier', 'badge'=>'NEW'],
            ['slug'=>'articles',     'icon'=>'fa-newspaper',        'label'=>'Mes articles'],
            ['slug'=>'journal',      'icon'=>'fa-calendar-days',    'label'=>'Planning contenu'],
            ['slug'=>'ressources',   'icon'=>'fa-book-open',        'label'=>'Guides &amp; ressources', 'badge'=>'NEW'],
            ['slug'=>'seo-semantic', 'icon'=>'fa-chart-bar',        'label'=>'Mots-cl&eacute;s &amp; s&eacute;mantique'],
            ['slug'=>'seo',          'icon'=>'fa-magnifying-glass', 'label'=>'Mon r&eacute;f&eacute;rencement'],
            ['slug'=>'local-seo',    'icon'=>'fa-location-dot',     'label'=>'Google My Business'],
            ['slug'=>'analytics',    'icon'=>'fa-chart-line',       'label'=>'Mes statistiques'],
            ['slug'=>'market-analyzer', 'icon'=>'fa-chart-line',    'label'=>'Analyseur March&eacute;', 'badge'=>'NEW'],
        ],
    ],
    [
        'id' => 'grp-acquisition', 'label' => 'Acquisition',
        'icon' => 'fa-bullseye', 'color' => '#dc2626',
        'slugs' => ['captures','sequences','campagnes'],
        'children' => [
            ['slug'=>'captures',  'icon'=>'fa-bolt',           'label'=>'Pages de capture'],
            ['slug'=>'sequences', 'icon'=>'fa-list-check',     'label'=>'S&eacute;quences email'],
            ['slug'=>'campagnes', 'icon'=>'fa-paper-plane',    'label'=>'Campagnes email'],
        ],
    ],
    [
        'id' => 'grp-social', 'label' => 'Mes R&eacute;seaux',
        'icon' => 'fa-share-nodes', 'color' => '#db2777',
        'slugs' => ['reseaux-sociaux','facebook','instagram','linkedin','tiktok','gmb','image-editor','scraper-gmb'],
        'children' => [
            ['slug'=>'reseaux-sociaux','icon'=>'fa-share-nodes',  'label'=>"Vue d'ensemble"],
            ['slug'=>'facebook',       'icon'=>'fab fa-facebook', 'label'=>'Facebook'],
            ['slug'=>'instagram',      'icon'=>'fab fa-instagram','label'=>'Instagram'],
            ['slug'=>'linkedin',       'icon'=>'fab fa-linkedin', 'label'=>'LinkedIn'],
            ['slug'=>'tiktok',         'icon'=>'fab fa-tiktok',   'label'=>'TikTok'],
            ['slug'=>'gmb',            'icon'=>'fab fa-google',   'label'=>'Google My Business'],
            ['slug'=>'image-editor',   'icon'=>'fa-image',        'label'=>"Éditeur d'images IA", 'badge'=>'NEW'],
            ['slug'=>'scraper-gmb',    'icon'=>'fa-binoculars',   'label'=>'Trouver des partenaires'],
        ],
    ],
    [
        'id' => 'grp-plan', 'label' => 'Strat&eacute;gie',
        'icon' => 'fa-rocket', 'color' => '#ea580c',
        'slugs' => ['launchpad','neuropersona','strategy-module','seo-strategie','analyse-marche'],
        'children' => [
            ['slug'=>'launchpad',    'icon'=>'fa-rocket', 'label'=>'Plan de lancement'],
            ['slug'=>'neuropersona', 'icon'=>'fa-brain',  'label'=>'Mon client id&eacute;al'],
            ['slug'=>'seo-strategie','icon'=>'fa-bullhorn','label'=>'SEO strat&eacute;gie', 'badge'=>'SOON'],
            ['slug'=>'analyse-marche','icon'=>'fa-chart-pie','label'=>'Analyse de march&eacute;', 'badge'=>'SOON'],
        ],
    ],
    [
        'id' => 'grp-ia', 'label' => 'Mon IA',
        'icon' => 'fa-microchip', 'color' => '#6366f1',
        'slugs' => ['ai','ai-prompts','agents','advisor-context'],
        'children' => [
            ['slug'=>'ai',              'icon'=>'fa-robot',       'label'=>'Assistant IA'],
            ['slug'=>'ai-prompts',      'icon'=>'fa-scroll',      'label'=>'Mes prompts'],
            ['slug'=>'agents',          'icon'=>'fa-microchip',   'label'=>'Agents automatiques'],
            ['slug'=>'advisor-context', 'icon'=>'fa-user-circle', 'label'=>'Mon profil IA', 'badge'=>'NEW'],
        ],
    ],
    [
        'id' => 'grp-systeme', 'label' => 'Param&egrave;tres / Configuration',
        'icon' => 'fa-gear', 'color' => '#64748b',
        'slugs' => ['modules','settings','maintenance','license','api-keys','ai-settings','users'],
        'children' => array_merge(
            isSuperUser() ? [['slug'=>'users', 'icon'=>'fa-users-gear', 'label'=>'Utilisateurs', 'badge'=>'SU']] : [],
            [
                ['slug'=>'modules',     'icon'=>'fa-puzzle-piece', 'label'=>'Modules &amp; sant&eacute;'],
                ['slug'=>'settings',    'icon'=>'fa-sliders',      'label'=>'Configuration'],
                ['slug'=>'api-keys',    'icon'=>'fa-key',          'label'=>'Cl&eacute;s API'],
                ['slug'=>'ai-settings', 'icon'=>'fa-robot',        'label'=>'Param&egrave;tres AI'],
                ['slug'=>'maintenance', 'icon'=>'fa-wrench',       'label'=>'Maintenance'],
                ['slug'=>'license',     'icon'=>'fa-shield-check', 'label'=>'Ma licence'],
            ]
        ),
    ],
];

// Groupe actif
$autoOpenGroup = '';
foreach ($sidebarGroups as $grp) {
    if (in_array($activeModule, $grp['slugs'])) {
        $autoOpenGroup = $grp['id'];
        break;
    }
}
?>

<style>
/* ===============================================
   SIDEBAR — clic fixe + sections distinctes
=============================================== */

.sb-group-wrap .sb-children {
    max-height: 0;
    overflow: hidden;
    transition: max-height .28s ease, opacity .2s ease;
    opacity: 0;
}

.sb-group-wrap.open .sb-children,
.sb-group-wrap.active-group .sb-children {
    max-height: 600px;
    opacity: 1;
}

.sb-group-wrap.open .sb-group-chevron,
.sb-group-wrap.active-group .sb-group-chevron {
    transform: rotate(90deg);
}
.sb-group-chevron {
    transition: transform .22s ease;
    margin-left: auto;
    font-size: 10px;
    opacity: .45;
}

.sb-group-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    background: none;
    border: none;
    cursor: pointer;
    padding: 7px 12px 7px 10px;
    border-radius: 8px;
    transition: background .18s;
    color: var(--text-1, #f1f5f9);
}
.sb-group-btn:hover {
    background: rgba(255,255,255,.07);
}
.sb-group-btn.has-active {
    color: #fff;
    background: rgba(255,255,255,.06);
}

.sb-group-icon {
    width: 28px; height: 28px;
    border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
}

.sb-group-label {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .3px;
    text-transform: uppercase;
    opacity: .75;
    flex: 1;
    text-align: left;
}
.sb-group-btn.has-active .sb-group-label {
    opacity: 1;
}

.sb-group-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    opacity: 0;
    flex-shrink: 0;
    transition: opacity .2s;
}
.sb-group-btn.has-active .sb-group-dot {
    opacity: 1;
}

/* Separateur entre groupes */
.sb-group-wrap {
    position: relative;
}
.sb-group-wrap + .sb-group-wrap {
    margin-top: 2px;
    padding-top: 2px;
    border-top: 1px solid rgba(255,255,255,.06);
}

/* Separateur visuel plus marque entre blocs logiques */
.sb-group-wrap.grp-sep-top {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid rgba(255,255,255,.12) !important;
}

.sb-children {
    padding: 2px 6px 4px 6px;
}
.sb-item {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 6px 10px;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 600;
    color: #dbe4f2;
    text-decoration: none;
    transition: background .15s, color .15s;
    white-space: nowrap;
    overflow: hidden;
}
.sb-item:hover {
    background: rgba(255,255,255,.1);
    color: #fff;
}
.sb-item.active {
    background: rgba(99,102,241,.26);
    color: #fff;
    font-weight: 700;
}
.sb-item i {
    font-size: 13px;
    width: 16px;
    text-align: center;
    flex-shrink: 0;
    opacity: .7;
}
.sb-item.active i,
.sb-item:hover i {
    opacity: 1;
}
.sb-item.sep-before {
    margin-top: 6px;
    border-top: 1px solid rgba(255,255,255,.07);
    padding-top: 8px;
}

.sb-badge {
    margin-left: auto;
    font-size: 9px;
    font-weight: 700;
    padding: 1px 5px;
    border-radius: 4px;
    letter-spacing: .4px;
    text-transform: uppercase;
    flex-shrink: 0;
}
.sb-badge.new  { background: rgba(101,163,13,.25); color: #86efac; }
.sb-badge.pro  { background: rgba(201,145,59,.25); color: #fcd34d; }
.sb-badge.soon { background: rgba(100,116,139,.25); color: #cbd5e1; }
.sb-badge.su   { background: rgba(99,102,241,.3); color: #a5b4fc; }
</style>

<aside class="sb" id="sidebar">

    <!-- Logo -->
    <div class="sb-logo">
        <a href="?page=dashboard" class="sb-logo-inner">
            <div class="sb-logo-icon"><i class="fas fa-house-chimney"></i></div>
            <div>
                <div class="sb-logo-name">IMMO LOCAL+</div>
                <div class="sb-logo-sub">Ecosyst&egrave;me v<?= defined('IMMO_VERSION') ? IMMO_VERSION : '8.6' ?></div>
            </div>
        </a>
    </div>

    <!-- Dashboard -->
    <a href="?page=dashboard" class="sb-dashboard<?= $activeModule==='dashboard' ? ' active' : '' ?>">
        <i class="fas fa-grid-2"></i>
        <span>Tableau de bord</span>
    </a>

    <!-- Navigation -->
    <nav class="sb-nav" id="sidebarNav">
        <?php
        // Groupes qui commencent un nouveau bloc logique (separateur plus marque)
        $sepGroups = ['grp-site', 'grp-seo-content', 'grp-acquisition', 'grp-social', 'grp-plan', 'grp-ia', 'grp-systeme'];

        foreach ($sidebarGroups as $grp):
            // Filtrer les enfants selon les permissions de l'admin
            $visibleChildren = [];
            foreach ($grp['children'] as $item) {
                if (function_exists('isModuleAllowed') && isModuleAllowed($item['slug'])) {
                    $visibleChildren[] = $item;
                }
            }
            // Si aucun enfant visible, masquer tout le groupe
            if (empty($visibleChildren)) continue;

            $isGroupActive = in_array($activeModule, $grp['slugs']);
            $sepClass = in_array($grp['id'], $sepGroups) ? ' grp-sep-top' : '';
        ?>
        <div class="sb-group-wrap<?= $isGroupActive ? ' active-group' : '' ?><?= $sepClass ?>"
             id="<?= $grp['id'] ?>">

            <button class="sb-group-btn<?= $isGroupActive ? ' has-active' : '' ?>"
                    title="<?= htmlspecialchars(html_entity_decode($grp['label'])) ?>">
                <div class="sb-group-icon"
                     style="background:<?= $grp['color'] ?>22;color:<?= $grp['color'] ?>">
                    <i class="fas <?= $grp['icon'] ?>"></i>
                </div>
                <span class="sb-group-label"><?= $grp['label'] ?></span>
                <div class="sb-group-dot" style="background:<?= $grp['color'] ?>"></div>
                <i class="fas fa-chevron-right sb-group-chevron"></i>
            </button>

            <div class="sb-children">
                <?php foreach ($visibleChildren as $item):
                    $isActive  = ($activeModule === $item['slug']);
                    $iconCls   = str_starts_with($item['icon'], 'fab ') ? $item['icon'] : 'fas '.$item['icon'];
                    $sepCls    = !empty($item['sep']) ? ' sep-before' : '';
                    $badgeHtml = '';
                    if (!empty($item['badge'])) {
                        $cls = strtolower($item['badge']);
                        $badgeHtml = '<span class="sb-badge '.$cls.'">'.$item['badge'].'</span>';
                    }
                ?>
                <a href="?page=<?= $item['slug'] ?>"
                   class="sb-item<?= $isActive ? ' active' : '' ?><?= $sepCls ?>">
                    <i class="<?= $iconCls ?>"></i>
                    <span><?= $item['label'] ?></span>
                    <?= $badgeHtml ?>
                </a>
                <?php endforeach; ?>
            </div>

        </div>
        <?php endforeach; ?>
    </nav>

    <!-- Footer -->
    <div class="sb-footer">
        <a href="?page=advisor-context" class="sb-user">
            <div class="sb-user-avatar" style="<?= isSuperUser() ? 'background:linear-gradient(135deg,#6366f1,#8b5cf6)' : '' ?>"><?= strtoupper(mb_substr($advisorName, 0, 1)) ?></div>
            <div>
                <div class="sb-user-name"><?= htmlspecialchars($advisorName) ?></div>
                <div class="sb-user-role"><?= getRoleLabel() ?></div>
            </div>
            <i class="fas fa-<?= isSuperUser() ? 'crown' : 'gear' ?> sb-user-icon"></i>
        </a>
    </div>

</aside>

<script>
const sidebarGroups = document.querySelectorAll('.sb-group-wrap');
const sidebarOpenKey = 'admin_sidebar_open_groups';
let persistedOpenGroups = [];

try {
    persistedOpenGroups = JSON.parse(localStorage.getItem(sidebarOpenKey) || '[]');
    if (!Array.isArray(persistedOpenGroups)) persistedOpenGroups = [];
} catch (e) {
    persistedOpenGroups = [];
}

sidebarGroups.forEach(group => {
    const button = group.querySelector('.sb-group-btn');
    if (!button) return;

    if (group.classList.contains('active-group') || persistedOpenGroups.includes(group.id)) {
        group.classList.add('open');
    }

    button.addEventListener('click', function() {
        group.classList.toggle('open');

        const openIds = Array.from(sidebarGroups)
            .filter(wrap => wrap.classList.contains('open'))
            .map(wrap => wrap.id);

        localStorage.setItem(sidebarOpenKey, JSON.stringify(openIds));
    });
});

const searchInput = document.getElementById('globalSearch');
if (searchInput) {
    let timer;
    searchInput.addEventListener('input', function() {
        clearTimeout(timer);
        timer = setTimeout(() => {
            const q = this.value.toLowerCase().trim();
            if (!q) {
                document.querySelectorAll('.sb-item, .sb-group-wrap').forEach(el => el.style.display = '');
                return;
            }
            document.querySelectorAll('.sb-group-wrap').forEach(wrap => {
                let hasMatch = false;
                wrap.querySelectorAll('.sb-item').forEach(item => {
                    const match = item.textContent.toLowerCase().includes(q);
                    item.style.display = match ? '' : 'none';
                    if (match) hasMatch = true;
                });
                wrap.style.display = hasMatch ? '' : 'none';
            });
        }, 180);
    });
}
</script>

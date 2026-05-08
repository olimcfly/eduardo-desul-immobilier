<?php
declare(strict_types=1);

require_once dirname(__DIR__, 5) . '/includes/settings.php';

$s = settings_group('api');
$h = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

function api_section_url(string $view): string
{
    $query = ['module' => 'parametres', 'section' => 'api', 'view' => $view];
    if (function_exists('url')) {
        return url('admin/?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986));
    }

    return '/admin/?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
}

function api_page_definitions(): array
{
    return [
        'overview' => [
            'title' => 'Intégrations & API',
            'description' => 'Choisis une intégration pour ouvrir sa sous-page, suivre la configuration et réserver l’emplacement vidéo.',
            'icon' => 'fas fa-plug-circle-bolt',
        ],
        'openai' => [
            'title' => 'OpenAI',
            'description' => 'Clé API utilisée par les assistants, l’IA de rédaction et certaines automatisations.',
            'icon' => 'fas fa-robot',
            'badge' => 'LLM',
            'video' => 'Présentation OpenAI',
            'placeholder' => 'Emplacement réservé pour votre vidéo YouTube OpenAI.',
            'fields' => [
                ['key' => 'api_openai', 'label' => 'Clé API OpenAI', 'type' => 'password', 'placeholder' => 'sk-…', 'help' => 'Utilisée par les fonctions IA du site.'],
            ],
        ],
        'openrouter' => [
            'title' => 'OpenRouter',
            'description' => 'Routeur de modèles LLM pour faire varier les moteurs selon le besoin et le coût.',
            'icon' => 'fas fa-network-wired',
            'badge' => 'Agents IA',
            'video' => 'Présentation OpenRouter',
            'placeholder' => 'Emplacement réservé pour votre vidéo YouTube OpenRouter.',
            'fields' => [
                ['key' => 'api_openrouter', 'label' => 'Clé API OpenRouter', 'type' => 'password', 'placeholder' => 'sk-or-v1-…', 'help' => 'Optionnel si la variable d’environnement est déjà configurée.'],
            ],
        ],
        'google' => [
            'title' => 'Google Maps & PSI',
            'description' => 'Cartographie, géocodage et PageSpeed Insights pour les pages publiques.',
            'icon' => 'fab fa-google',
            'badge' => 'Carto',
            'video' => 'Présentation Google Maps',
            'placeholder' => 'Emplacement réservé pour votre vidéo YouTube Google Maps.',
            'fields' => [
                ['key' => 'api_google_maps', 'label' => 'Clé Google Maps', 'type' => 'password', 'placeholder' => 'AIza…', 'help' => 'Affichage cartes, géocodage et local search.'],
                ['key' => 'api_google_psi', 'label' => 'Clé PageSpeed Insights', 'type' => 'password', 'placeholder' => 'AIza…', 'help' => 'Mesures de performance et audit technique.'],
            ],
        ],
        'gmb' => [
            'title' => 'Google My Business',
            'description' => 'Connexion OAuth pour synchroniser la fiche établissement et les avis.',
            'icon' => 'fas fa-store',
            'badge' => 'Local',
            'video' => 'Présentation Google Business Profile',
            'placeholder' => 'Emplacement réservé pour votre vidéo YouTube GMB.',
            'fields' => [
                ['key' => 'api_gmb_client_id', 'label' => 'Client ID OAuth', 'type' => 'text', 'placeholder' => 'XXXX.apps.googleusercontent.com', 'help' => 'Identifiant OAuth Google.'],
                ['key' => 'api_gmb_client_secret', 'label' => 'Client Secret OAuth', 'type' => 'password', 'placeholder' => 'GOCSPX-…', 'help' => 'Secret OAuth associé au client ID.'],
                ['key' => 'api_gmb_account_id', 'label' => 'Account ID', 'type' => 'text', 'placeholder' => 'accounts/123456789', 'help' => 'Compte Google Business Profile.'],
            ],
        ],
        'social' => [
            'title' => 'Facebook & Instagram',
            'description' => 'Pages Meta utilisées pour publier, synchroniser ou remonter des contenus.',
            'icon' => 'fab fa-facebook',
            'badge' => 'Social',
            'video' => 'Présentation Meta',
            'placeholder' => 'Emplacement réservé pour votre vidéo YouTube Meta.',
            'fields' => [
                ['key' => 'api_fb_page_id', 'label' => 'Page ID Facebook', 'type' => 'text', 'placeholder' => '123456789', 'help' => 'Identifiant de la page Facebook.'],
                ['key' => 'api_instagram_id', 'label' => 'Instagram Account ID', 'type' => 'text', 'placeholder' => '17841…', 'help' => 'Compte Instagram lié à la page.'],
                ['key' => 'api_fb_access_token', 'label' => 'Access Token permanent', 'type' => 'password', 'placeholder' => 'EAAB…', 'help' => 'Jeton long terme Meta.'],
            ],
        ],
        'cloudinary' => [
            'title' => 'Cloudinary',
            'description' => 'Stockage média, optimisation des images et transformations à la volée.',
            'icon' => 'fas fa-cloud-arrow-up',
            'badge' => 'Médias',
            'video' => 'Présentation Cloudinary',
            'placeholder' => 'Emplacement réservé pour votre vidéo YouTube Cloudinary.',
            'fields' => [
                ['key' => 'api_cloudinary_name', 'label' => 'Cloud Name', 'type' => 'text', 'placeholder' => 'my-cloud', 'help' => 'Nom du cloud Cloudinary.'],
                ['key' => 'api_cloudinary_key', 'label' => 'API Key', 'type' => 'text', 'placeholder' => '1234567890', 'help' => 'Clé publique Cloudinary.'],
                ['key' => 'api_cloudinary_secret', 'label' => 'API Secret', 'type' => 'password', 'placeholder' => '••••••••', 'help' => 'Secret Cloudinary à garder privé.'],
            ],
        ],
        'gsc' => [
            'title' => 'Google Search Console',
            'description' => 'OAuth2 pour connecter le site à la Search Console et suivre l’indexation.',
            'icon' => 'fas fa-chart-line',
            'badge' => 'SEO',
            'video' => 'Présentation Search Console',
            'placeholder' => 'Emplacement réservé pour votre vidéo YouTube GSC.',
            'special' => 'gsc',
        ],
        'dataforseo' => [
            'title' => 'DataForSEO',
            'description' => 'Authentification login + mot de passe pour les requêtes SERP et analyses SEO.',
            'icon' => 'fas fa-magnifying-glass-chart',
            'badge' => 'SEO API',
            'video' => 'Présentation DataForSEO',
            'placeholder' => 'Emplacement réservé pour votre vidéo YouTube DataForSEO.',
            'fields' => [
                ['key' => 'api_dataforseo_login', 'label' => 'Login (email)', 'type' => 'email', 'placeholder' => 'vous@email.com', 'help' => 'Identifiant DataForSEO.'],
                ['key' => 'api_dataforseo_password', 'label' => 'Mot de passe API', 'type' => 'password', 'placeholder' => 'Mot de passe API…', 'help' => 'Mot de passe Basic Auth DataForSEO.'],
            ],
        ],
        'perplexity' => [
            'title' => 'Perplexity',
            'description' => 'Clé API Perplexity pour les usages IA et les recherches conversationnelles.',
            'icon' => 'fas fa-wand-magic-sparkles',
            'badge' => 'Nouveau',
            'video' => 'Présentation Perplexity',
            'placeholder' => 'Emplacement réservé pour votre vidéo YouTube Perplexity.',
            'fields' => [
                ['key' => 'api_perplexity_key', 'label' => 'Clé API Perplexity', 'type' => 'password', 'placeholder' => 'pplx-…', 'help' => 'Clé publique pour les usages Perplexity.'],
            ],
        ],
    ];
}

function api_render_video_placeholder(array $page): void
{
    ?>
    <div class="api-video-frame">
        <div class="api-video-thumb">
            <div class="api-video-play"><i class="fas fa-play"></i></div>
            <div class="api-video-caption">
                <strong><?= htmlspecialchars((string) ($page['video'] ?? 'Vidéo explicative'), ENT_QUOTES, 'UTF-8') ?></strong>
                <span><?= htmlspecialchars((string) ($page['placeholder'] ?? 'Emplacement réservé pour une vidéo YouTube.'), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
        <div class="api-video-copy">
            <p><strong>Vidéo YouTube à intégrer plus tard.</strong></p>
            <p>Le bloc ci-contre sert de placeholder temporaire. Vous pourrez y coller votre iframe YouTube ou remplacer cette carte par un slide dédié à la formation.</p>
        </div>
    </div>
    <?php
}

function api_render_field(array $field, array $values): void
{
    $key = (string) ($field['key'] ?? '');
    $label = (string) ($field['label'] ?? $key);
    $type = (string) ($field['type'] ?? 'text');
    $placeholder = (string) ($field['placeholder'] ?? '');
    $help = (string) ($field['help'] ?? '');
    $value = htmlspecialchars((string) ($values[$key] ?? ''), ENT_QUOTES, 'UTF-8');
    ?>
    <div class="form-group">
        <label><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></label>
        <div class="api-key-row">
            <input type="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>"
                   name="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                   value="<?= $value ?>"
                   placeholder="<?= htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') ?>">
            <?php if ($type === 'password'): ?>
                <button type="button" class="api-key-toggle"><i class="fas fa-eye"></i></button>
            <?php endif; ?>
        </div>
        <?php if ($help !== ''): ?>
            <div class="label-hint" style="display:block;margin-top:6px"><?= htmlspecialchars($help, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
    </div>
    <?php
}

function api_render_overview(array $pages): void
{
    ?>
    <div class="api-hero">
        <div>
            <p class="api-hero-kicker">Sous-pages par intégration</p>
            <h1>Intégrations & API</h1>
            <p class="api-hero-text">Chaque intégration a désormais sa propre sous-page, avec un emplacement vidéo YouTube réservé et une configuration séparée.</p>
        </div>
        <div class="api-hero-note">
            <strong>À faire plus tard</strong>
            <span>Vous pourrez remplacer les placeholders vidéo par vos liens YouTube ou un slide de formation.</span>
        </div>
    </div>

    <div class="api-carousel">
        <?php foreach ($pages as $slug => $page): ?>
            <?php if ($slug === 'overview') continue; ?>
            <a class="api-card" href="<?= htmlspecialchars(api_section_url($slug), ENT_QUOTES, 'UTF-8') ?>">
                <div class="api-card__icon"><i class="<?= htmlspecialchars((string) ($page['icon'] ?? 'fas fa-plug'), ENT_QUOTES, 'UTF-8') ?>"></i></div>
                <div class="api-card__body">
                    <div class="api-card__title"><?= htmlspecialchars((string) ($page['title'] ?? $slug), ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="api-card__desc"><?= htmlspecialchars((string) ($page['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="api-card__badge"><?= htmlspecialchars((string) ($page['badge'] ?? 'API'), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <div class="api-card__cta"><i class="fas fa-chevron-right"></i></div>
            </a>
        <?php endforeach; ?>
    </div>
    <?php
}

function api_render_form_page(string $view, array $page, array $pages, array $values): void
{
    $title = (string) ($page['title'] ?? 'API');
    $description = (string) ($page['description'] ?? '');
    ?>
    <div class="page-header">
        <div class="page-header__top">
            <div>
                <h1><i class="<?= htmlspecialchars((string) ($page['icon'] ?? 'fas fa-plug'), ENT_QUOTES, 'UTF-8') ?> page-icon"></i> <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="page-description"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <a class="settings-back-link" href="<?= htmlspecialchars(api_section_url('overview'), ENT_QUOTES, 'UTF-8') ?>">
                <i class="fas fa-layer-group"></i> Retour au hub API
            </a>
        </div>
    </div>

    <div class="settings-subnav api-subnav" aria-label="Sous-sections API">
        <?php foreach ($pages as $slug => $meta): ?>
            <?php if ($slug === 'overview') continue; ?>
            <?php if ($slug === $view): ?>
                <span class="settings-subnav__pill settings-subnav__pill--active"><?= htmlspecialchars((string) ($meta['title'] ?? $slug), ENT_QUOTES, 'UTF-8') ?></span>
            <?php else: ?>
                <a class="settings-subnav__pill" href="<?= htmlspecialchars(api_section_url($slug), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) ($meta['title'] ?? $slug), ENT_QUOTES, 'UTF-8') ?></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="settings-intro api-intro">
        <div class="settings-intro__text">
            <strong>Configuration dédiée.</strong>
            Cette sous-page regroupe uniquement l’intégration choisie, avec son aide vidéo et ses champs de configuration.
        </div>
        <div class="settings-intro__meta">
            <span class="settings-chip"><?= htmlspecialchars((string) ($page['badge'] ?? 'API'), ENT_QUOTES, 'UTF-8') ?></span>
            <span class="settings-chip settings-chip--muted">Vidéo placeholder prête</span>
        </div>
    </div>

    <?php api_render_video_placeholder($page); ?>

    <div class="settings-section-shell">
        <?php if ($view === 'gsc'): ?>
            <?php
            $gscConnected = !empty($values['api_gsc_refresh_token']);
            $redirectUri  = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
                          . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
                          . '/admin/api/settings/gsc-callback.php';
            ?>
            <div class="api-help-banner">
                <i class="fas fa-circle-info"></i>
                <div>
                    <strong>Configuration OAuth2 requise</strong><br>
                    <span>
                        1. <a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener">console.cloud.google.com</a>
                        → Créer un ID client OAuth (application Web)<br>
                        2. Ajouter l'URI de redirection autorisée :
                        <code><?= htmlspecialchars($redirectUri, ENT_QUOTES, 'UTF-8') ?></code><br>
                        3. Activer l’API Search Console dans la bibliothèque.
                    </span>
                </div>
            </div>

            <div class="gsc-oauth-status">
                <span class="oauth-badge <?= $gscConnected ? 'badge-ok' : 'badge-off' ?>">
                    <i class="<?= $gscConnected ? 'fas fa-check-circle' : 'fas fa-circle-xmark' ?>"></i>
                    <?= $gscConnected ? 'Compte connecté' : 'Non connecté' ?>
                </span>
                <?php if ($gscConnected): ?>
                    <a href="/admin/api/settings/gsc-callback.php?action=revoke"
                       class="btn-oauth btn-revoke"
                       onclick="return confirm('Révoquer la connexion GSC ?')">
                        <i class="fas fa-unlink"></i> Déconnecter
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($gscConnected): ?>
                <form class="settings-form" method="post" style="margin-top:1rem">
                    <input type="hidden" name="section" value="api">
                    <div class="form-group">
                        <label>Site URL dans GSC <span class="label-hint">ex: sc-domain:monsite.fr</span></label>
                        <input type="text" name="api_gsc_site_url"
                               value="<?= htmlspecialchars((string) ($values['api_gsc_site_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="sc-domain:monsite.fr">
                    </div>
                    <button type="submit" class="btn-oauth btn-connect" style="margin-top:.5rem">
                        <i class="fas fa-save"></i> Mettre à jour l’URL
                    </button>
                </form>
            <?php else: ?>
                <form class="settings-form" method="post">
                    <input type="hidden" name="section" value="api">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Client ID OAuth</label>
                            <input type="text" name="api_gsc_client_id"
                                   value="<?= htmlspecialchars((string) ($values['api_gsc_client_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                   placeholder="XXXX.apps.googleusercontent.com">
                        </div>
                        <div class="form-group">
                            <label>Client Secret</label>
                            <div class="api-key-row">
                                <input type="password" name="api_gsc_client_secret"
                                       value="<?= htmlspecialchars((string) ($values['api_gsc_client_secret'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                       placeholder="GOCSPX-…">
                                <button type="button" class="api-key-toggle"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Site URL dans GSC <span class="label-hint">ex: sc-domain:monsite.fr</span></label>
                        <input type="text" name="api_gsc_site_url"
                               value="<?= htmlspecialchars((string) ($values['api_gsc_site_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="sc-domain:monsite.fr">
                    </div>

                    <div style="display:flex;gap:1rem;align-items:center;margin-top:.75rem;flex-wrap:wrap">
                        <button type="submit" class="btn-oauth btn-test">
                            <i class="fas fa-save"></i> Enregistrer les credentials
                        </button>
                        <a href="/admin/api/settings/gsc-auth.php"
                           class="btn-oauth btn-connect"
                           id="btn-gsc-connect">
                            <i class="fab fa-google"></i> Connecter Google
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        <?php elseif ($view === 'dataforseo'): ?>
            <form class="settings-form" method="post">
                <input type="hidden" name="section" value="api">
                <?php foreach ($page['fields'] as $field) api_render_field($field, $values); ?>
                <div class="form-group">
                    <button type="button" class="btn-oauth btn-test" id="btn-dfs-test" onclick="testDataForSEO()">
                        <i class="fas fa-plug"></i> Tester la connexion
                    </button>
                    <span id="dfs-test-result" style="margin-left:10px;font-size:.85rem"></span>
                </div>
                <div class="drawer-footer">
                    <button type="button" class="btn-cancel" onclick="window.location.href='<?= htmlspecialchars(api_section_url('overview'), ENT_QUOTES, 'UTF-8') ?>'">Annuler</button>
                    <button type="submit" class="btn-save"><i class="fas fa-check"></i> Enregistrer</button>
                </div>
            </form>
        <?php else: ?>
            <form class="settings-form" method="post">
                <input type="hidden" name="section" value="api">
                <?php foreach (($page['fields'] ?? []) as $field) api_render_field($field, $values); ?>
                <div class="drawer-footer">
                    <button type="button" class="btn-cancel" onclick="window.location.href='<?= htmlspecialchars(api_section_url('overview'), ENT_QUOTES, 'UTF-8') ?>'">Annuler</button>
                    <button type="submit" class="btn-save"><i class="fas fa-check"></i> Enregistrer</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
    <?php
}

$pages = api_page_definitions();
$view = strtolower((string) ($_GET['view'] ?? 'overview'));
$view = preg_replace('/[^a-z_]/', '', $view) ?: 'overview';
if (!isset($pages[$view])) {
    $view = 'overview';
}

if ($view === 'overview') {
    api_render_overview($pages);
} else {
    api_render_form_page($view, $pages[$view], $pages, $s);
}
?>

<style>
.api-hero {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    align-items: flex-end;
    margin-bottom: 18px;
    flex-wrap: wrap;
}
.api-hero-kicker {
    text-transform: uppercase;
    letter-spacing: .08em;
    font-size: 11px;
    font-weight: 800;
    color: #7c3aed;
    margin-bottom: 6px;
}
.api-hero h1 {
    font-size: 28px;
    margin: 0;
    color: #1a2332;
}
.api-hero-text {
    max-width: 720px;
    color: #64748b;
    margin-top: 8px;
    line-height: 1.6;
}
.api-hero-note {
    min-width: 240px;
    background: linear-gradient(135deg, #f8fafc, #eef2ff);
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 14px 16px;
    color: #334155;
}
.api-hero-note strong {
    display: block;
    margin-bottom: 6px;
    color: #1a2332;
}
.api-carousel {
    display: grid;
    grid-auto-flow: column;
    grid-auto-columns: minmax(280px, 1fr);
    gap: 14px;
    overflow-x: auto;
    padding-bottom: 8px;
    scroll-snap-type: x mandatory;
}
.api-card {
    scroll-snap-align: start;
    display: flex;
    gap: 14px;
    align-items: center;
    background: #fff;
    border: 1px solid #e8ecf0;
    border-radius: 16px;
    padding: 18px;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 6px 20px rgba(15, 23, 42, .04);
    transition: transform .15s, box-shadow .15s, border-color .15s;
}
.api-card:hover {
    transform: translateY(-2px);
    border-color: #7c3aed;
    box-shadow: 0 12px 28px rgba(124, 58, 237, .12);
}
.api-card__icon {
    width: 54px;
    height: 54px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #eef2ff, #f3e8ff);
    color: #6d28d9;
    font-size: 20px;
    flex-shrink: 0;
}
.api-card__body { flex: 1; }
.api-card__title { font-weight: 800; color: #1a2332; margin-bottom: 4px; }
.api-card__desc { font-size: 12px; color: #64748b; line-height: 1.5; }
.api-card__badge {
    display: inline-flex;
    margin-top: 8px;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .04em;
    background: #eef2ff;
    color: #4338ca;
}
.api-card__cta { color: #cbd5e1; }
.api-subnav { margin-top: 6px; }
.api-video-frame {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(260px, .85fr);
    gap: 18px;
    align-items: stretch;
    margin-bottom: 18px;
}
.api-video-thumb {
    min-height: 250px;
    border-radius: 18px;
    background:
        radial-gradient(circle at top left, rgba(255,255,255,.25), transparent 35%),
        linear-gradient(135deg, #0f172a, #1e293b 60%, #312e81);
    position: relative;
    overflow: hidden;
    padding: 24px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    color: #fff;
}
.api-video-thumb::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 0, rgba(0,0,0,.18) 100%);
}
.api-video-play {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.22);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    position: relative;
    z-index: 1;
}
.api-video-caption {
    position: relative;
    z-index: 1;
}
.api-video-caption strong {
    display: block;
    font-size: 18px;
    margin-bottom: 6px;
}
.api-video-caption span {
    font-size: 13px;
    color: rgba(255,255,255,.8);
    line-height: 1.6;
}
.api-video-copy {
    background: linear-gradient(180deg, #fff, #f8fafc);
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 22px;
    color: #334155;
    line-height: 1.65;
}
.api-video-copy p:first-child { margin-top: 0; }
.api-video-copy p:last-child { margin-bottom: 0; }
@media (max-width: 900px) {
    .api-video-frame { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .api-carousel { grid-auto-flow: row; grid-auto-columns: 1fr; overflow-x: visible; }
    .api-card { align-items: flex-start; }
}
</style>

<script>
function testDataForSEO() {
    const btn    = document.getElementById('btn-dfs-test');
    const result = document.getElementById('dfs-test-result');
    const login  = document.querySelector('[name="api_dataforseo_login"]').value.trim();
    const pass   = document.querySelector('[name="api_dataforseo_password"]').value.trim();

    if (!login || !pass) {
        result.innerHTML = '<span style="color:#dc2626">⚠️ Remplis login + mot de passe d\'abord.</span>';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Test…';
    result.innerHTML = '';

    fetch('/admin/api/settings/dataforseo-test.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ login, password: pass }),
    })
    .then(r => r.json())
    .then(d => {
        result.innerHTML = d.success
            ? `<span style="color:#16a34a"><i class="fas fa-check-circle"></i> Connecté — Solde : ${d.balance ?? '?'}$</span>`
            : `<span style="color:#dc2626"><i class="fas fa-circle-xmark"></i> ${d.error ?? 'Erreur'}</span>`;
    })
    .catch(() => {
        result.innerHTML = '<span style="color:#dc2626">Erreur réseau</span>';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plug"></i> Tester la connexion';
    });
}
</script>

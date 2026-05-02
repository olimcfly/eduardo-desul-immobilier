<?php
declare(strict_types=1);

/**
 * Admin : édition des textes page_contents (registre config/page_content_registry.php).
 * Mise en page alignée sur l’édition Accueil (slug=home) : cms-card, cms-editor-layout, panneau SEO.
 */

function cms_page_text_load_registry(): array
{
    if (!function_exists('page_content_registry')) {
        require_once ROOT_PATH . '/core/helpers/cms.php';
    }
    require_once ROOT_PATH . '/core/services/PageContentService.php';

    return page_content_registry();
}

/** @return string|null message d'erreur ou null si OK */
function cms_page_text_try_save_post(): ?string
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return null;
    }
    if ((string) ($_POST['cms_page_text_action'] ?? '') !== 'save') {
        return null;
    }
    if (function_exists('csrfToken')) {
        $t = (string) ($_POST['csrf_token'] ?? '');
        if (!hash_equals((string) csrfToken(), $t)) {
            return 'Jeton CSRF invalide.';
        }
    }

    $key = preg_replace('/[^a-z0-9-]/', '', (string) ($_POST['cms_page_key'] ?? ''));
    $registry = cms_page_text_load_registry();
    if ($key === '' || !isset($registry[$key])) {
        return 'Page inconnue ou identifiant invalide.';
    }

    $raw = $_POST['sections'] ?? [];
    if (!is_array($raw)) {
        return 'Données invalides.';
    }

    $clean = [];
    foreach ($raw as $sec => $fields) {
        if (!is_array($fields)) {
            continue;
        }
        $secKey = preg_replace('/[^a-z0-9_-]/', '', (string) $sec);
        if ($secKey === '') {
            continue;
        }
        foreach ($fields as $fname => $fval) {
            $fk = preg_replace('/[^a-z0-9_-]/', '', (string) $fname);
            if ($fk === '') {
                continue;
            }
            $clean[$secKey][$fk] = is_scalar($fval) || $fval === null
                ? (string) $fval
                : '';
        }
    }

    PageContentService::savePageContent($key, $clean);

    return null;
}

/**
 * @param array<string, mixed> $pageDef entrée du registre pour cette page
 * @param array{saved?: bool, error?: string} $flash
 */
function cms_page_text_render_editor(string $pageKey, array $pageDef, array $flash = []): void
{
    $registry = cms_page_text_load_registry();
    if (!isset($registry[$pageKey])) {
        echo '<div class="error">Page introuvable.</div>';

        return;
    }

    $sectionsDef = $pageDef['sections'] ?? [];
    if (!is_array($sectionsDef)) {
        $sectionsDef = [];
    }

    PageContentService::ensureDefaults($pageKey, $sectionsDef);
    $saved = PageContentService::getPageContent($pageKey);

    $label = (string) ($pageDef['label'] ?? $pageKey);
    $template = (string) ($pageDef['template'] ?? '');
    $routeSlug = $pageDef['route_slug'] ?? null;

    $seoSection = null;
    $contentSections = [];
    foreach ($sectionsDef as $sk => $scfg) {
        if ($sk === 'seo' && is_array($scfg)) {
            $seoSection = $scfg;
        } elseif (is_array($scfg)) {
            $contentSections[$sk] = $scfg;
        }
    }
    $hasSeoRail = is_array($seoSection)
        && isset($seoSection['fields'])
        && is_array($seoSection['fields'])
        && $seoSection['fields'] !== [];

    $layoutClass = 'cms-editor-layout' . ($hasSeoRail ? '' : ' cms-editor-layout--single');

    $rteNeeded = false;

    $seoInputId = static function (string $fieldKey): string {
        if ($fieldKey === 'page_title') {
            return 'ptext-meta-title';
        }
        if ($fieldKey === 'meta_description') {
            return 'ptext-meta-desc';
        }

        return 'ptext-seo-' . preg_replace('/[^a-z0-9_-]/', '-', $fieldKey);
    };

    $renderFields = function (
        array $fields,
        string $sectionKey,
        array $saved,
        string $context,
        callable $seoInputId
    ) use (&$rteNeeded): void {
        foreach ($fields as $fieldKey => $fieldCfg) {
            if (!is_array($fieldCfg)) {
                continue;
            }
            $type = (string) ($fieldCfg['type'] ?? 'text');
            $flabel = (string) ($fieldCfg['label'] ?? $fieldKey);
            $default = (string) ($fieldCfg['default'] ?? '');
            $cur = $saved[$sectionKey][$fieldKey] ?? null;
            $value = ($cur !== null && $cur !== '') ? $cur : $default;
            $nameS = htmlspecialchars((string) $sectionKey, ENT_QUOTES, 'UTF-8');
            $nameF = htmlspecialchars((string) $fieldKey, ENT_QUOTES, 'UTF-8');
            $fid = $context === 'rail' ? $seoInputId((string) $fieldKey) : htmlspecialchars($sectionKey . '_' . $fieldKey, ENT_QUOTES, 'UTF-8');

            $editorPref = (string) ($fieldCfg['editor'] ?? '');
            $useRte = $editorPref !== 'plain'
                && (
                    $type === 'richtext'
                    || ($type === 'textarea' && $sectionKey !== 'seo' && $context === 'main')
                );
            if ($useRte) {
                $rteNeeded = true;
            }
            $taClass = $useRte ? ' class="cms-rte"' : '';

            if ($context === 'rail') {
                echo '<div class="cms-seo-field">';
                echo '<label for="' . htmlspecialchars($fid, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($flabel, ENT_QUOTES, 'UTF-8') . '</label>';
                if ($type === 'textarea' || $type === 'richtext') {
                    echo '<textarea id="' . htmlspecialchars($fid, ENT_QUOTES, 'UTF-8') . '"' . $taClass . ' name="sections[' . $nameS . '][' . $nameF . ']" rows="' . ($type === 'richtext' ? '14' : '6') . '">' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</textarea>';
                } else {
                    echo '<input id="' . htmlspecialchars($fid, ENT_QUOTES, 'UTF-8') . '" type="text" name="sections[' . $nameS . '][' . $nameF . ']" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '">';
                }
                echo '</div>';
            } else {
                echo '<div class="cms-field-block">';
                echo '<label for="' . $fid . '">' . htmlspecialchars($flabel, ENT_QUOTES, 'UTF-8') . '</label>';
                if ($type === 'textarea' || $type === 'richtext') {
                    echo '<textarea id="' . $fid . '"' . $taClass . ' name="sections[' . $nameS . '][' . $nameF . ']" rows="' . ($type === 'richtext' ? '14' : '6') . '">' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</textarea>';
                } else {
                    echo '<input id="' . $fid . '" type="text" name="sections[' . $nameS . '][' . $nameF . ']" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '">';
                }
                echo '</div>';
            }
        }
    };

    $showSaved = !empty($flash['saved']);
    $flashError = trim((string) ($flash['error'] ?? ''));

    ?>
    <div class="cms-card">
        <h2>Édition des textes — <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></h2>
        <?php if (!$hasSeoRail && $template !== ''): ?>
            <div class="cms-editor-template-fallback">
                <div class="cms-seo-metrics">
                    <div class="cms-seo-metric cms-seo-metric--full">
                        <span>Modèle utilisé (gabarit)</span>
                        <strong class="cms-seo-metric-path"><?= htmlspecialchars($template, ENT_QUOTES, 'UTF-8') ?>.php</strong>
                        <span class="cms-seo-metric-sub">
                            <?php if (is_string($routeSlug) && $routeSlug !== ''): ?>
                                URL <code>/<?= htmlspecialchars($routeSlug, ENT_QUOTES, 'UTF-8') ?></code> · données <code>page_contents</code>.
                            <?php else: ?>
                                Textes en base (<code>page_contents</code>) · fichier sous <code>public/</code>.
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($showSaved): ?>
            <div class="notice">Modifications enregistrées.</div>
        <?php endif; ?>
        <?php if ($flashError !== ''): ?>
            <div class="error"><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="post" class="cms-form">
            <?php if (function_exists('csrfField')): ?>
                <?= csrfField() ?>
            <?php endif; ?>
            <input type="hidden" name="cms_page_text_action" value="save">
            <input type="hidden" name="cms_page_key" value="<?= htmlspecialchars($pageKey, ENT_QUOTES, 'UTF-8') ?>">

            <div class="<?= $layoutClass ?>">
                <div class="cms-editor-main">
                    <?php if ($contentSections === []): ?>
                        <section class="cms-section">
                            <h3>Contenu</h3>
                            <p style="margin:0;color:#64748b;font-size:14px;">Aucun bloc « contenu » détecté pour ce gabarit (SEO seul ou registre à compléter).</p>
                        </section>
                    <?php endif; ?>
                    <?php foreach ($contentSections as $sectionKey => $sectionCfg): ?>
                        <?php
                        if (!is_array($sectionCfg)) {
                            continue;
                        }
                        $fields = $sectionCfg['fields'] ?? [];
                        if (!is_array($fields) || $fields === []) {
                            continue;
                        }
                        $sectionTitle = (string) ($sectionCfg['title'] ?? $sectionKey);
                        ?>
                        <section class="cms-section">
                            <h3><?= htmlspecialchars($sectionTitle, ENT_QUOTES, 'UTF-8') ?></h3>
                            <?php $renderFields($fields, (string) $sectionKey, $saved, 'main', $seoInputId); ?>
                        </section>
                    <?php endforeach; ?>
                </div>

                <?php if ($hasSeoRail): ?>
                    <div class="cms-editor-rail">
                        <aside class="cms-seo-panel" aria-labelledby="ptext-seo-heading">
                            <div class="cms-seo-panel__head">
                                <h3 id="ptext-seo-heading">Assistant SEO</h3>
                                <span class="cms-seo-panel__badge">Live</span>
                            </div>
                            <div class="cms-seo-score">
                                <div class="cms-seo-score-badge" id="ptext-seo-score-badge">0</div>
                                <div class="cms-seo-score-text">
                                    <span class="cms-seo-score-label">Score</span>
                                    <strong id="ptext-seo-score-label">À optimiser</strong>
                                </div>
                            </div>
                            <div class="cms-seo-metrics">
                                <div class="cms-seo-metric">
                                    <span>Meta title</span>
                                    <strong id="ptext-seo-title-count">0</strong>
                                </div>
                                <div class="cms-seo-metric">
                                    <span>Meta description</span>
                                    <strong id="ptext-seo-desc-count">0</strong>
                                </div>
                                <?php if ($template !== ''): ?>
                                    <div class="cms-seo-metric cms-seo-metric--full">
                                        <span>Modèle utilisé (gabarit)</span>
                                        <strong class="cms-seo-metric-path"><?= htmlspecialchars($template, ENT_QUOTES, 'UTF-8') ?>.php</strong>
                                        <span class="cms-seo-metric-sub">
                                            <?php if (is_string($routeSlug) && $routeSlug !== ''): ?>
                                                URL publique <code>/<?= htmlspecialchars($routeSlug, ENT_QUOTES, 'UTF-8') ?></code> · <code>page_contents</code>.
                                            <?php else: ?>
                                                Textes en base (<code>page_contents</code>) · <code>public/</code>.
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php
                            $seoFields = $seoSection['fields'] ?? [];
                            if (is_array($seoFields)) {
                                $renderFields($seoFields, 'seo', $saved, 'rail', $seoInputId);
                            }
                            ?>
                            <ul class="cms-seo-checklist" id="ptext-seo-checklist" role="list"></ul>
                            <p class="cms-seo-help">Longueurs recommandées : meta title 50–60 caractères, meta description 120–160 (comme sur la page Accueil).</p>
                        </aside>
                    </div>
                <?php endif; ?>
            </div>

            <div class="cms-actions">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a class="btn btn-light" href="/admin?module=cms">Retour à la liste</a>
            </div>
        </form>
    </div>
    <?php if ($hasSeoRail): ?>
    <script>
    (function () {
        var mt = document.getElementById('ptext-meta-title');
        var md = document.getElementById('ptext-meta-desc');
        var checklist = document.getElementById('ptext-seo-checklist');
        var badge = document.getElementById('ptext-seo-score-badge');
        var scoreLabel = document.getElementById('ptext-seo-score-label');
        if (!mt || !md || !checklist || !badge || !scoreLabel) {
            return;
        }
        function buildItem(label, ok) {
            return '<li class="' + (ok ? 'ok' : 'bad') + '">' + (ok ? 'OK — ' : 'À corriger — ') + label + '</li>';
        }
        function refresh() {
            var t = mt.value || '';
            var d = md.value || '';
            var titleOk = t.length >= 50 && t.length <= 60;
            var descOk = d.length >= 120 && d.length <= 160;
            var score = 0;
            var checks = [];
            checks.push(buildItem('Meta title entre 50 et 60 caractères (' + t.length + ')', titleOk));
            if (titleOk) { score += 50; }
            checks.push(buildItem('Meta description entre 120 et 160 caractères (' + d.length + ')', descOk));
            if (descOk) { score += 50; }
            checklist.innerHTML = checks.join('');
            var tc = document.getElementById('ptext-seo-title-count');
            var dc = document.getElementById('ptext-seo-desc-count');
            if (tc) { tc.textContent = String(t.length); }
            if (dc) { dc.textContent = String(d.length); }
            badge.textContent = String(score);
            badge.className = 'cms-seo-score-badge';
            if (score >= 90) {
                badge.classList.add('is-good');
                scoreLabel.textContent = 'Excellent';
            } else if (score >= 50) {
                badge.classList.add('is-warn');
                scoreLabel.textContent = 'Bon début';
            } else {
                badge.classList.add('is-bad');
                scoreLabel.textContent = 'À optimiser';
            }
        }
        mt.addEventListener('input', refresh);
        md.addEventListener('input', refresh);
        refresh();
    })();
    </script>
    <?php endif; ?>
    <?php
    if ($rteNeeded) {
        require_once __DIR__ . '/cms_rich_text.inc.php';
        cms_render_rich_text_editor_assets();
    }
}

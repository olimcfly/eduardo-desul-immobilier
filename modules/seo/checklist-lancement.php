<?php

declare(strict_types=1);

if ((!function_exists('setting') || !function_exists('saveSetting')) && is_file(ROOT_PATH . '/includes/settings.php')) {
    require_once ROOT_PATH . '/includes/settings.php';
}

require_once __DIR__ . '/services/SeoLaunchChecklistService.php';

$pageTitle = 'Checklist lancement SEO';

$launchFlash = (class_exists('Session') && method_exists('Session', 'getFlash')) ? Session::getFlash() : null;

$ownerId = SeoLaunchChecklistService::siteOwnerUserId();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && function_exists('csrfToken')) {
    $token = (string) ($_POST['csrf_token'] ?? '');
    if (hash_equals((string) csrfToken(), $token)) {
        $manual = [];
        foreach (SeoLaunchChecklistService::definitions() as $def) {
            if (!$def['auto']) {
                $manual[$def['id']] = !empty($_POST['manual'][$def['id']]);
            }
        }
        if ($ownerId > 0 && SeoLaunchChecklistService::saveManual($ownerId, $manual)) {
            if (class_exists('Session')) {
                Session::flash('success', 'Checklist enregistrée. Les notes manuelles sont sauvegardées pour le compte site (propriétaire).');
            }
        } elseif ($ownerId <= 0) {
            if (class_exists('Session')) {
                Session::flash('error', 'Aucun utilisateur « site » (rôle user) trouvé : impossible de sauvegarder la checklist.');
            }
        }
        header('Location: /admin?module=seo&action=launch-checklist', true, 303);
        exit;
    }
}

$auto = SeoLaunchChecklistService::runAutoChecks();
$manual = $ownerId > 0 ? SeoLaunchChecklistService::loadManual($ownerId) : [];
$score = SeoLaunchChecklistService::score($auto, $manual);

function launch_state_badge(string $state): string
{
    return match ($state) {
        'ok' => '<span class="lc-badge lc-badge--ok">OK</span>',
        'warn' => '<span class="lc-badge lc-badge--warn">À vérifier</span>',
        'fail' => '<span class="lc-badge lc-badge--fail">Non</span>',
        default => '<span class="lc-badge lc-badge--na">N/A</span>',
    };
}
?>
<style>
.launch-checklist { max-width: 1100px; }
.lc-hero {
    background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
    color: #fff; border-radius: 14px; padding: 1.25rem 1.35rem; margin-bottom: 1.25rem;
    display: grid; gap: .6rem;
}
.lc-hero h2 { margin: 0; font-size: 1.35rem; }
.lc-scores { display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; }
.lc-score-pill {
    background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.25);
    border-radius: 10px; padding: .45rem .75rem; font-size: .88rem;
}
.lc-score-pill strong { font-size: 1.25rem; color: #c9a84c; }
.lc-note-global { font-size: 1.5rem; font-weight: 800; color: #c9a84c; }
.lc-table-wrap { overflow-x: auto; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; }
.lc-table { width: 100%; border-collapse: collapse; font-size: .88rem; }
.lc-table th, .lc-table td { padding: .65rem .75rem; text-align: left; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
.lc-table th { background: #f8fafc; font-weight: 600; color: #334155; }
.lc-table tr:last-child td { border-bottom: 0; }
.lc-badge { display: inline-block; padding: .2rem .5rem; border-radius: 6px; font-weight: 700; font-size: .72rem; text-transform: uppercase; letter-spacing: .03em; }
.lc-badge--ok { background: #dcfce7; color: #166534; }
.lc-badge--warn { background: #fef3c7; color: #92400e; }
.lc-badge--fail { background: #fee2e2; color: #b91c1c; }
.lc-badge--na { background: #f1f5f9; color: #64748b; }
.lc-hint { color: #64748b; font-size: .8rem; display: block; margin-top: .2rem; }
.lc-actions { margin-top: 1rem; display: flex; gap: .75rem; flex-wrap: wrap; align-items: center; }
.lc-actions .btn { padding: .55rem 1rem; border-radius: 8px; font-weight: 600; border: 0; cursor: pointer; }
.lc-actions .btn--primary { background: #0f2237; color: #fff; }
.lc-foot { margin-top: 1rem; font-size: .82rem; color: #64748b; line-height: 1.5; }
.launch-checklist .flash--success { background: #ecfdf5; color: #166534; border: 1px solid #bbf7d0; }
.launch-checklist .flash--error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
</style>

<div class="launch-checklist">
    <div class="seo-breadcrumb">Accueil › SEO › Checklist lancement</div>

    <?php if (!empty($launchFlash['message'])): ?>
    <div class="flash flash--<?= htmlspecialchars((string) ($launchFlash['type'] ?? 'success'), ENT_QUOTES, 'UTF-8') ?>" role="alert" style="margin-bottom:1rem;padding:.75rem 1rem;border-radius:8px;font-size:.9rem">
        <?= htmlspecialchars((string) $launchFlash['message'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>

    <div class="lc-hero">
        <h2>SEO — Checklist lancement & conformité</h2>
        <p style="margin:0;opacity:.9;font-size:.9rem">Liste standard (anglais) + vérifications automatiques depuis ce serveur. Les cases à cocher servent aux points non mesurables automatiquement (Search Console, tracking, etc.).</p>
        <div class="lc-scores">
            <div class="lc-score-pill">Note auto : <strong><?= (int) $score['note_auto_pct'] ?>%</strong> (<?= (int) $score['auto_ok'] ?>/<?= (int) $score['auto_total'] ?>)</div>
            <?php if ((int) $score['manual_total'] > 0): ?>
            <div class="lc-score-pill">Manuel : <strong><?= (int) $score['note_manual_pct'] ?>%</strong> (<?= (int) $score['manual_ok'] ?>/<?= (int) $score['manual_total'] ?>)</div>
            <?php endif; ?>
            <div class="lc-score-pill">Note globale indicielle : <span class="lc-note-global"><?= (int) $score['note_global_pct'] ?>%</span></div>
        </div>
    </div>

    <form method="post" action="/admin?module=seo&action=launch-checklist">
        <?= function_exists('csrfField') ? csrfField() : '' ?>
        <div class="lc-table-wrap">
            <table class="lc-table">
                <thead>
                    <tr>
                        <th style="width:42%">Critère</th>
                        <th style="width:22%">État auto</th>
                        <th style="width:36%">Détail / validation manuelle</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach (SeoLaunchChecklistService::definitions() as $def):
                    $id = $def['id'];
                    $rowAuto = $def['auto'] ? ($auto[$id] ?? ['state' => 'na', 'detail' => '']) : null;
                    ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($def['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <span class="lc-hint"><?= htmlspecialchars($def['hint'], ENT_QUOTES, 'UTF-8') ?></span>
                        </td>
                        <td>
                            <?php if ($rowAuto): ?>
                                <?= launch_state_badge((string) ($rowAuto['state'] ?? 'na')) ?>
                            <?php else: ?>
                                <span class="lc-badge lc-badge--na">Manuel</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($rowAuto): ?>
                                <?= htmlspecialchars((string) ($rowAuto['detail'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            <?php else: ?>
                                <label style="display:flex;gap:.5rem;align-items:flex-start;cursor:pointer">
                                    <input type="checkbox" name="manual[<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>]" value="1"<?= !empty($manual[$id]) ? ' checked' : '' ?>>
                                    <span>Cocher lorsque c’est fait / validé.</span>
                                </label>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="lc-actions">
            <button type="submit" class="btn btn--primary">Enregistrer les validations manuelles</button>
            <a class="btn" style="background:#e2e8f0;color:#0f172a;text-decoration:none;display:inline-flex;align-items:center" href="/admin?module=seo">Retour hub SEO</a>
        </div>
    </form>

    <p class="lc-foot">
        <strong>Réparations récentes côté public :</strong> <code>/robots.txt</code> est servi dynamiquement (APP_URL + Sitemap) — supprimer tout fichier statique obsolète sur le serveur si besoin.
        <code>/sitemap.xml</code> est routé explicitement. Le plan du site HTML pointe désormais vers <code>/avis</code> pour les avis clients.
        Activez <code>GA_MEASUREMENT_ID</code> et <code>SEARCH_CONSOLE_VERIFICATION</code> dans <code>.env</code> pour les critères Analytics / Search Console.
    </p>
</div>

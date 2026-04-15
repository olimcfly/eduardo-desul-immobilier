<?php

declare(strict_types=1);

$step = (int) ($viewData['step'] ?? 1);
$labels = $viewData['labels'] ?? [];
$stepKeys = $viewData['stepKeys'] ?? [];
$answers = $viewData['answers'] ?? [];
$errors = $viewData['errors'] ?? [];
$blueprint = $viewData['blueprint'] ?? [];
$canResume = (bool) ($viewData['canResume'] ?? false);
$resumeStep = (int) ($viewData['resumeStep'] ?? 1);
$session = $viewData['session'] ?? [];
$status = (string) ($session['status'] ?? 'draft');
$flash = Session::getFlash();

$activeKey = $stepKeys[$step] ?? '';
$activeAnswers = $activeKey !== '' ? ($answers[$activeKey] ?? []) : [];

$progressPercent = (int) round((max(1, min(6, $step)) / 6) * 100);
if ($step === 6 && $status === 'completed') {
    $progressPercent = 100;
}

$identity = $answers['identity'] ?? [];
$target = $answers['target'] ?? [];
$offer = $answers['offer'] ?? [];
$territory = $answers['territory'] ?? [];
$goal = $answers['goal'] ?? [];
$auto = $blueprint['auto_generation'] ?? [];
?>
<style>
    .onb-shell { max-width: 720px; margin: 0 auto; display:grid; gap:1rem; padding-bottom:1rem; }
    .onb-card { background:#fff; border:1px solid #e5e7eb; border-radius:18px; padding:1rem; box-shadow:0 8px 28px rgba(17,24,39,.05); }
    .onb-header h1 { margin:0; font-size:1.35rem; color:#0f172a; }
    .onb-header p { margin:.35rem 0 0; color:#64748b; font-size:.94rem; }
    .onb-progress-meta { display:flex; justify-content:space-between; color:#64748b; font-size:.82rem; margin:.8rem 0 .4rem; }
    .onb-progress-track { width:100%; height:12px; background:#e2e8f0; border-radius:999px; overflow:hidden; }
    .onb-progress-bar { height:100%; background:linear-gradient(90deg,#2563eb,#0ea5e9); }
    .onb-stepper { display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:.35rem; margin-top:.75rem; }
    .onb-step { border:1px solid #d1d5db; border-radius:999px; padding:.38rem .5rem; text-align:center; font-size:.72rem; color:#475569; }
    .onb-step.active { background:#eff6ff; color:#1d4ed8; border-color:#93c5fd; font-weight:700; }
    .onb-step.done { background:#ecfdf3; color:#166534; border-color:#86efac; font-weight:700; }
    .onb-title { margin:0 0 .8rem; color:#0f172a; font-size:1.12rem; }
    .onb-grid { display:grid; gap:.75rem; }
    .onb-field label { display:block; font-weight:700; margin-bottom:.3rem; color:#0f172a; }
    .onb-field input, .onb-field select, .onb-field textarea { width:100%; border:1px solid #cbd5e1; border-radius:12px; padding:.84rem .9rem; font:inherit; font-size:1rem; }
    .onb-field textarea { min-height:110px; resize:vertical; }
    .onb-actions { display:flex; justify-content:space-between; gap:.6rem; margin-top:1rem; flex-wrap:wrap; }
    .onb-btn { border:0; border-radius:14px; padding:.85rem 1.1rem; font-weight:800; cursor:pointer; min-height:48px; }
    .onb-btn-secondary { background:#e2e8f0; color:#0f172a; }
    .onb-btn-ghost { background:#f8fafc; color:#334155; border:1px solid #cbd5e1; }
    .onb-btn-primary { background:#2563eb; color:#fff; }
    .onb-alert { padding:.72rem .86rem; border-radius:12px; font-size:.92rem; }
    .onb-alert-success { background:#ecfdf3; color:#166534; border:1px solid #bbf7d0; }
    .onb-alert-error { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
    .onb-summary { display:grid; gap:.7rem; }
    .onb-summary-box { border:1px solid #e2e8f0; border-radius:12px; padding:.75rem; background:#f8fafc; }
    .onb-summary-box h3 { margin:0 0 .5rem; font-size:.98rem; color:#0f172a; }
    .onb-summary-box p { margin:.2rem 0; color:#334155; font-size:.92rem; }

    @media (min-width: 768px) {
        .onb-card { padding:1.15rem 1.2rem; }
        .onb-grid.two { grid-template-columns:1fr 1fr; }
    }
</style>

<div class="onb-shell">
    <section class="onb-card onb-header">
        <h1>Onboarding — collecte des informations</h1>
        <p>Un écran par étape pour structurer vos données et préparer le module Construire.</p>

        <?php if ($canResume): ?>
            <p><a href="/admin?module=onboarding&amp;step=<?= (int) $resumeStep ?>">Reprendre votre progression (étape <?= (int) $resumeStep ?>/6)</a></p>
        <?php endif; ?>

        <div class="onb-progress-meta">
            <span>Progression</span>
            <span><?= (int) $progressPercent ?>%</span>
        </div>
        <div class="onb-progress-track">
            <div class="onb-progress-bar" style="width:<?= (int) max(0, min(100, $progressPercent)) ?>%"></div>
        </div>

        <div class="onb-stepper" aria-label="Progression onboarding">
            <?php foreach ($labels as $stepIndex => $stepLabel): ?>
                <?php
                $class = 'onb-step';
                if ($stepIndex === $step) {
                    $class .= ' active';
                } elseif ($stepIndex < $step || ($status === 'completed' && $stepIndex <= 6)) {
                    $class .= ' done';
                }
                ?>
                <div class="<?= e($class) ?>"><?= (int) $stepIndex ?></div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if (is_array($flash) && isset($flash['message'], $flash['type'])): ?>
        <div class="onb-alert <?= $flash['type'] === 'success' ? 'onb-alert-success' : 'onb-alert-error' ?>"><?= e((string) $flash['message']) ?></div>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <div class="onb-alert onb-alert-error">
            <?php foreach ($errors as $error): ?>
                <div>• <?= e((string) $error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($step <= 5): ?>
    <section class="onb-card">
        <h2 class="onb-title">Étape <?= (int) $step ?> — <?= e((string) ($labels[$step] ?? '')) ?></h2>

        <form method="post" action="/admin?module=onboarding&amp;step=<?= (int) $step ?>">
            <?= csrfField() ?>
            <input type="hidden" name="intent" value="save_step">
            <input type="hidden" name="step" value="<?= (int) $step ?>">

            <div class="onb-grid <?= in_array($step, [1, 5], true) ? 'two' : '' ?>">
                <?php if ($step === 1): ?>
                    <div class="onb-field"><label>Nom *</label><input required type="text" name="name" value="<?= e((string) ($activeAnswers['name'] ?? '')) ?>"></div>
                    <div class="onb-field"><label>Ville *</label><input required type="text" name="city" value="<?= e((string) ($activeAnswers['city'] ?? '')) ?>"></div>
                    <div class="onb-field"><label>Statut *</label>
                        <select name="status" required>
                            <?php foreach (['agent', 'mandataire', 'coach'] as $s): ?>
                                <option value="<?= e($s) ?>" <?= (($activeAnswers['status'] ?? '') === $s ? 'selected' : '') ?>><?= e(ucfirst($s)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="onb-field"><label>Expérience</label><input type="text" name="experience" value="<?= e((string) ($activeAnswers['experience'] ?? '')) ?>" placeholder="Ex : 8 ans, junior, reconversion..."></div>

                <?php elseif ($step === 2): ?>
                    <div class="onb-field"><label>Type de clients *</label><input required type="text" name="client_types" value="<?= e((string) ($activeAnswers['client_types'] ?? '')) ?>" placeholder="Ex : vendeurs, acheteurs, investisseurs"></div>
                    <div class="onb-field"><label>Situations principales</label><textarea name="main_situations" placeholder="Ex : succession, divorce, première acquisition, mutation...\"><?= e((string) ($activeAnswers['main_situations'] ?? '')) ?></textarea></div>

                <?php elseif ($step === 3): ?>
                    <div class="onb-field"><label>Description libre de votre offre actuelle *</label><textarea required name="description" placeholder="Décrivez votre offre telle qu’elle existe aujourd’hui."><?= e((string) ($activeAnswers['description'] ?? '')) ?></textarea></div>
                    <div class="onb-field"><label>Méthodes utilisées</label><textarea name="methods" placeholder="Ex : prospection terrain, recommandations, publicité locale...\"><?= e((string) ($activeAnswers['methods'] ?? '')) ?></textarea></div>

                <?php elseif ($step === 4): ?>
                    <div class="onb-field"><label>Ville principale *</label><input required type="text" name="primary_city" value="<?= e((string) ($activeAnswers['primary_city'] ?? '')) ?>"></div>
                    <div class="onb-field"><label>Zones secondaires</label><textarea name="secondary_zones" placeholder="Ex : Aix centre, Luynes, Puyricard..."><?= e((string) ($activeAnswers['secondary_zones'] ?? '')) ?></textarea></div>

                <?php elseif ($step === 5): ?>
                    <div class="onb-field"><label>Leads / mois visés *</label><input required type="number" min="0" name="leads_per_month" value="<?= e((string) ($activeAnswers['leads_per_month'] ?? '')) ?>"></div>
                    <div class="onb-field"><label>RDV souhaités *</label><input required type="number" min="0" name="appointments_target" value="<?= e((string) ($activeAnswers['appointments_target'] ?? '')) ?>"></div>
                    <div class="onb-field" style="grid-column:1/-1"><label>Revenus cible (€) *</label><input required type="number" min="0" step="100" name="revenue_target" value="<?= e((string) ($activeAnswers['revenue_target'] ?? '')) ?>"></div>
                <?php endif; ?>
            </div>

            <div class="onb-actions">
                <div>
                    <?php if ($step > 1): ?>
                        <button class="onb-btn onb-btn-secondary" name="navigation" value="prev" type="submit">Retour</button>
                    <?php endif; ?>
                </div>
                <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                    <button class="onb-btn onb-btn-ghost" name="navigation" value="save" type="submit">Sauvegarder</button>
                    <button class="onb-btn onb-btn-primary" name="navigation" value="next" type="submit"><?= $step < 5 ? 'Suivant' : 'Voir la synthèse' ?></button>
                </div>
            </div>
        </form>
    </section>
    <?php else: ?>

    <section class="onb-card">
        <h2 class="onb-title">Étape 6 — Synthèse</h2>
        <div class="onb-summary">
            <div class="onb-summary-box">
                <h3>Résumé des réponses</h3>
                <p><strong>Qui tu es :</strong> <?= e(trim(($identity['name'] ?? '') . ' — ' . ($identity['status'] ?? '') . ' — ' . ($identity['experience'] ?? ''))) ?></p>
                <p><strong>Cible :</strong> <?= e((string) ($target['client_types'] ?? '—')) ?><?= ($target['main_situations'] ?? '') !== '' ? ' · ' . e((string) $target['main_situations']) : '' ?></p>
                <p><strong>Offre actuelle :</strong> <?= e((string) ($offer['description'] ?? '—')) ?></p>
                <p><strong>Zone :</strong> <?= e((string) ($territory['primary_city'] ?? '—')) ?><?= ($territory['secondary_zones'] ?? '') !== '' ? ' · ' . e((string) $territory['secondary_zones']) : '' ?></p>
                <p><strong>Objectifs :</strong> <?= e((string) ($goal['leads_per_month'] ?? '0')) ?> leads/mois · <?= e((string) ($goal['appointments_target'] ?? '0')) ?> RDV · <?= e((string) ($goal['revenue_target'] ?? '0')) ?>€</p>
            </div>

            <div class="onb-summary-box">
                <h3>Génération automatique</h3>
                <p><strong>Persona :</strong> <?= e((string) ($auto['persona'] ?? '—')) ?></p>
                <p><strong>Positionnement :</strong> <?= e((string) ($auto['positioning'] ?? '—')) ?></p>
                <p><strong>Offre :</strong> <?= e((string) ($auto['offer'] ?? '—')) ?></p>
            </div>
        </div>

        <div class="onb-actions">
            <a class="onb-btn onb-btn-secondary" href="/admin?module=onboarding&amp;step=5" style="text-decoration:none;display:inline-flex;align-items:center;">Retour</a>
            <?php if ($status !== 'completed'): ?>
                <form method="post" action="/admin?module=onboarding&amp;step=6" style="display:inline">
                    <?= csrfField() ?>
                    <input type="hidden" name="intent" value="complete">
                    <button class="onb-btn onb-btn-primary" type="submit">Valider et continuer vers Construire</button>
                </form>
            <?php else: ?>
                <a class="onb-btn onb-btn-primary" href="/admin?module=construire" style="text-decoration:none;display:inline-flex;align-items:center;">Aller à Construire</a>
            <?php endif; ?>
        </div>
    </section>

    <?php endif; ?>
</div>

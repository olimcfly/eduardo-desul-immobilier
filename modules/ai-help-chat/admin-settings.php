<?php

declare(strict_types=1);

function renderAiHelpChatAdminSettings(AiHelpChatService $service): void
{
    aiHelpChatRequireSuperuser();

    $flash = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrf((string) ($_POST['csrf_token'] ?? ''))) {
            $flash = ['type' => 'error', 'text' => 'Token CSRF invalide.'];
        } else {
            $saveAction = (string) ($_POST['save_action'] ?? '');

            if ($saveAction === 'save_settings') {
                $ok = $service->saveSettings([
                    'assistant_name' => (string) ($_POST['assistant_name'] ?? 'Assistant Aide IA'),
                    'is_enabled' => isset($_POST['is_enabled']),
                    'default_language' => (string) ($_POST['default_language'] ?? 'fr'),
                    'tone' => (string) ($_POST['tone'] ?? 'professionnel'),
                    'response_length' => (string) ($_POST['response_length'] ?? 'moyenne'),
                    'response_mode' => (string) ($_POST['response_mode'] ?? 'guide'),
                    'system_prompt' => (string) ($_POST['system_prompt'] ?? ''),
                    'allow_admin' => isset($_POST['allow_admin']),
                    'allow_user' => isset($_POST['allow_user']),
                    'suggest_articles' => isset($_POST['suggest_articles']),
                    'suggest_next_step' => isset($_POST['suggest_next_step']),
                    'show_module_cta' => isset($_POST['show_module_cta']),
                    'enable_context_suggestions' => isset($_POST['enable_context_suggestions']),
                ]);

                $flash = ['type' => $ok ? 'success' : 'error', 'text' => $ok ? 'Réglages enregistrés.' : 'Erreur pendant la sauvegarde.'];
            }

            if ($saveAction === 'save_sources') {
                $postedSources = (array) ($_POST['sources'] ?? []);
                $sourcesMap = [];
                foreach ($service->getSources() as $source) {
                    $key = (string) ($source['source_key'] ?? '');
                    if ($key === '') {
                        continue;
                    }
                    $sourcesMap[$key] = isset($postedSources[$key]);
                }

                $ok = $service->saveSources($sourcesMap);
                $flash = ['type' => $ok ? 'success' : 'error', 'text' => $ok ? 'Sources mises à jour.' : 'Erreur de sauvegarde des sources.'];
            }
        }
    }

    $settings = $service->getSettings();
    $sources = $service->getSources();
    $logs = $service->getUsageLogs(40);
    ?>
    <section class="ai-help-admin">
        <header>
            <h1><i class="fas fa-robot"></i> Chat d’aide IA — Configuration superuser</h1>
            <p>Configuration globale réservée au superuser : prompt, sources, visibilité, comportements et audit.</p>
        </header>

        <?php if ($flash !== null): ?>
            <div class="ai-help-alert ai-help-alert--<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['text']) ?></div>
        <?php endif; ?>

        <div class="ai-help-tabs">
            <button data-tab="general" class="is-active">Général</button>
            <button data-tab="prompt">Prompt système</button>
            <button data-tab="sources">Sources</button>
            <button data-tab="access">Accès</button>
            <button data-tab="logs">Logs</button>
        </div>

        <section class="ai-help-tab is-active" id="tab-general">
            <form method="post" class="ai-help-form-grid">
                <?= csrfField() ?>
                <input type="hidden" name="save_action" value="save_settings">

                <label>Nom de l’assistant
                    <input type="text" name="assistant_name" value="<?= htmlspecialchars((string) $settings['assistant_name']) ?>" maxlength="120">
                </label>

                <label>
                    <input type="checkbox" name="is_enabled" <?= !empty($settings['is_enabled']) ? 'checked' : '' ?>> Chat actif
                </label>

                <label>Langue par défaut
                    <select name="default_language">
                        <option value="fr" <?= ($settings['default_language'] === 'fr') ? 'selected' : '' ?>>Français</option>
                        <option value="en" <?= ($settings['default_language'] === 'en') ? 'selected' : '' ?>>English</option>
                    </select>
                </label>

                <label>Ton de réponse
                    <select name="tone">
                        <option value="professionnel" <?= ($settings['tone'] === 'professionnel') ? 'selected' : '' ?>>Professionnel</option>
                        <option value="direct" <?= ($settings['tone'] === 'direct') ? 'selected' : '' ?>>Direct</option>
                        <option value="bienveillant" <?= ($settings['tone'] === 'bienveillant') ? 'selected' : '' ?>>Bienveillant</option>
                    </select>
                </label>

                <label>Longueur de réponse
                    <select name="response_length">
                        <option value="courte" <?= ($settings['response_length'] === 'courte') ? 'selected' : '' ?>>Courte</option>
                        <option value="moyenne" <?= ($settings['response_length'] === 'moyenne') ? 'selected' : '' ?>>Moyenne</option>
                        <option value="longue" <?= ($settings['response_length'] === 'longue') ? 'selected' : '' ?>>Longue</option>
                    </select>
                </label>

                <label>Comportement
                    <select name="response_mode">
                        <option value="concis" <?= ($settings['response_mode'] === 'concis') ? 'selected' : '' ?>>Concis</option>
                        <option value="normal" <?= ($settings['response_mode'] === 'normal') ? 'selected' : '' ?>>Normal</option>
                        <option value="guide" <?= ($settings['response_mode'] === 'guide') ? 'selected' : '' ?>>Guidé</option>
                    </select>
                </label>

                <label>
                    <input type="checkbox" name="suggest_articles" <?= !empty($settings['suggest_articles']) ? 'checked' : '' ?>> Proposer automatiquement des articles liés
                </label>
                <label>
                    <input type="checkbox" name="suggest_next_step" <?= !empty($settings['suggest_next_step']) ? 'checked' : '' ?>> Proposer l’étape suivante
                </label>
                <label>
                    <input type="checkbox" name="show_module_cta" <?= !empty($settings['show_module_cta']) ? 'checked' : '' ?>> Afficher un CTA vers le module conseillé
                </label>
                <label>
                    <input type="checkbox" name="enable_context_suggestions" <?= !empty($settings['enable_context_suggestions']) ? 'checked' : '' ?>> Activer suggestions contextuelles
                </label>

                <div class="ai-help-actions">
                    <button type="submit">Enregistrer Général + Comportements</button>
                </div>
            </form>
        </section>

        <section class="ai-help-tab" id="tab-prompt">
            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="save_action" value="save_settings">
                <label>Prompt système (limité au périmètre CRM)
                    <textarea name="system_prompt" rows="10"><?= htmlspecialchars((string) $settings['system_prompt']) ?></textarea>
                </label>
                <input type="hidden" name="assistant_name" value="<?= htmlspecialchars((string) $settings['assistant_name']) ?>">
                <input type="hidden" name="default_language" value="<?= htmlspecialchars((string) $settings['default_language']) ?>">
                <input type="hidden" name="tone" value="<?= htmlspecialchars((string) $settings['tone']) ?>">
                <input type="hidden" name="response_length" value="<?= htmlspecialchars((string) $settings['response_length']) ?>">
                <input type="hidden" name="response_mode" value="<?= htmlspecialchars((string) $settings['response_mode']) ?>">
                <?php if (!empty($settings['is_enabled'])): ?><input type="hidden" name="is_enabled" value="1"><?php endif; ?>
                <?php if (!empty($settings['allow_admin'])): ?><input type="hidden" name="allow_admin" value="1"><?php endif; ?>
                <?php if (!empty($settings['allow_user'])): ?><input type="hidden" name="allow_user" value="1"><?php endif; ?>
                <?php if (!empty($settings['suggest_articles'])): ?><input type="hidden" name="suggest_articles" value="1"><?php endif; ?>
                <?php if (!empty($settings['suggest_next_step'])): ?><input type="hidden" name="suggest_next_step" value="1"><?php endif; ?>
                <?php if (!empty($settings['show_module_cta'])): ?><input type="hidden" name="show_module_cta" value="1"><?php endif; ?>
                <?php if (!empty($settings['enable_context_suggestions'])): ?><input type="hidden" name="enable_context_suggestions" value="1"><?php endif; ?>
                <div class="ai-help-actions"><button type="submit">Enregistrer Prompt</button></div>
            </form>
        </section>

        <section class="ai-help-tab" id="tab-sources">
            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="save_action" value="save_sources">
                <div class="ai-help-sources">
                    <?php foreach ($sources as $source): ?>
                        <label>
                            <input type="checkbox" name="sources[<?= htmlspecialchars((string) $source['source_key']) ?>]" <?= !empty($source['is_active']) ? 'checked' : '' ?>>
                            <?= htmlspecialchars((string) $source['label']) ?>
                            <small>(<?= htmlspecialchars((string) $source['source_type']) ?>)</small>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="ai-help-actions"><button type="submit">Enregistrer Sources</button></div>
            </form>
        </section>

        <section class="ai-help-tab" id="tab-access">
            <form method="post" class="ai-help-form-grid">
                <?= csrfField() ?>
                <input type="hidden" name="save_action" value="save_settings">
                <input type="hidden" name="assistant_name" value="<?= htmlspecialchars((string) $settings['assistant_name']) ?>">
                <input type="hidden" name="default_language" value="<?= htmlspecialchars((string) $settings['default_language']) ?>">
                <input type="hidden" name="tone" value="<?= htmlspecialchars((string) $settings['tone']) ?>">
                <input type="hidden" name="response_length" value="<?= htmlspecialchars((string) $settings['response_length']) ?>">
                <input type="hidden" name="response_mode" value="<?= htmlspecialchars((string) $settings['response_mode']) ?>">
                <input type="hidden" name="system_prompt" value="<?= htmlspecialchars((string) $settings['system_prompt']) ?>">
                <?php if (!empty($settings['is_enabled'])): ?><input type="hidden" name="is_enabled" value="1"><?php endif; ?>
                <?php if (!empty($settings['suggest_articles'])): ?><input type="hidden" name="suggest_articles" value="1"><?php endif; ?>
                <?php if (!empty($settings['suggest_next_step'])): ?><input type="hidden" name="suggest_next_step" value="1"><?php endif; ?>
                <?php if (!empty($settings['show_module_cta'])): ?><input type="hidden" name="show_module_cta" value="1"><?php endif; ?>
                <?php if (!empty($settings['enable_context_suggestions'])): ?><input type="hidden" name="enable_context_suggestions" value="1"><?php endif; ?>

                <label><input type="checkbox" name="allow_admin" <?= !empty($settings['allow_admin']) ? 'checked' : '' ?>> Visible pour admin</label>
                <label><input type="checkbox" name="allow_user" <?= !empty($settings['allow_user']) ? 'checked' : '' ?>> Visible pour user</label>
                <div class="ai-help-actions"><button type="submit">Enregistrer Accès</button></div>
            </form>
        </section>

        <section class="ai-help-tab" id="tab-logs">
            <div class="ai-help-logs">
                <?php if ($logs === []): ?>
                    <p>Aucun log pour le moment.</p>
                <?php else: ?>
                    <table>
                        <thead><tr><th>Date</th><th>User</th><th>Contexte</th><th>Action</th><th>Détails</th></tr></thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string) ($log['created_at'] ?? '')) ?></td>
                                    <td>#<?= (int) ($log['user_id'] ?? 0) ?></td>
                                    <td><?= htmlspecialchars((string) ($log['module_context'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string) ($log['action_type'] ?? '')) ?></td>
                                    <td><code><?= htmlspecialchars((string) ($log['details_json'] ?? '{}')) ?></code></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </section>
    </section>

    <style>
        .ai-help-admin{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:1.2rem}
        .ai-help-admin h1{margin:.2rem 0 .5rem;color:#0f172a;font-size:1.35rem}
        .ai-help-admin p{margin:0;color:#64748b}
        .ai-help-alert{margin:1rem 0;padding:.7rem .9rem;border-radius:10px}
        .ai-help-alert--success{background:#ecfdf5;color:#166534;border:1px solid #bbf7d0}
        .ai-help-alert--error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
        .ai-help-tabs{display:flex;flex-wrap:wrap;gap:.5rem;margin:1rem 0}
        .ai-help-tabs button{border:1px solid #cbd5e1;background:#fff;padding:.45rem .8rem;border-radius:999px;cursor:pointer}
        .ai-help-tabs button.is-active{background:#1f3a5f;color:#fff;border-color:#1f3a5f}
        .ai-help-tab{display:none}
        .ai-help-tab.is-active{display:block}
        .ai-help-form-grid{display:grid;gap:.8rem;grid-template-columns:repeat(auto-fit,minmax(240px,1fr))}
        .ai-help-form-grid label{display:flex;flex-direction:column;gap:.4rem;color:#1e293b;font-size:.9rem}
        .ai-help-form-grid input[type="text"], .ai-help-form-grid select, textarea{padding:.58rem .7rem;border:1px solid #cbd5e1;border-radius:8px}
        .ai-help-sources{display:grid;gap:.6rem}
        .ai-help-sources label{display:flex;align-items:center;gap:.45rem;background:#f8fafc;padding:.55rem .65rem;border:1px solid #e2e8f0;border-radius:8px}
        .ai-help-sources small{color:#64748b}
        .ai-help-actions{grid-column:1/-1;display:flex;justify-content:flex-end}
        .ai-help-actions button{border:none;background:#1f3a5f;color:#fff;padding:.62rem 1rem;border-radius:8px;cursor:pointer}
        .ai-help-logs{overflow:auto}
        .ai-help-logs table{width:100%;border-collapse:collapse;font-size:.85rem}
        .ai-help-logs th,.ai-help-logs td{padding:.5rem;border-bottom:1px solid #e2e8f0;text-align:left;vertical-align:top}
        .ai-help-logs code{font-size:.77rem;white-space:pre-wrap;word-break:break-word}
    </style>

    <script>
        (() => {
            const tabs = document.querySelectorAll('.ai-help-tabs button');
            const panes = document.querySelectorAll('.ai-help-tab');
            tabs.forEach((btn) => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.tab;
                    tabs.forEach((x) => x.classList.toggle('is-active', x === btn));
                    panes.forEach((pane) => pane.classList.toggle('is-active', pane.id === `tab-${id}`));
                });
            });
        })();
    </script>
    <?php
}

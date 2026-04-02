<?php

declare(strict_types=1);

/**
 * Composant réutilisable pour les pages d'accueil module (3R + MERE + Action).
 */
class ModuleWelcomePage
{
    private string $storageFile;
    private string $userKey;

    public function __construct(string $storageFile, string $userKey)
    {
        $this->storageFile = $storageFile;
        $this->userKey = $userKey;
    }

    /**
     * @param array<string,mixed> $config
     */
    public function render(string $moduleKey, array $config, array $context = []): void
    {
        $title = (string)($config['title'] ?? ucfirst($moduleKey));
        $subtitle = (string)($config['subtitle'] ?? 'Démarrez avec un plan clair et actionnable.');
        $threeR = $config['3r'] ?? $config['three_r'] ?? [];
        $mere = $config['mere'] ?? [];
        $actions = $config['actions'] ?? $config['choices'] ?? [];
        $hasFreeField = (bool)($config['has_free_field'] ?? $config['free_text'] ?? false);
        $welcomeUrl = (string)($config['welcome_url'] ?? ('?page=' . urlencode($moduleKey)));
        $dashboardUrl = (string)($config['dashboard_url'] ?? ('?page=' . urlencode($moduleKey)));
        ?>
        <section class="mw-page">
            <style>
                .mw-page{max-width:1100px;margin:0 auto;padding:18px 10px 32px}
                .mw-hero{background:#fff;border:1px solid var(--line);border-radius:16px;padding:22px;display:flex;flex-wrap:wrap;gap:14px;justify-content:space-between;align-items:flex-end}
                .mw-over{font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:var(--text-3);font-weight:800}
                .mw-title{margin:6px 0 4px;font-size:34px;line-height:1.1}
                .mw-sub{margin:0;color:var(--text-2);font-size:16px}
                .mw-hero-actions{display:flex;gap:10px;flex-wrap:wrap}
                .mw-btn{border:none;border-radius:10px;padding:10px 14px;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;gap:7px;align-items:center}
                .mw-btn-primary{background:#4f46e5;color:#fff}
                .mw-btn-secondary{background:#eef2ff;color:#3730a3}
                .mw-block{margin-top:16px;background:#fff;border:1px solid var(--line);border-radius:16px;padding:18px}
                .mw-grid-3,.mw-grid-4{display:grid;gap:12px}
                .mw-grid-3{grid-template-columns:repeat(3,minmax(0,1fr))}
                .mw-grid-4{grid-template-columns:repeat(4,minmax(0,1fr))}
                .mw-card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:12px}
                .mw-card h3{margin:0 0 5px;font-size:13px;text-transform:uppercase;letter-spacing:.04em}
                .mw-card p{margin:0;color:#475569;font-size:14px}
                .mw-choice{display:flex;flex-direction:column;gap:12px}
                .mw-choice-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
                .mw-choice-item{display:flex;gap:9px;align-items:flex-start;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:10px}
                .mw-choice-item input{margin-top:2px}
                .mw-choice-item span{font-weight:600}
                .mw-textarea{width:100%;min-height:74px;border:1px solid #d1d5db;border-radius:10px;padding:10px;font:inherit}
                .mw-actions{display:flex;gap:10px;flex-wrap:wrap}
                .mw-context{margin-top:12px;font-size:12px;color:var(--text-3)}
                @media (max-width:900px){.mw-grid-3,.mw-grid-4,.mw-choice-grid{grid-template-columns:1fr}.mw-title{font-size:28px}}
            </style>

            <div class="mw-hero">
                <div>
                    <div class="mw-over">Module</div>
                    <h1 class="mw-title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
                    <p class="mw-sub"><?= htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="mw-hero-actions">
                    <button type="submit" form="module-welcome-form" name="welcome_action" value="start" class="mw-btn mw-btn-primary">Commencer</button>
                    <a href="<?= htmlspecialchars($dashboardUrl, ENT_QUOTES, 'UTF-8') ?>" class="mw-btn mw-btn-secondary">Accéder directement</a>
                </div>
            </div>

            <div class="mw-block">
                <div class="mw-over">3R</div>
                <div class="mw-grid-3">
                    <?php foreach ($threeR as $item): ?>
                        <article class="mw-card">
                            <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars((string)($item['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mw-block">
                <div class="mw-over">MERE</div>
                <div class="mw-grid-4">
                    <?php foreach ($mere as $item): ?>
                        <article class="mw-card">
                            <h3><?= htmlspecialchars((string)($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars((string)($item['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <form method="post" class="mw-block mw-choice" id="module-welcome-form">
                <input type="hidden" name="module_welcome_form" value="1">
                <input type="hidden" name="module_key" value="<?= htmlspecialchars($moduleKey, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="welcome_url" value="<?= htmlspecialchars($welcomeUrl, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="dashboard_url" value="<?= htmlspecialchars($dashboardUrl, ENT_QUOTES, 'UTF-8') ?>">

                <div class="mw-over">Action</div>
                <p style="margin:0;color:var(--text-2)">Choisissez comment vous souhaitez commencer.</p>

                <div class="mw-choice-grid">
                    <?php foreach ($actions as $idx => $action): ?>
                        <?php
                            $choiceId = is_array($action)
                                ? (string)($action['id'] ?? ('choice_' . $idx))
                                : ('choice_' . $idx);
                            $choiceLabel = is_array($action)
                                ? (string)($action['label'] ?? $choiceId)
                                : (string)$action;
                        ?>
                        <label class="mw-choice-item">
                            <input type="radio" name="module_choice" value="<?= htmlspecialchars($choiceId, ENT_QUOTES, 'UTF-8') ?>" <?= $idx === 0 ? 'checked' : '' ?>>
                            <div><span><?= htmlspecialchars($choiceLabel, ENT_QUOTES, 'UTF-8') ?></span></div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <?php if ($hasFreeField): ?>
                    <div>
                        <label for="module_note" style="font-size:13px;font-weight:700">Décrivez votre situation en une phrase</label>
                        <textarea id="module_note" name="module_note" class="mw-textarea" placeholder="Ex : j’ai déjà un pipeline, mais pas de suivi régulier."></textarea>
                    </div>
                <?php endif; ?>

                <div class="mw-actions">
                    <button type="submit" name="welcome_action" value="start" class="mw-btn mw-btn-primary">Continuer</button>
                    <button type="submit" name="welcome_action" value="direct" class="mw-btn mw-btn-secondary">Ignorer pour l’instant</button>
                </div>
            </form>

            <?php if (!empty($context)): ?>
                <div class="mw-context">
                    Dernier contexte : <?= htmlspecialchars((string)($context['choice'] ?? 'n/a'), ENT_QUOTES, 'UTF-8') ?><?= !empty($context['note']) ? ' — ' . htmlspecialchars((string)$context['note'], ENT_QUOTES, 'UTF-8') : '' ?>.
                </div>
            <?php endif; ?>
        </section>
        <?php
    }

    public function hasSeenModule(string $moduleKey): bool
    {
        $seen = $this->readSeenData();
        return !empty($seen[$this->userKey][$moduleKey]);
    }

    public function markAsSeen(string $moduleKey): void
    {
        $seen = $this->readSeenData();
        if (!isset($seen[$this->userKey]) || !is_array($seen[$this->userKey])) {
            $seen[$this->userKey] = [];
        }
        $seen[$this->userKey][$moduleKey] = true;
        $this->writeSeenData($seen);
    }

    /**
     * @return array<string,mixed>
     */
    private function readSeenData(): array
    {
        $file = $this->storageFile;
        if (!file_exists($file)) {
            return [];
        }

        $raw = file_get_contents($file);
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string,mixed> $data
     */
    private function writeSeenData(array $data): void
    {
        $dir = dirname($this->storageFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        file_put_contents(
            $this->storageFile,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }
}

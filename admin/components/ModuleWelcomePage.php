<?php
/**
 * Composant ModuleWelcomePage
 * Gère l'affichage et le suivi des pages de bienvenue des modules
 */

class ModuleWelcomePage
{
    private string $moduleKey;
    private array $config;
    private string $seenModulesFile;
    private array $seenModules;
    private string $userId;

    public function __construct(string $moduleKey)
    {
        $this->moduleKey = $moduleKey;
        $this->seenModulesFile = STORAGE_PATH . '/seen-modules.json';
        $this->userId = $this->resolveUserId();
        $this->seenModules = $this->loadSeenModules();
        $this->config = $this->loadConfig($moduleKey);
    }

    public function hasSeenModule(): bool
    {
        return isset($this->seenModules[$this->userId][$this->moduleKey])
            && $this->seenModules[$this->userId][$this->moduleKey] === true;
    }

    public function markAsSeen(): void
    {
        if (!isset($this->seenModules[$this->userId])) {
            $this->seenModules[$this->userId] = [];
        }
        $this->seenModules[$this->userId][$this->moduleKey] = true;
        $this->seenModules[$this->userId]['_last_seen_' . $this->moduleKey] = date('Y-m-d H:i:s');
        $this->saveSeenModules();
    }

    public function resetSeen(): void
    {
        if (isset($this->seenModules[$this->userId][$this->moduleKey])) {
            unset($this->seenModules[$this->userId][$this->moduleKey]);
            unset($this->seenModules[$this->userId]['_last_seen_' . $this->moduleKey]);
            $this->saveSeenModules();
        }
    }

    public function handleRequest(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $action = $_POST['welcome_action'] ?? '';

        if ($action === 'mark_seen') {
            $this->markAsSeen();
            $this->saveUserChoice();
            redirect($this->config['dashboard_url']);
            return true;
        }

        if ($action === 'skip') {
            $this->markAsSeen();
            redirect($this->config['dashboard_url']);
            return true;
        }

        if ($action === 'reset') {
            $this->resetSeen();
            redirect($this->config['welcome_url']);
            return true;
        }

        if ($action === 'choose') {
            $this->markAsSeen();
            $this->saveUserChoice();
            $choiceValue = $_POST['action_choice'] ?? '';
            $redirectUrl = $this->resolveActionUrl($choiceValue);
            redirect($redirectUrl);
            return true;
        }

        return false;
    }

    public function shouldShowWelcome(): bool
    {
        if (isset($_GET['force']) && $_GET['force'] === '1') {
            $this->resetSeen();
            return true;
        }

        return !$this->hasSeenModule();
    }

    public function render(): string
    {
        ob_start();
        ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($this->config['title']) ?> - Bienvenue | CRM Immobilier</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="/admin/assets/css/module-welcome.css">
    <style>
        :root {
            --module-color: <?= e($this->config['color']) ?>;
            --module-gradient: <?= e($this->config['gradient']) ?>;
        }
    </style>
</head>
<body class="welcome-body">
<nav class="welcome-nav">
    <div class="welcome-nav__inner">
        <div class="welcome-nav__brand">
            <i class="fa-solid fa-building-columns"></i>
            <span>CRM Immobilier</span>
        </div>
        <div class="welcome-nav__actions">
            <a href="<?= e($this->config['dashboard_url']) ?>" class="btn btn--ghost btn--sm">
                <i class="fa-solid fa-gauge"></i>
                Accéder directement
            </a>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="welcome_action" value="skip">
                <button type="submit" class="btn btn--outline btn--sm">
                    <i class="fa-solid fa-forward"></i>
                    Ignorer
                </button>
            </form>
        </div>
    </div>
</nav>

<section class="welcome-hero" style="background: var(--module-gradient);">
    <div class="welcome-hero__overlay"></div>
    <div class="welcome-hero__content">
        <div class="welcome-hero__badge">
            <i class="fa-solid <?= e($this->config['icon']) ?>"></i>
            <span>Module <?= e($this->config['title']) ?></span>
        </div>

        <h1 class="welcome-hero__title">Bienvenue dans <span class="welcome-hero__title--accent"><?= e($this->config['title']) ?></span></h1>
        <p class="welcome-hero__subtitle"><?= e($this->config['subtitle']) ?></p>

        <div class="welcome-hero__cta">
            <a href="#section-action" class="btn btn--primary btn--lg"><i class="fa-solid fa-play"></i>Commencer</a>
            <a href="<?= e($this->config['dashboard_url']) ?>" class="btn btn--white btn--lg"><i class="fa-solid fa-gauge-high"></i>Accéder au tableau de bord</a>
        </div>
    </div>
</section>

<section class="welcome-section welcome-3r" id="section-3r">
    <div class="welcome-container">
        <div class="section-header">
            <span class="section-header__tag">Comprendre le contexte</span>
            <h2 class="section-header__title">Les 3 points clés à connaître</h2>
        </div>

        <div class="cards-3r">
            <?php foreach (['realite', 'resultat', 'risque'] as $cardKey): ?>
                <?php $card = $this->config['3r'][$cardKey]; ?>
                <div class="card-3r card-3r--<?= e($cardKey) ?>" style="--card-color: <?= e($card['color'] ?? '#333') ?>">
                    <div class="card-3r__icon"><i class="fa-solid <?= e($card['icon'] ?? 'fa-circle') ?>"></i></div>
                    <h3 class="card-3r__title"><?= e($card['title']) ?></h3>
                    <p class="card-3r__text"><?= e($card['text']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="welcome-section welcome-mere" id="section-mere">
    <div class="welcome-container">
        <div class="section-header">
            <span class="section-header__tag">Ce que vous allez apprendre</span>
            <h2 class="section-header__title">Le cadre complet du module</h2>
        </div>

        <div class="cards-mere">
            <?php foreach (['motivation', 'explication', 'resultat', 'exercice'] as $mereKey): ?>
                <?php $block = $this->config['mere'][$mereKey]; ?>
                <div class="card-mere card-mere--<?= e($mereKey) ?>">
                    <div class="card-mere__icon"><i class="fa-solid <?= e($block['icon'] ?? 'fa-circle') ?>"></i></div>
                    <div class="card-mere__content">
                        <h3 class="card-mere__title"><?= e($block['title']) ?></h3>
                        <p class="card-mere__text"><?= e($block['text']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="welcome-section welcome-action" id="section-action">
    <div class="welcome-container">
        <div class="section-header">
            <span class="section-header__tag">Passez à l'action</span>
            <h2 class="section-header__title">Choisissez comment vous souhaitez commencer</h2>
        </div>

        <form method="POST" class="action-form" id="action-form-<?= e($this->moduleKey) ?>">
            <input type="hidden" name="welcome_action" value="choose">
            <input type="hidden" name="action_choice" id="action-choice-hidden" value="">

            <div class="action-choices">
                <?php foreach ($this->config['actions'] as $actionItem): ?>
                    <button type="button" class="action-choice-btn" data-value="<?= e($actionItem['value']) ?>" onclick="selectAction(this, '<?= e($actionItem['value']) ?>')">
                        <?= e($actionItem['label']) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($this->config['has_free_field'])): ?>
                <div class="action-free-field" id="free-field-wrapper">
                    <label class="action-free-field__label" for="free-field-input">
                        <?= e($this->config['free_field_label'] ?? 'Décrivez votre situation') ?>
                    </label>
                    <textarea id="free-field-input" name="free_field_text" class="action-free-field__textarea" placeholder="<?= e($this->config['free_field_placeholder'] ?? '') ?>" rows="3" maxlength="500"></textarea>
                </div>
            <?php endif; ?>

            <div class="action-submit">
                <button type="submit" class="btn btn--primary btn--xl btn--module" id="btn-continuer" disabled>
                    Continuer avec mon choix
                </button>
                <a href="<?= e($this->config['dashboard_url']) ?>" class="btn btn--ghost btn--lg">Accéder directement au tableau de bord</a>
            </div>
        </form>
    </div>
</section>

<footer class="welcome-footer">
    <div class="welcome-container">
        <div class="welcome-footer__actions">
            <form method="POST" style="display:inline;">
                <input type="hidden" name="welcome_action" value="reset">
                <button type="submit" class="btn btn--ghost btn--xs">Relancer l'introduction</button>
            </form>
        </div>
    </div>
</footer>

<script src="/admin/assets/js/module-welcome.js"></script>
<script>
    ModuleWelcome.init({
        moduleKey: '<?= e($this->moduleKey) ?>',
        dashboardUrl: '<?= e($this->config['dashboard_url']) ?>',
        hasFreeField: <?= !empty($this->config['has_free_field']) ? 'true' : 'false' ?>
    });
</script>
</body>
</html>
        <?php
        return ob_get_clean();
    }

    private function loadConfig(string $moduleKey): array
    {
        $allConfigs = require CONFIG_PATH . '/module-welcome.php';

        if (!isset($allConfigs[$moduleKey])) {
            throw new \InvalidArgumentException("Module '{$moduleKey}' non trouvé dans la configuration.");
        }

        return $allConfigs[$moduleKey];
    }

    private function loadSeenModules(): array
    {
        if (!file_exists($this->seenModulesFile)) {
            return [];
        }

        $json = file_get_contents($this->seenModulesFile);
        $data = json_decode($json, true);

        return is_array($data) ? $data : [];
    }

    private function saveSeenModules(): void
    {
        $dir = dirname($this->seenModulesFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            $this->seenModulesFile,
            json_encode($this->seenModules, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }

    private function resolveUserId(): string
    {
        if (isset($_SESSION['auth_admin_id'])) {
            return (string)$_SESSION['auth_admin_id'];
        }

        if (isset($_SESSION['user_id'])) {
            return (string)$_SESSION['user_id'];
        }

        if (!isset($_SESSION['anonymous_id'])) {
            $_SESSION['anonymous_id'] = 'anon_' . bin2hex(random_bytes(8));
        }

        return (string)$_SESSION['anonymous_id'];
    }

    private function resolveActionUrl(string $choiceValue): string
    {
        foreach ($this->config['actions'] as $action) {
            if (($action['value'] ?? '') === $choiceValue) {
                return (string)$action['url'];
            }
        }

        return (string)$this->config['dashboard_url'];
    }

    private function saveUserChoice(): void
    {
        $choice = $_POST['action_choice'] ?? null;
        $freeField = $_POST['free_field_text'] ?? null;

        if ($choice) {
            $_SESSION['module_choices'][$this->moduleKey] = [
                'choice' => $choice,
                'free_text' => $freeField,
                'chosen_at' => date('Y-m-d H:i:s')
            ];
        }
    }
}

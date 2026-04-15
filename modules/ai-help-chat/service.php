<?php

declare(strict_types=1);

require_once __DIR__ . '/../aide/service.php';

final class AiHelpChatService
{
    private PDO $db;
    private HelpCenterService $helpService;
    private static array $tableExistsCache = [];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->helpService = new HelpCenterService($db);
        $this->ensureSchema();
    }

    public function getSettings(): array
    {
        $defaults = $this->defaultSettings();

        if (!$this->tableExists('ai_help_settings')) {
            return $defaults;
        }

        try {
            $stmt = $this->db->query('SELECT * FROM ai_help_settings ORDER BY id ASC LIMIT 1');
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return $defaults;
            }

            return array_merge($defaults, [
                'id' => (int) ($row['id'] ?? 1),
                'assistant_name' => (string) ($row['assistant_name'] ?? $defaults['assistant_name']),
                'is_enabled' => (int) ($row['is_enabled'] ?? 1) === 1,
                'default_language' => (string) ($row['default_language'] ?? 'fr'),
                'tone' => (string) ($row['tone'] ?? 'professionnel'),
                'response_length' => (string) ($row['response_length'] ?? 'moyenne'),
                'response_mode' => (string) ($row['response_mode'] ?? 'guide'),
                'system_prompt' => (string) ($row['system_prompt'] ?? $defaults['system_prompt']),
                'allow_admin' => (int) ($row['allow_admin'] ?? 1) === 1,
                'allow_user' => (int) ($row['allow_user'] ?? 1) === 1,
                'suggest_articles' => (int) ($row['suggest_articles'] ?? 1) === 1,
                'suggest_next_step' => (int) ($row['suggest_next_step'] ?? 1) === 1,
                'show_module_cta' => (int) ($row['show_module_cta'] ?? 1) === 1,
                'enable_context_suggestions' => (int) ($row['enable_context_suggestions'] ?? 1) === 1,
            ]);
        } catch (Throwable $e) {
            error_log('AiHelpChatService::getSettings ' . $e->getMessage());
            return $defaults;
        }
    }

    public function saveSettings(array $payload): bool
    {
        $defaults = $this->defaultSettings();
        $data = array_merge($defaults, $payload);

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO ai_help_settings (
                    id, assistant_name, is_enabled, default_language, tone, response_length,
                    response_mode, system_prompt, allow_admin, allow_user, suggest_articles,
                    suggest_next_step, show_module_cta, enable_context_suggestions, created_at, updated_at
                ) VALUES (
                    1, :assistant_name, :is_enabled, :default_language, :tone, :response_length,
                    :response_mode, :system_prompt, :allow_admin, :allow_user, :suggest_articles,
                    :suggest_next_step, :show_module_cta, :enable_context_suggestions, NOW(), NOW()
                )
                ON DUPLICATE KEY UPDATE
                    assistant_name = VALUES(assistant_name),
                    is_enabled = VALUES(is_enabled),
                    default_language = VALUES(default_language),
                    tone = VALUES(tone),
                    response_length = VALUES(response_length),
                    response_mode = VALUES(response_mode),
                    system_prompt = VALUES(system_prompt),
                    allow_admin = VALUES(allow_admin),
                    allow_user = VALUES(allow_user),
                    suggest_articles = VALUES(suggest_articles),
                    suggest_next_step = VALUES(suggest_next_step),
                    show_module_cta = VALUES(show_module_cta),
                    enable_context_suggestions = VALUES(enable_context_suggestions),
                    updated_at = NOW()'
            );

            return $stmt->execute([
                'assistant_name' => mb_substr(trim((string) $data['assistant_name']), 0, 120),
                'is_enabled' => $this->toBoolInt($data['is_enabled'] ?? false),
                'default_language' => mb_substr(trim((string) $data['default_language']), 0, 8),
                'tone' => mb_substr(trim((string) $data['tone']), 0, 32),
                'response_length' => mb_substr(trim((string) $data['response_length']), 0, 32),
                'response_mode' => mb_substr(trim((string) $data['response_mode']), 0, 32),
                'system_prompt' => trim((string) $data['system_prompt']),
                'allow_admin' => $this->toBoolInt($data['allow_admin'] ?? false),
                'allow_user' => $this->toBoolInt($data['allow_user'] ?? false),
                'suggest_articles' => $this->toBoolInt($data['suggest_articles'] ?? false),
                'suggest_next_step' => $this->toBoolInt($data['suggest_next_step'] ?? false),
                'show_module_cta' => $this->toBoolInt($data['show_module_cta'] ?? false),
                'enable_context_suggestions' => $this->toBoolInt($data['enable_context_suggestions'] ?? false),
            ]);
        } catch (Throwable $e) {
            error_log('AiHelpChatService::saveSettings ' . $e->getMessage());
            return false;
        }
    }

    public function canUserUseChat(string $role): bool
    {
        $settings = $this->getSettings();
        if (!$settings['is_enabled']) {
            return false;
        }

        if ($role === 'superadmin') {
            return true;
        }

        if ($role === 'admin') {
            return $settings['allow_admin'];
        }

        if ($role === 'user') {
            return $settings['allow_user'];
        }

        return false;
    }

    public function getSources(): array
    {
        $defaults = [
            ['source_type' => 'help_articles', 'source_key' => 'help_articles', 'label' => 'Articles du centre d’aide', 'is_active' => true],
            ['source_type' => 'internal_guides', 'source_key' => 'internal_guides', 'label' => 'Guides internes', 'is_active' => true],
            ['source_type' => 'module_docs', 'source_key' => 'module_docs', 'label' => 'Documentation par module', 'is_active' => true],
            ['source_type' => 'videos', 'source_key' => 'videos', 'label' => 'Ressources vidéos (futures)', 'is_active' => false],
        ];

        if (!$this->tableExists('ai_help_sources')) {
            return $defaults;
        }

        try {
            $stmt = $this->db->query('SELECT id, source_type, source_key, label, is_active, created_at FROM ai_help_sources ORDER BY id ASC');
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            if ($rows === []) {
                $this->seedSources($defaults);
                return $this->getSources();
            }

            return array_map(static function (array $row): array {
                return [
                    'id' => (int) ($row['id'] ?? 0),
                    'source_type' => (string) ($row['source_type'] ?? ''),
                    'source_key' => (string) ($row['source_key'] ?? ''),
                    'label' => (string) ($row['label'] ?? ''),
                    'is_active' => (int) ($row['is_active'] ?? 0) === 1,
                    'created_at' => (string) ($row['created_at'] ?? ''),
                ];
            }, $rows);
        } catch (Throwable $e) {
            error_log('AiHelpChatService::getSources ' . $e->getMessage());
            return $defaults;
        }
    }

    public function saveSources(array $sources): bool
    {
        if (!$this->tableExists('ai_help_sources')) {
            return false;
        }

        try {
            $stmt = $this->db->prepare('UPDATE ai_help_sources SET is_active = :is_active WHERE source_key = :source_key');
            foreach ($sources as $sourceKey => $isActive) {
                $stmt->execute([
                    'source_key' => mb_substr(preg_replace('/[^a-z0-9_-]/', '', (string) $sourceKey), 0, 100),
                    'is_active' => $this->toBoolInt($isActive),
                ]);
            }

            return true;
        } catch (Throwable $e) {
            error_log('AiHelpChatService::saveSources ' . $e->getMessage());
            return false;
        }
    }

    public function createConversation(int $userId, string $roleType, array $context): int
    {
        if (!$this->tableExists('ai_help_conversations')) {
            return 0;
        }

        try {
            $stmt = $this->db->prepare('INSERT INTO ai_help_conversations (user_id, role_type, module_context, created_at) VALUES (:user_id, :role_type, :module_context, NOW())');
            $stmt->execute([
                'user_id' => $userId > 0 ? $userId : null,
                'role_type' => mb_substr($roleType, 0, 20),
                'module_context' => json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            return (int) $this->db->lastInsertId();
        } catch (Throwable $e) {
            error_log('AiHelpChatService::createConversation ' . $e->getMessage());
            return 0;
        }
    }

    public function saveMessage(int $conversationId, string $sender, string $message, array $metadata = []): void
    {
        if ($conversationId <= 0 || !$this->tableExists('ai_help_messages')) {
            return;
        }

        try {
            $stmt = $this->db->prepare('INSERT INTO ai_help_messages (conversation_id, sender, message, metadata_json, created_at) VALUES (:conversation_id, :sender, :message, :metadata_json, NOW())');
            $stmt->execute([
                'conversation_id' => $conversationId,
                'sender' => mb_substr($sender, 0, 20),
                'message' => trim($message),
                'metadata_json' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
        } catch (Throwable $e) {
            error_log('AiHelpChatService::saveMessage ' . $e->getMessage());
        }
    }

    public function logUsage(int $userId, string $moduleContext, string $actionType, array $details = []): void
    {
        if (!$this->tableExists('ai_help_usage_logs')) {
            return;
        }

        try {
            $stmt = $this->db->prepare('INSERT INTO ai_help_usage_logs (user_id, module_context, action_type, details_json, created_at) VALUES (:user_id, :module_context, :action_type, :details_json, NOW())');
            $stmt->execute([
                'user_id' => $userId > 0 ? $userId : null,
                'module_context' => mb_substr($moduleContext, 0, 60),
                'action_type' => mb_substr($actionType, 0, 50),
                'details_json' => json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
        } catch (Throwable $e) {
            error_log('AiHelpChatService::logUsage ' . $e->getMessage());
        }
    }

    public function getUsageLogs(int $limit = 100): array
    {
        if (!$this->tableExists('ai_help_usage_logs')) {
            return [];
        }

        try {
            $stmt = $this->db->prepare('SELECT id, user_id, module_context, action_type, details_json, created_at FROM ai_help_usage_logs ORDER BY id DESC LIMIT :limit');
            $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('AiHelpChatService::getUsageLogs ' . $e->getMessage());
            return [];
        }
    }

    public function suggestResources(string $query, array $context, int $limit = 3): array
    {
        $settings = $this->getSettings();
        if (!$settings['suggest_articles']) {
            return [];
        }

        $activeSourceMap = [];
        foreach ($this->getSources() as $source) {
            $activeSourceMap[(string) ($source['source_key'] ?? '')] = (bool) ($source['is_active'] ?? false);
        }

        if (!($activeSourceMap['help_articles'] ?? false)) {
            return [];
        }

        $contextModule = (string) ($context['module'] ?? '');
        $articles = $this->helpService->searchArticles($query, '', $contextModule, $limit);

        return array_map(static function (array $article): array {
            return [
                'id' => (string) ($article['id'] ?? ''),
                'slug' => (string) ($article['slug'] ?? ''),
                'title' => (string) ($article['title'] ?? 'Ressource interne'),
                'excerpt' => (string) ($article['excerpt'] ?? ''),
                'category' => (string) ($article['category'] ?? ''),
                'module_key' => (string) ($article['module_key'] ?? ''),
                'url' => '/admin?module=aide&action=article&id=' . rawurlencode((string) ($article['slug'] ?: $article['id'] ?? '')),
            ];
        }, $articles);
    }

    public function buildAssistantResponse(string $message, array $context): array
    {
        $settings = $this->getSettings();
        $resources = $this->suggestResources($message, $context, 3);
        $module = (string) ($context['module'] ?? 'dashboard');

        $response = $this->buildScopedReply($message, $resources, $settings);

        $nextStep = null;
        if ($settings['suggest_next_step']) {
            $nextStep = $this->inferNextStep($module);
        }

        $moduleCta = null;
        if ($settings['show_module_cta'] && $nextStep !== null) {
            $moduleCta = [
                'label' => 'Aller au module conseillé : ' . ucfirst($nextStep),
                'url' => '/admin?module=' . rawurlencode($nextStep),
            ];
        }

        return [
            'answer' => $response,
            'resources' => $resources,
            'next_step' => $nextStep,
            'module_cta' => $moduleCta,
        ];
    }

    private function buildScopedReply(string $message, array $resources, array $settings): string
    {
        $tone = (string) ($settings['tone'] ?? 'professionnel');
        $mode = (string) ($settings['response_mode'] ?? 'guide');

        $prefix = match ($tone) {
            'direct' => 'Réponse directe :',
            'bienveillant' => 'Voici une aide rapide :',
            default => 'Assistant CRM :',
        };

        $actionHint = match ($mode) {
            'concis' => 'Passez à l’action dans le module actif.',
            'normal' => 'Appliquez cette étape puis vérifiez le résultat.',
            default => 'Suivez l’étape proposée ci-dessous pour avancer concrètement.',
        };

        if ($resources === []) {
            return $prefix . " Je reste limité au périmètre du CRM. Reformulez votre question avec le module, la page ou l’objectif (ex: SEO, Capturer, Convertir). " . $actionHint;
        }

        $best = $resources[0];
        $resourceTitle = (string) ($best['title'] ?? 'ressource interne');
        return $prefix . ' Pour votre demande (« ' . mb_substr(trim($message), 0, 160) . ' »), commencez par la ressource « ' . $resourceTitle . ' ». ' . $actionHint;
    }

    private function inferNextStep(string $module): ?string
    {
        $module = preg_replace('/[^a-z0-9_-]/', '', mb_strtolower($module));
        return match ($module) {
            'onboarding' => 'construire',
            'construire' => 'attirer',
            'attirer', 'seo', 'social' => 'capturer',
            'capturer', 'capture' => 'convertir',
            'convertir' => 'optimiser',
            default => null,
        };
    }

    private function defaultSettings(): array
    {
        return [
            'id' => 1,
            'assistant_name' => 'Assistant Aide IA',
            'is_enabled' => true,
            'default_language' => 'fr',
            'tone' => 'professionnel',
            'response_length' => 'moyenne',
            'response_mode' => 'guide',
            'system_prompt' => "Tu es un assistant d’aide interne au CRM. Tu réponds uniquement depuis les ressources CRM. Tu restes concret, tu n’inventes pas de fonctionnalité, et tu proposes une action suivante.",
            'allow_admin' => true,
            'allow_user' => true,
            'suggest_articles' => true,
            'suggest_next_step' => true,
            'show_module_cta' => true,
            'enable_context_suggestions' => true,
        ];
    }

    private function ensureSchema(): void
    {
        try {
            $this->db->exec(
                'CREATE TABLE IF NOT EXISTS ai_help_settings (
                    id INT UNSIGNED NOT NULL PRIMARY KEY,
                    assistant_name VARCHAR(120) NOT NULL,
                    is_enabled TINYINT(1) NOT NULL DEFAULT 1,
                    default_language VARCHAR(8) NOT NULL DEFAULT "fr",
                    tone VARCHAR(32) NOT NULL DEFAULT "professionnel",
                    response_length VARCHAR(32) NOT NULL DEFAULT "moyenne",
                    response_mode VARCHAR(32) NOT NULL DEFAULT "guide",
                    system_prompt TEXT NOT NULL,
                    allow_admin TINYINT(1) NOT NULL DEFAULT 1,
                    allow_user TINYINT(1) NOT NULL DEFAULT 1,
                    suggest_articles TINYINT(1) NOT NULL DEFAULT 1,
                    suggest_next_step TINYINT(1) NOT NULL DEFAULT 1,
                    show_module_cta TINYINT(1) NOT NULL DEFAULT 1,
                    enable_context_suggestions TINYINT(1) NOT NULL DEFAULT 1,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );

            $this->db->exec(
                'CREATE TABLE IF NOT EXISTS ai_help_sources (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    source_type VARCHAR(50) NOT NULL,
                    source_key VARCHAR(100) NOT NULL UNIQUE,
                    label VARCHAR(180) NOT NULL,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );

            $this->db->exec(
                'CREATE TABLE IF NOT EXISTS ai_help_conversations (
                    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    user_id INT UNSIGNED NULL,
                    role_type VARCHAR(20) NOT NULL,
                    module_context JSON NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_ai_help_conv_user (user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );

            $this->db->exec(
                'CREATE TABLE IF NOT EXISTS ai_help_messages (
                    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    conversation_id BIGINT UNSIGNED NOT NULL,
                    sender VARCHAR(20) NOT NULL,
                    message TEXT NOT NULL,
                    metadata_json JSON NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_ai_help_msg_conv (conversation_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );

            $this->db->exec(
                'CREATE TABLE IF NOT EXISTS ai_help_usage_logs (
                    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    user_id INT UNSIGNED NULL,
                    module_context VARCHAR(60) NOT NULL,
                    action_type VARCHAR(50) NOT NULL,
                    details_json JSON NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_ai_help_usage_user (user_id),
                    INDEX idx_ai_help_usage_context (module_context)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );

            $this->saveSettings($this->defaultSettings());
            $this->seedSources($this->getSources());
        } catch (Throwable $e) {
            error_log('AiHelpChatService::ensureSchema ' . $e->getMessage());
        }
    }

    private function seedSources(array $sources): void
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO ai_help_sources (source_type, source_key, label, is_active, created_at)
                 VALUES (:source_type, :source_key, :label, :is_active, NOW())
                 ON DUPLICATE KEY UPDATE
                    source_type = VALUES(source_type),
                    label = VALUES(label),
                    is_active = VALUES(is_active)'
            );

            foreach ($sources as $source) {
                $stmt->execute([
                    'source_type' => mb_substr((string) ($source['source_type'] ?? 'custom'), 0, 50),
                    'source_key' => mb_substr((string) ($source['source_key'] ?? ''), 0, 100),
                    'label' => mb_substr((string) ($source['label'] ?? 'Source'), 0, 180),
                    'is_active' => $this->toBoolInt((bool) ($source['is_active'] ?? false)),
                ]);
            }
        } catch (Throwable $e) {
            error_log('AiHelpChatService::seedSources ' . $e->getMessage());
        }
    }

    private function tableExists(string $tableName): bool
    {
        if (array_key_exists($tableName, self::$tableExistsCache)) {
            return self::$tableExistsCache[$tableName];
        }

        try {
            $stmt = $this->db->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name LIMIT 1');
            $stmt->execute(['table_name' => $tableName]);
            self::$tableExistsCache[$tableName] = (bool) $stmt->fetchColumn();
            return self::$tableExistsCache[$tableName];
        } catch (Throwable $e) {
            error_log('AiHelpChatService::tableExists ' . $e->getMessage());
            self::$tableExistsCache[$tableName] = false;
            return false;
        }
    }

    private function toBoolInt(mixed $value): int
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    }
}

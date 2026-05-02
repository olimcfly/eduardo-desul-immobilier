<?php

declare(strict_types=1);

final class AgentService
{
    private PDO $db;
    private string $openrouterKey;

    public function __construct(PDO $db, string $openrouterKey = '')
    {
        $this->db = $db;
        $this->openrouterKey = $openrouterKey;
    }

    // ============ AGENTS ============

    public function getAgents(): array
    {
        try {
            $stmt = $this->db->query('SELECT * FROM agents WHERE is_active = 1 ORDER BY name');
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
        } catch (Throwable $e) {
            error_log('AgentService::getAgents ' . $e->getMessage());
            return [];
        }
    }

    public function getAgent(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM agents WHERE id = ?');
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) {
            error_log('AgentService::getAgent ' . $e->getMessage());
            return null;
        }
    }

    public function getAgentBySlug(string $slug): ?array
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM agents WHERE slug = ?');
            $stmt->execute([$slug]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) {
            error_log('AgentService::getAgentBySlug ' . $e->getMessage());
            return null;
        }
    }

    public function createAgent(array $data): int|false
    {
        try {
            $stmt = $this->db->prepare('
                INSERT INTO agents (slug, name, description, system_prompt, task_category, is_active)
                VALUES (?, ?, ?, ?, ?, ?)
            ');
            $success = $stmt->execute([
                $data['slug'] ?? '',
                $data['name'] ?? '',
                $data['description'] ?? '',
                $data['system_prompt'] ?? '',
                $data['task_category'] ?? '',
                $data['is_active'] ?? 1,
            ]);
            return $success ? $this->db->lastInsertId() : false;
        } catch (Throwable $e) {
            error_log('AgentService::createAgent ' . $e->getMessage());
            return false;
        }
    }

    public function updateAgent(int $id, array $data): bool
    {
        try {
            $updates = [];
            $values = [];
            foreach (['name', 'description', 'system_prompt', 'task_category', 'is_active'] as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = ?";
                    $values[] = $data[$field];
                }
            }
            if (empty($updates)) {
                return false;
            }
            $values[] = $id;
            $stmt = $this->db->prepare('UPDATE agents SET ' . implode(', ', $updates) . ' WHERE id = ?');
            return $stmt->execute($values);
        } catch (Throwable $e) {
            error_log('AgentService::updateAgent ' . $e->getMessage());
            return false;
        }
    }

    // ============ AGENT MODELS ============

    public function getAgentModels(int $agentId): array
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM agent_models WHERE agent_id = ? ORDER BY is_primary DESC, model_name');
            $stmt->execute([$agentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
        } catch (Throwable $e) {
            error_log('AgentService::getAgentModels ' . $e->getMessage());
            return [];
        }
    }

    public function getPrimaryModel(int $agentId): ?array
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM agent_models WHERE agent_id = ? AND is_primary = 1 LIMIT 1');
            $stmt->execute([$agentId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) {
            error_log('AgentService::getPrimaryModel ' . $e->getMessage());
            return null;
        }
    }

    public function assignModel(int $agentId, string $modelId, array $config = []): bool
    {
        try {
            $stmt = $this->db->prepare('
                INSERT INTO agent_models (agent_id, model_id, model_name, provider, capabilities, temperature, max_tokens, is_primary)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    model_name = VALUES(model_name),
                    capabilities = VALUES(capabilities),
                    temperature = VALUES(temperature),
                    max_tokens = VALUES(max_tokens),
                    is_primary = VALUES(is_primary)
            ');
            return $stmt->execute([
                $agentId,
                $modelId,
                $config['model_name'] ?? $modelId,
                $config['provider'] ?? 'openrouter',
                json_encode($config['capabilities'] ?? []),
                $config['temperature'] ?? 0.7,
                $config['max_tokens'] ?? 2048,
                $config['is_primary'] ?? 0,
            ]);
        } catch (Throwable $e) {
            error_log('AgentService::assignModel ' . $e->getMessage());
            return false;
        }
    }

    // ============ TASKS ============

    public function getAgentTasks(int $agentId): array
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM agent_tasks WHERE agent_id = ? ORDER BY name');
            $stmt->execute([$agentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
        } catch (Throwable $e) {
            error_log('AgentService::getAgentTasks ' . $e->getMessage());
            return [];
        }
    }

    public function addTask(int $agentId, array $taskData): int|false
    {
        try {
            $stmt = $this->db->prepare('
                INSERT INTO agent_tasks (agent_id, name, description, input_type, output_type, examples)
                VALUES (?, ?, ?, ?, ?, ?)
            ');
            $success = $stmt->execute([
                $agentId,
                $taskData['name'] ?? '',
                $taskData['description'] ?? '',
                $taskData['input_type'] ?? 'text',
                $taskData['output_type'] ?? 'text',
                $taskData['examples'] ?? '',
            ]);
            return $success ? $this->db->lastInsertId() : false;
        } catch (Throwable $e) {
            error_log('AgentService::addTask ' . $e->getMessage());
            return false;
        }
    }

    // ============ OPENROUTER INTEGRATION ============

    public function syncOpenrouterModels(): int
    {
        if (empty($this->openrouterKey)) {
            error_log('AgentService::syncOpenrouterModels - No API key provided');
            return 0;
        }

        try {
            $response = $this->openrouterCall('/models');
            if (!$response || !isset($response['data'])) {
                error_log('AgentService::syncOpenrouterModels - Invalid response');
                return 0;
            }

            $count = 0;
            foreach ($response['data'] as $model) {
                $stmt = $this->db->prepare('
                    INSERT INTO openrouter_models
                    (model_id, model_name, description, organization, capabilities, pricing_input, pricing_output, context_window)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        model_name = VALUES(model_name),
                        description = VALUES(description),
                        capabilities = VALUES(capabilities)
                ');
                $capabilities = [
                    'text' => !empty($model['per_1k_prompt_tokens']),
                    'image' => strpos($model['id'], 'vision') !== false,
                    'embedding' => strpos($model['id'], 'embed') !== false,
                ];
                $success = $stmt->execute([
                    $model['id'] ?? '',
                    $model['name'] ?? '',
                    $model['description'] ?? '',
                    $model['organization'] ?? '',
                    json_encode($capabilities),
                    (float)($model['pricing']['prompt'] ?? 0),
                    (float)($model['pricing']['completion'] ?? 0),
                    (int)($model['context_length'] ?? 4096),
                ]);
                if ($success) {
                    $count++;
                }
            }
            return $count;
        } catch (Throwable $e) {
            error_log('AgentService::syncOpenrouterModels ' . $e->getMessage());
            return 0;
        }
    }

    public function getAvailableModels(string $capability = ''): array
    {
        try {
            $query = 'SELECT * FROM openrouter_models WHERE is_available = 1';
            if (!empty($capability)) {
                $query .= ' AND JSON_CONTAINS(capabilities, \'true\', \'$.' . addslashes($capability) . '\')';
            }
            $query .= ' ORDER BY organization, model_name';
            $stmt = $this->db->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
        } catch (Throwable $e) {
            error_log('AgentService::getAvailableModels ' . $e->getMessage());
            return [];
        }
    }

    // ============ EXECUTION ============

    public function executeAgent(int $agentId, string $task, array $input = []): array
    {
        $agent = $this->getAgent($agentId);
        if (!$agent) {
            return ['success' => false, 'error' => 'Agent not found'];
        }

        $model = $this->getPrimaryModel($agentId);
        if (!$model) {
            return ['success' => false, 'error' => 'No model assigned to agent'];
        }

        $startTime = microtime(true);
        try {
            $result = $this->openrouterCall('/chat/completions', 'POST', [
                'model' => $model['model_id'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $agent['system_prompt'] ?? 'You are a helpful assistant.',
                    ],
                    [
                        'role' => 'user',
                        'content' => json_encode(['task' => $task, 'input' => $input]),
                    ],
                ],
                'temperature' => (float)$model['temperature'],
                'max_tokens' => (int)$model['max_tokens'],
            ]);

            $executionTime = intval((microtime(true) - $startTime) * 1000);
            $output = $result['choices'][0]['message']['content'] ?? '';
            $tokensUsed = ($result['usage']['total_tokens'] ?? 0);

            $this->logExecution($agentId, $task, $input, $output, $model['model_id'], $tokensUsed, $executionTime, 'success');

            return [
                'success' => true,
                'output' => $output,
                'tokens' => $tokensUsed,
                'time_ms' => $executionTime,
                'model' => $model['model_id'],
            ];
        } catch (Throwable $e) {
            $executionTime = intval((microtime(true) - $startTime) * 1000);
            $error = $e->getMessage();
            $this->logExecution($agentId, $task, $input, '', $model['model_id'], 0, $executionTime, 'error', $error);

            return [
                'success' => false,
                'error' => $error,
                'model' => $model['model_id'],
            ];
        }
    }

    private function logExecution(int $agentId, string $task, array $input, string $output, string $modelUsed, int $tokensUsed, int $timeMs, string $status, string $error = ''): void
    {
        try {
            $stmt = $this->db->prepare('
                INSERT INTO agent_logs (agent_id, task_name, input_data, output_data, model_used, tokens_used, execution_time_ms, status, error_message)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $agentId,
                $task,
                json_encode($input),
                $output,
                $modelUsed,
                $tokensUsed,
                $timeMs,
                $status,
                $error,
            ]);
        } catch (Throwable $e) {
            error_log('AgentService::logExecution ' . $e->getMessage());
        }
    }

    // ============ API CALLS ============

    private function openrouterCall(string $endpoint, string $method = 'GET', array $data = []): ?array
    {
        if (empty($this->openrouterKey)) {
            return null;
        }

        $url = 'https://openrouter.ai/api/v1' . $endpoint;
        $headers = [
            'Authorization: Bearer ' . $this->openrouterKey,
            'Content-Type: application/json',
            'HTTP-Referer: https://eduardo-desul-immobilier.fr',
            'X-Title: Site Immo Agent',
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            return json_decode($response, true);
        }
        return null;
    }
}

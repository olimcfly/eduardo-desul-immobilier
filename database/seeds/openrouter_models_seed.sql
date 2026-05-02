-- Modèles Open Source Populaires via OpenRouter
-- À charger manuellement ou via AgentService::syncOpenrouterModels()

INSERT IGNORE INTO openrouter_models (model_id, model_name, description, organization, capabilities, context_window) VALUES

-- ============ LLAMA (Meta) ============
('meta-llama/llama-3-70b-instruct', 'Llama 3 70B Instruct', 'Modèle très capable, excellent français, instructions claires', 'Meta', JSON_OBJECT('text', true, 'image', false, 'embedding', false, 'audio', false, 'video', false, 'rerank', false, 'speech', false), 8192),
('meta-llama/llama-3-8b-instruct', 'Llama 3 8B Instruct', 'Version légère et rapide, bon pour latence faible', 'Meta', JSON_OBJECT('text', true, 'image', false, 'embedding', false, 'audio', false, 'video', false, 'rerank', false, 'speech', false), 8192),
('meta-llama/llama-2-70b-chat', 'Llama 2 70B Chat', 'Modèle stable, bon en français', 'Meta', JSON_OBJECT('text', true, 'image', false, 'embedding', false, 'audio', false, 'video', false, 'rerank', false, 'speech', false), 4096),
('meta-llama/llama-2-13b-chat', 'Llama 2 13B Chat', 'Version moyenne, équilibrée', 'Meta', JSON_OBJECT('text', true, 'image', false, 'embedding', false, 'audio', false, 'video', false, 'rerank', false, 'speech', false), 4096),

-- ============ MISTRAL (Mistral AI) ============
('mistralai/mistral-medium', 'Mistral Medium', 'Excellent rapport qualité/vitesse, très rapide', 'Mistral AI', JSON_OBJECT('text', true, 'image', false, 'embedding', false, 'audio', false, 'video', false, 'rerank', false, 'speech', false), 8192),
('mistralai/mistral-7b-instruct', 'Mistral 7B Instruct', 'Léger et rapide, bon pour chat', 'Mistral AI', JSON_OBJECT('text', true, 'image', false, 'embedding', false, 'audio', false, 'video', false, 'rerank', false, 'speech', false), 8192),
('mistralai/mixtral-8x7b-instruct', 'Mixtral 8x7B Instruct', 'Modèle mixture of experts très performant', 'Mistral AI', JSON_OBJECT('text', true, 'image', false, 'embedding', false, 'audio', false, 'video', false, 'rerank', false, 'speech', false), 32768),

-- ============ QWEN (Alibaba) ============
('qwen/qwen-32b-chat', 'Qwen 32B Chat', 'Très bon français, multilingue, puissant', 'Alibaba', JSON_OBJECT('text', true, 'image', false, 'embedding', false, 'audio', false, 'video', false, 'rerank', false, 'speech', false), 8192),
('qwen/qwen-14b-chat', 'Qwen 14B Chat', 'Excellent français, équilibrée', 'Alibaba', JSON_OBJECT('text', true, 'image', false, 'embedding', false, 'audio', false, 'video', false, 'rerank', false, 'speech', false), 8192),

-- ============ DEEPSEEK ============
('deepseek/deepseek-chat', 'DeepSeek Chat', 'Bon pour français et code, très efficace', 'DeepSeek', JSON_OBJECT('text', true, 'image', false, 'embedding', false, 'audio', false, 'video', false, 'rerank', false, 'speech', false), 8192),

-- ============ PHI (Microsoft) ============
('microsoft/phi-3-mini-128k-instruct', 'Phi 3 Mini', 'Petit modèle très efficace, contexte large', 'Microsoft', JSON_OBJECT('text', true, 'image', false, 'embedding', false, 'audio', false, 'video', false, 'rerank', false, 'speech', false), 128000),
('microsoft/phi-3-medium-128k-instruct', 'Phi 3 Medium', 'Modèle équilibré, contexte très large', 'Microsoft', JSON_OBJECT('text', true, 'image', false, 'embedding', false, 'audio', false, 'video', false, 'rerank', false, 'speech', false), 128000),

-- ============ NEURAL CHAT ============
('intel/neural-chat-7b', 'Neural Chat 7B', 'Chat optimisé, bon français', 'Intel', JSON_OBJECT('text', true, 'image', false, 'embedding', false, 'audio', false, 'video', false, 'rerank', false, 'speech', false), 8192),

-- ============ EMBEDDINGS ============
('mixedbread-ai/mxbai-embed-large', 'MXBai Embed Large', 'Meilleur pour embeddings français, 1024 dim', 'MixedBread AI', JSON_OBJECT('text', false, 'image', false, 'embedding', true, 'audio', false, 'video', false, 'rerank', false, 'speech', false), 512),
('nomic-ai/nomic-embed-text-v1', 'Nomic Embed Text', 'Bon embeddings généraliste, 768 dim', 'Nomic AI', JSON_OBJECT('text', false, 'image', false, 'embedding', true, 'audio', false, 'video', false, 'rerank', false, 'speech', false), 512),
('sentence-transformers/all-minilm-l6-v2', 'All MiniLM L6 v2', 'Embeddings léger, 384 dim', 'Sentence Transformers', JSON_OBJECT('text', false, 'image', false, 'embedding', true, 'audio', false, 'video', false, 'rerank', false, 'speech', false), 256),

-- ============ RERANKING ============
('mixedbread-ai/mxbai-rerank-base-v1', 'MXBai Rerank Base', 'Classement pertinence résultats de recherche', 'MixedBread AI', JSON_OBJECT('text', false, 'image', false, 'embedding', false, 'audio', false, 'video', false, 'rerank', true, 'speech', false), 512),
('jina/jina-reranker-v1-base-en', 'Jina Reranker Base', 'Reranking de résultats de recherche', 'Jina AI', JSON_OBJECT('text', false, 'image', false, 'embedding', false, 'audio', false, 'video', false, 'rerank', true, 'speech', false), 512),

-- ============ VISION (Accès via OpenRouter) ============
('claude-3-opus', 'Claude 3 Opus', 'Vision très capable, analyse détaillée images', 'Anthropic', JSON_OBJECT('text', true, 'image', true, 'embedding', false, 'audio', false, 'video', false, 'rerank', false, 'speech', false), 200000),
('claude-3-sonnet', 'Claude 3 Sonnet', 'Vision équilibrée, bon français', 'Anthropic', JSON_OBJECT('text', true, 'image', true, 'embedding', false, 'audio', false, 'video', false, 'rerank', false, 'speech', false), 200000),
('gpt-4-vision-preview', 'GPT-4 Vision', 'Vision très précise et détaillée', 'OpenAI', JSON_OBJECT('text', true, 'image', true, 'embedding', false, 'audio', false, 'video', false, 'rerank', false, 'speech', false), 128000),

-- ============ SPEECH ============
('openai/whisper-large-v3', 'Whisper Large V3', 'Speech-to-text très bon français', 'OpenAI', JSON_OBJECT('text', false, 'image', false, 'embedding', false, 'audio', true, 'video', false, 'rerank', false, 'speech', true), 1024),
('openai/whisper-base', 'Whisper Base', 'Speech-to-text plus léger', 'OpenAI', JSON_OBJECT('text', false, 'image', false, 'embedding', false, 'audio', true, 'video', false, 'rerank', false, 'speech', true), 1024);

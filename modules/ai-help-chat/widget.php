<?php

declare(strict_types=1);

function renderAiHelpChatWidget(AiHelpChatService $service, array $context = []): void
{
    $user = Auth::user() ?? ['role' => 'guest'];
    $role = (string) ($user['role'] ?? 'guest');
    if (!$service->canUserUseChat($role)) {
        return;
    }

    $settings = $service->getSettings();
    $assistantName = (string) ($settings['assistant_name'] ?? 'Assistant Aide IA');
    $module = preg_replace('/[^a-z0-9_-]/', '', (string) ($context['module'] ?? 'dashboard'));
    $page = mb_substr((string) ($context['page'] ?? ''), 0, 120);
    ?>
    <div class="ai-help-chat" id="ai-help-chat-root" data-module="<?= htmlspecialchars($module) ?>" data-page="<?= htmlspecialchars($page) ?>">
        <button class="ai-help-chat__fab" type="button" id="ai-help-chat-toggle" aria-label="Ouvrir le chat d’aide IA">
            <i class="fas fa-comments"></i>
        </button>

        <section class="ai-help-chat__panel" id="ai-help-chat-panel" aria-hidden="true">
            <header class="ai-help-chat__header">
                <h3><?= htmlspecialchars($assistantName) ?></h3>
                <button type="button" class="ai-help-chat__close" id="ai-help-chat-close" aria-label="Fermer">×</button>
            </header>

            <div class="ai-help-chat__messages" id="ai-help-chat-messages">
                <article class="ai-help-chat__message ai-help-chat__message--assistant">
                    Je peux vous aider à comprendre ce module et vous montrer quoi faire ensuite.
                </article>
            </div>

            <div class="ai-help-chat__state" id="ai-help-chat-state" hidden></div>

            <form class="ai-help-chat__composer" id="ai-help-chat-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                <input type="text" name="message" id="ai-help-chat-input" placeholder="Posez votre question sur ce module…" maxlength="1000" required>
                <button type="submit">Envoyer</button>
            </form>
        </section>
    </div>

    <style>
        .ai-help-chat{position:fixed;right:18px;bottom:18px;z-index:1100;font-family:Inter,Segoe UI,sans-serif}
        .ai-help-chat__fab{width:56px;height:56px;border-radius:999px;border:none;background:#1f3a5f;color:#fff;box-shadow:0 8px 24px rgba(15,23,42,.25);cursor:pointer}
        .ai-help-chat__panel{position:absolute;right:0;bottom:66px;width:min(420px,calc(100vw - 24px));max-height:70vh;background:#fff;border-radius:14px;border:1px solid #dbe2ea;box-shadow:0 20px 40px rgba(0,0,0,.15);display:none;overflow:hidden}
        .ai-help-chat__panel.is-open{display:flex;flex-direction:column}
        .ai-help-chat__header{display:flex;justify-content:space-between;align-items:center;padding:.8rem 1rem;background:linear-gradient(135deg,#1f3a5f,#355f92);color:#fff}
        .ai-help-chat__header h3{font-size:.95rem;margin:0}
        .ai-help-chat__close{border:none;background:transparent;color:#fff;font-size:1.2rem;cursor:pointer}
        .ai-help-chat__messages{padding:.9rem;overflow:auto;display:flex;flex-direction:column;gap:.55rem;min-height:220px;max-height:42vh;background:#f8fafc}
        .ai-help-chat__message{padding:.62rem .7rem;border-radius:10px;font-size:.88rem;line-height:1.45;white-space:pre-wrap}
        .ai-help-chat__message--assistant{background:#e6edf5;color:#1e293b;align-self:flex-start;max-width:92%}
        .ai-help-chat__message--user{background:#1f3a5f;color:#fff;align-self:flex-end;max-width:90%}
        .ai-help-chat__state{padding:.45rem .9rem;font-size:.8rem;color:#64748b;background:#fff;border-top:1px solid #e2e8f0}
        .ai-help-chat__state.error{color:#b91c1c}
        .ai-help-chat__composer{display:flex;gap:.5rem;padding:.7rem;border-top:1px solid #e2e8f0;background:#fff}
        .ai-help-chat__composer input{flex:1;padding:.6rem .7rem;border:1px solid #cbd5e1;border-radius:8px;font-size:.88rem}
        .ai-help-chat__composer button{background:#1f3a5f;color:#fff;border:none;padding:.6rem .9rem;border-radius:8px;cursor:pointer}
        .ai-help-chat__resources{margin-top:.4rem;display:grid;gap:.3rem}
        .ai-help-chat__resources a{font-size:.82rem;color:#1d4ed8;text-decoration:none}
    </style>

    <script>
        (() => {
            const root = document.getElementById('ai-help-chat-root');
            if (!root) return;

            const panel = document.getElementById('ai-help-chat-panel');
            const toggle = document.getElementById('ai-help-chat-toggle');
            const closeBtn = document.getElementById('ai-help-chat-close');
            const form = document.getElementById('ai-help-chat-form');
            const input = document.getElementById('ai-help-chat-input');
            const messages = document.getElementById('ai-help-chat-messages');
            const state = document.getElementById('ai-help-chat-state');
            let conversationId = 0;

            const setState = (text = '', isError = false) => {
                if (!text) {
                    state.hidden = true;
                    state.classList.remove('error');
                    state.textContent = '';
                    return;
                }
                state.hidden = false;
                state.textContent = text;
                state.classList.toggle('error', Boolean(isError));
            };

            const appendMessage = (content, sender = 'assistant') => {
                const bubble = document.createElement('article');
                bubble.className = `ai-help-chat__message ai-help-chat__message--${sender}`;
                bubble.textContent = content;
                messages.appendChild(bubble);
                messages.scrollTop = messages.scrollHeight;
                return bubble;
            };

            const appendResources = (items = []) => {
                if (!items.length) return;
                const box = document.createElement('div');
                box.className = 'ai-help-chat__resources';
                items.forEach((item) => {
                    const a = document.createElement('a');
                    a.href = item.url || '#';
                    a.textContent = `📘 ${item.title || 'Ressource interne'}`;
                    box.appendChild(a);
                });
                messages.appendChild(box);
                messages.scrollTop = messages.scrollHeight;
            };

            toggle.addEventListener('click', () => {
                panel.classList.add('is-open');
                panel.setAttribute('aria-hidden', 'false');
                setTimeout(() => input.focus(), 70);
            });

            closeBtn.addEventListener('click', () => {
                panel.classList.remove('is-open');
                panel.setAttribute('aria-hidden', 'true');
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const text = input.value.trim();
                if (!text) return;

                appendMessage(text, 'user');
                input.value = '';
                setState('Chargement…');

                const payload = new FormData(form);
                payload.append('api_action', 'send_message');
                payload.append('conversation_id', String(conversationId));
                payload.append('context_module', root.dataset.module || 'dashboard');
                payload.append('context_page', root.dataset.page || '');

                try {
                    const response = await fetch('/admin?module=ai-help-chat&action=api', {
                        method: 'POST',
                        body: payload,
                    });

                    const data = await response.json();
                    if (!data.success) {
                        setState(data.error || 'Erreur du chat.', true);
                        return;
                    }

                    conversationId = Number(data.conversation_id || 0);
                    const assistant = data.assistant || {};
                    appendMessage(assistant.answer || 'Je n’ai pas trouvé de réponse exploitable.', 'assistant');
                    appendResources(assistant.resources || []);
                    if (assistant.module_cta && assistant.module_cta.url) {
                        appendResources([{ title: assistant.module_cta.label || 'Ouvrir le module', url: assistant.module_cta.url }]);
                    }
                    setState('');
                } catch (error) {
                    setState('Impossible de joindre le service IA.', true);
                }
            });
        })();
    </script>
    <?php
}

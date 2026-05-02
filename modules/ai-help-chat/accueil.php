<?php

declare(strict_types=1);

require_once __DIR__ . '/permissions.php';
require_once __DIR__ . '/service.php';
require_once __DIR__ . '/api.php';
require_once __DIR__ . '/widget.php';
require_once __DIR__ . '/admin-settings.php';
require_once __DIR__ . '/index.php';

$pageTitle = 'Chat d’aide IA';
$pageDescription = 'Assistant contextuel interne connecté aux ressources CRM.';

function renderContent(): void
{
    $service = new AiHelpChatService(db());
    $action = preg_replace('/[^a-z_]/', '', (string) ($_GET['action'] ?? 'index'));

    if ($action === 'api') {
        handleAiHelpChatApi($service);
        return;
    }

    if ($action === 'settings') {
        renderAiHelpChatAdminSettings($service);
        return;
    }

    if ($action === 'index') {
        $user = Auth::user() ?? ['role' => 'guest'];
        $role = (string) ($user['role'] ?? 'guest');
        $settings = $service->getSettings();
        ?>
        <style>
            .chat-page { display: grid; gap: 22px; }
            .chat-hero { background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%); border-radius: 16px; padding: 24px 20px; color: #fff; box-shadow: 0 4px 20px rgba(15, 34, 55, .18); }
            .chat-hero h1 { margin: 0 0 10px; font-size: clamp(24px, 4vw, 30px); line-height: 1.24; color: #fff; }
            .chat-hero p { margin: 0; color: rgba(255,255,255,.78); font-size: 15px; line-height: 1.65; }
            .chat-modules { display: grid; grid-template-columns: 1fr; gap: 12px; }
            @media (min-width: 768px) { .chat-modules { grid-template-columns: repeat(2, 1fr); } }
            .chat-card { background: #fff; border-radius: 16px; padding: 18px; box-shadow: 0 1px 8px rgba(15,23,42,.08); border: 1px solid #e2e8f0; text-decoration: none; color: inherit; }
            .chat-card h3 { margin: 0 0 8px; font-size: 16px; color: #0f172a; font-weight: 600; }
            .chat-card p { margin: 0; font-size: 14px; color: #475569; line-height: 1.6; }
            .chat-final-cta { background: #fff; border: 1px solid #e8edf4; border-radius: 14px; padding: 1.05rem 1rem; display: grid; gap: .7rem; }
            .chat-final-cta h2 { margin: 0; font-size: 1.2rem; color: #111827; font-weight: 700; }
            .chat-btn { display: inline-flex; align-items: center; gap: .5rem; width: max-content; text-decoration: none; background: #c9a84c; color: #10253c; font-weight: 700; border-radius: 10px; padding: .58rem .92rem; margin-top: .7rem; }
            @media (min-width: 768px) { .chat-hero { padding: 2rem 2.1rem; } .chat-hero h1 { font-size: 2rem; } }
        </style>

        <section class="chat-page">
            <header class="chat-hero">
                <h1>Chat d’aide IA</h1>
                <p>Assistant contextuel connecté au centre d’aide et aux ressources internes du CRM.</p>
            </header>

            <div class="chat-modules">
                <article class="chat-card" style="background:#f0f7ff;border-color:#cfe2ff;">
                    <h3><i class="fas fa-comments"></i> Assistant</h3>
                    <p><?= htmlspecialchars((string) ($settings['assistant_name'] ?? 'Assistant Aide IA')) ?></p>
                    <small style="color:#64748b;display:block;margin-top:.5rem;">Mode : <?= htmlspecialchars((string) ($settings['response_mode'] ?? 'guide')) ?></small>
                </article>

                <article class="chat-card" style="background:#f0fdf4;border-color:#bbf7d0;">
                    <h3><i class="fas fa-check-circle"></i> Statut</h3>
                    <p><?= $service->canUserUseChat($role) ? 'Disponible pour votre rôle.' : 'Non disponible pour votre rôle.' ?></p>
                    <small style="color:#64748b;display:block;margin-top:.5rem;">Rôle : <strong><?= htmlspecialchars($role) ?></strong></small>
                </article>

                <article class="chat-card" style="background:#fef3c7;border-color:#fcd34d;">
                    <h3><i class="fas fa-book"></i> Sources</h3>
                    <p>Centre d’aide, guides internes, docs modules.</p>
                    <small style="color:#64748b;display:block;margin-top:.5rem;">Suggestions contextuelles activables.</small>
                </article>

                <?php if (aiHelpChatCanConfigure()): ?>
                    <a href="/admin/?module=ai-help-chat&action=settings" class="chat-card" style="background:#f3e8ff;border-color:#e9d5ff;">
                        <h3><i class="fas fa-sliders"></i> Configuration</h3>
                        <p>Ajustez les paramètres de l’assistant IA.</p>
                        <small style="color:#64748b;display:block;margin-top:.5rem;">Rôle superuser détecté.</small>
                    </a>
                <?php endif; ?>
            </div>

            <section class="chat-final-cta">
                <div>
                    <h2>Utiliser l’assistant</h2>
                    <p>Posez vos questions et recevez des réponses contextuelles instantanées.</p>
                </div>
                <a href="/admin/?module=ai-help-chat" class="chat-btn"><i class="fas fa-rocket"></i> Démarrer</a>
            </section>
        </section>
        <?php
        return;
    }

    renderAiHelpChatIndex($service);
}

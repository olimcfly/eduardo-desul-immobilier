<?php

declare(strict_types=1);

function renderAiHelpChatIndex(AiHelpChatService $service): void
{
    $user = Auth::user() ?? ['role' => 'guest'];
    $role = (string) ($user['role'] ?? 'guest');
    $allowed = $service->canUserUseChat($role);
    $settings = $service->getSettings();
    ?>
    <section class="ai-help-hub">
        <h1><i class="fas fa-comments"></i> Chat d’aide IA</h1>
        <p>Assistant contextuel connecté au centre d’aide et aux ressources internes du CRM.</p>

        <div class="ai-help-cards">
            <article>
                <h3>Statut</h3>
                <p><?= $allowed ? 'Disponible pour votre rôle.' : 'Non disponible pour votre rôle.' ?></p>
                <small>Rôle actuel : <strong><?= htmlspecialchars($role) ?></strong></small>
            </article>
            <article>
                <h3>Assistant</h3>
                <p><?= htmlspecialchars((string) ($settings['assistant_name'] ?? 'Assistant Aide IA')) ?></p>
                <small>Mode : <?= htmlspecialchars((string) ($settings['response_mode'] ?? 'guide')) ?> · Ton : <?= htmlspecialchars((string) ($settings['tone'] ?? 'professionnel')) ?></small>
            </article>
            <article>
                <h3>Sources</h3>
                <p>Centre d’aide, guides internes, docs modules, vidéos futures.</p>
                <small>Suggestions contextuelles activables depuis la configuration.</small>
            </article>
        </div>

        <?php if (aiHelpChatCanConfigure()): ?>
            <a class="ai-help-link" href="/admin?module=ai-help-chat&action=settings">Ouvrir la configuration superuser</a>
        <?php endif; ?>
    </section>

    <style>
        .ai-help-hub{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:1.4rem}
        .ai-help-hub h1{margin:.2rem 0 .55rem;font-size:1.45rem;color:#0f172a}
        .ai-help-hub p{color:#475569}
        .ai-help-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.85rem;margin-top:1rem}
        .ai-help-cards article{border:1px solid #e2e8f0;border-radius:12px;padding:.9rem;background:#f8fafc}
        .ai-help-cards h3{margin:0 0 .4rem;color:#1e293b}
        .ai-help-cards small{color:#64748b}
        .ai-help-link{display:inline-flex;margin-top:1rem;background:#1f3a5f;color:#fff;padding:.65rem .95rem;border-radius:10px;text-decoration:none}
    </style>
    <?php
}

<?php

declare(strict_types=1);

function aiHelpChatCurrentUser(): array
{
    return Auth::user() ?? ['id' => 0, 'role' => 'guest', 'name' => '', 'email' => ''];
}

function aiHelpChatRole(): string
{
    return (string) (aiHelpChatCurrentUser()['role'] ?? 'guest');
}

function aiHelpChatIsSuperuser(): bool
{
    return aiHelpChatRole() === 'superadmin';
}

function aiHelpChatIsAdmin(): bool
{
    return aiHelpChatRole() === 'admin';
}

function aiHelpChatIsUser(): bool
{
    return aiHelpChatRole() === 'user';
}

function aiHelpChatRequireSuperuser(): void
{
    if (!aiHelpChatIsSuperuser()) {
        http_response_code(403);
        die('Accès réservé au superuser.');
    }
}

function aiHelpChatCanConfigure(): bool
{
    return aiHelpChatIsSuperuser();
}

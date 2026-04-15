<?php

declare(strict_types=1);

class MessageRepository
{
    public function __construct(private PDO $pdo) {}

    // ── SCHEMA ──────────────────────────────────────────────────

    public function ensureSchema(): void
    {
        $sql = file_get_contents(__DIR__ . '/../sql/messagerie.sql');
        if ($sql === false) return;
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
            try { $this->pdo->exec($stmt); } catch (PDOException) {}
        }
    }

    // ── THREADS ─────────────────────────────────────────────────

    public function getThreads(int $userId, int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("
            SELECT t.*,
                   (SELECT COUNT(*) FROM messages m WHERE m.thread_id = t.id) AS message_count
              FROM message_threads t
             WHERE t.user_id = ?
             ORDER BY t.last_message_at DESC
             LIMIT ? OFFSET ?
        ");
        $stmt->execute([$userId, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getThread(int $userId, int $threadId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM message_threads WHERE id = ? AND user_id = ?");
        $stmt->execute([$threadId, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getThreadByEmail(int $userId, string $email): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM message_threads WHERE user_id = ? AND contact_email = ?");
        $stmt->execute([$userId, strtolower(trim($email))]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function upsertThread(int $userId, string $email, string $name, string $subject, string $snippet): int
    {
        $email = strtolower(trim($email));
        $existing = $this->getThreadByEmail($userId, $email);

        if ($existing) {
            $stmt = $this->pdo->prepare("
                UPDATE message_threads
                   SET contact_name = ?, subject = ?, snippet = ?, last_message_at = NOW()
                 WHERE id = ?
            ");
            $stmt->execute([$name ?: $existing['contact_name'], $subject, $snippet, $existing['id']]);
            return (int) $existing['id'];
        }

        // Try to link to an existing contact or CRM lead
        [$contactId, $contactType] = $this->resolveContact($userId, $email);

        $stmt = $this->pdo->prepare("
            INSERT INTO message_threads
              (user_id, contact_id, contact_type, contact_email, contact_name, subject, snippet, last_message_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $contactId, $contactType, $email, $name, $subject, $snippet]);
        return (int) $this->pdo->lastInsertId();
    }

    public function incrementUnread(int $threadId): void
    {
        $this->pdo->prepare("UPDATE message_threads SET unread_count = unread_count + 1 WHERE id = ?")
                  ->execute([$threadId]);
    }

    public function resetUnread(int $threadId): void
    {
        $this->pdo->prepare("UPDATE message_threads SET unread_count = 0 WHERE id = ?")
                  ->execute([$threadId]);
    }

    public function getTotalUnread(int $userId): int
    {
        $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(unread_count),0) FROM message_threads WHERE user_id = ?");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    // ── MESSAGES ────────────────────────────────────────────────

    public function getMessages(int $threadId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM messages WHERE thread_id = ? ORDER BY created_at ASC
        ");
        $stmt->execute([$threadId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existsByGmailId(string $gmailMessageId): bool
    {
        $stmt = $this->pdo->prepare("SELECT id FROM messages WHERE gmail_message_id = ? LIMIT 1");
        $stmt->execute([$gmailMessageId]);
        return (bool) $stmt->fetchColumn();
    }

    public function insertMessage(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT IGNORE INTO messages
              (thread_id, user_id, gmail_message_id, direction, from_email, from_name,
               to_email, subject, body_html, body_text, status, is_read, sent_at, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['thread_id'],
            $data['user_id'],
            $data['gmail_message_id'] ?? null,
            $data['direction'],
            $data['from_email'],
            $data['from_name'] ?? '',
            $data['to_email'],
            $data['subject'] ?? '',
            $data['body_html'] ?? null,
            $data['body_text'] ?? null,
            $data['status'] ?? 'received',
            $data['is_read'] ?? 0,
            $data['sent_at'] ?? null,
            $data['created_at'] ?? date('Y-m-d H:i:s'),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function markThreadRead(int $userId, int $threadId): void
    {
        $this->pdo->prepare("UPDATE messages SET is_read = 1 WHERE thread_id = ? AND user_id = ?")
                  ->execute([$threadId, $userId]);
        $this->resetUnread($threadId);
    }

    // ── PRIVATE ─────────────────────────────────────────────────

    private function resolveContact(int $userId, string $email): array
    {
        // Try crm_leads
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM crm_leads WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $id = $stmt->fetchColumn();
            if ($id) return [(int)$id, 'crm'];
        } catch (Throwable) {}

        // Try contacts
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM contacts WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $id = $stmt->fetchColumn();
            if ($id) return [(int)$id, 'contact'];
        } catch (Throwable) {}

        return [null, null];
    }
}

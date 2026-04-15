<?php

declare(strict_types=1);

class ImapService
{
    public function __construct(
        private MessageRepository $repo,
        private int $userId
    ) {}

    // ── CONFIG ───────────────────────────────────────────────────

    public function isConfigured(): bool
    {
        return $this->getHost() !== '' && $this->getUser() !== '';
    }

    public function getAdvisorEmail(): string
    {
        return (string) (setting('smtp_user', '') ?: ($_ENV['SMTP_USER'] ?? ''));
    }

    private function getHost(): string
    {
        return trim((string) (setting('imap_host', '') ?: ($_ENV['IMAP_HOST'] ?? setting('smtp_host', '') ?: ($_ENV['SMTP_HOST'] ?? ''))));
    }

    private function getPort(): int
    {
        $p = (int) (setting('imap_port', '0') ?: ($_ENV['IMAP_PORT'] ?? 0));
        return $p > 0 ? $p : 993;
    }

    private function getSecure(): string
    {
        $s = strtolower(trim((string) (setting('imap_secure', '') ?: ($_ENV['IMAP_SECURE'] ?? 'ssl'))));
        return in_array($s, ['ssl', 'tls', 'none'], true) ? $s : 'ssl';
    }

    private function getUser(): string
    {
        return trim((string) (setting('imap_user', '') ?: ($_ENV['IMAP_USER'] ?? setting('smtp_user', '') ?: ($_ENV['SMTP_USER'] ?? ''))));
    }

    private function getPass(): string
    {
        return (string) (setting('imap_pass', '') ?: ($_ENV['IMAP_PASS'] ?? setting('smtp_pass', '') ?: ($_ENV['SMTP_PASS'] ?? '')));
    }

    private function buildMailbox(): string
    {
        $host   = $this->getHost();
        $port   = $this->getPort();
        $secure = $this->getSecure();

        $flags = '/imap';
        if ($secure === 'ssl') $flags .= '/ssl';
        if ($secure === 'tls') $flags .= '/tls';
        $flags .= '/novalidate-cert';

        return '{' . $host . ':' . $port . $flags . '}INBOX';
    }

    // ── TEST ─────────────────────────────────────────────────────

    public function testConnection(): int
    {
        $mailbox = imap_open(
            $this->buildMailbox(),
            $this->getUser(),
            $this->getPass(),
            0, 1,
            ['DISABLE_AUTHENTICATOR' => 'GSSAPI']
        );
        if ($mailbox === false) {
            throw new RuntimeException('Connexion IMAP échouée : ' . imap_last_error());
        }
        $count = imap_num_msg($mailbox);
        imap_close($mailbox);
        return $count;
    }

    // ── SYNC ─────────────────────────────────────────────────────

    /**
     * Lit les N derniers emails via IMAP et les stocke en DB.
     * Retourne le nombre de nouveaux messages importés.
     */
    public function syncInbox(int $limit = 100): int
    {
        $mailbox = imap_open(
            $this->buildMailbox(),
            $this->getUser(),
            $this->getPass(),
            0,
            1,
            ['DISABLE_AUTHENTICATOR' => 'GSSAPI']
        );

        if ($mailbox === false) {
            throw new RuntimeException('Connexion IMAP échouée : ' . imap_last_error());
        }

        try {
            $msgCount = imap_num_msg($mailbox);
            if ($msgCount === 0) return 0;

            // Fetch les N derniers (du plus récent au plus ancien)
            $from = max(1, $msgCount - $limit + 1);
            $overviews = imap_fetch_overview($mailbox, $from . ':' . $msgCount, 0);
            if (!$overviews) return 0;

            // Tri décroissant (plus récent en premier)
            usort($overviews, fn($a, $b) => $b->uid <=> $a->uid);

            $imported = 0;
            foreach ($overviews as $ov) {
                $uid    = (int) $ov->uid;
                $msgNum = imap_msgno($mailbox, $uid);

                // Déduplication via uid stocké comme gmail_message_id
                if ($this->repo->existsByGmailId('imap:' . $uid)) continue;

                $msg = $this->parseMessage($mailbox, $msgNum, $ov);
                if ($msg === null) continue;

                $this->importMessage($msg, $uid);
                $imported++;
            }

            return $imported;
        } finally {
            imap_close($mailbox);
        }
    }

    // ── SEND ─────────────────────────────────────────────────────

    /**
     * Envoie via MailService (SMTP déjà configuré) et stocke en local.
     */
    public function send(string $to, string $subject, string $bodyHtml): array
    {
        $sent = MailService::send($to, $subject, strip_tags($bodyHtml), $bodyHtml);

        if (!$sent) {
            return ['ok' => false, 'error' => 'Envoi SMTP échoué. Vérifiez la configuration dans Paramètres → SMTP.'];
        }

        $advisorEmail = $this->getAdvisorEmail();
        $fromName     = (string) setting('profil_nom', APP_NAME, $this->userId);

        $threadId = $this->repo->upsertThread(
            $this->userId,
            $to,
            $this->deriveNameFromEmail($to),
            $subject,
            substr(strip_tags($bodyHtml), 0, 160)
        );

        $this->repo->insertMessage([
            'thread_id'        => $threadId,
            'user_id'          => $this->userId,
            'gmail_message_id' => null,
            'direction'        => 'outbound',
            'from_email'       => $advisorEmail,
            'from_name'        => $fromName,
            'to_email'         => $to,
            'subject'          => $subject,
            'body_html'        => $bodyHtml,
            'body_text'        => strip_tags($bodyHtml),
            'status'           => 'sent',
            'is_read'          => 1,
            'sent_at'          => date('Y-m-d H:i:s'),
        ]);

        return ['ok' => true];
    }

    // ── PRIVATE — PARSING ────────────────────────────────────────

    private function parseMessage(\IMAP\Connection $mailbox, int $msgNum, object $ov): ?array
    {
        try {
            $header  = imap_headerinfo($mailbox, $msgNum);
            $subject = isset($ov->subject) ? $this->decodeHeader($ov->subject) : '(sans objet)';
            $from    = $header->from[0] ?? null;
            $to      = $header->to[0] ?? null;

            $fromEmail = $from ? strtolower(trim($from->mailbox . '@' . $from->host)) : '';
            $fromName  = $from ? $this->decodeHeader($from->personal ?? '') : '';
            $toEmail   = $to   ? strtolower(trim($to->mailbox   . '@' . $to->host))   : '';

            $date = null;
            if (!empty($ov->date)) {
                try { $date = (new DateTime($ov->date))->format('Y-m-d H:i:s'); } catch (Throwable) {}
            }

            $structure = imap_fetchstructure($mailbox, $msgNum);
            [$bodyHtml, $bodyText] = $this->extractBody($mailbox, $msgNum, $structure);

            return compact('fromEmail', 'fromName', 'toEmail', 'subject', 'bodyHtml', 'bodyText', 'date');
        } catch (Throwable $e) {
            error_log('ImapService parse error msg#' . $msgNum . ': ' . $e->getMessage());
            return null;
        }
    }

    private function importMessage(array $msg, int $uid): void
    {
        $advisorEmail = strtolower($this->getAdvisorEmail());
        $isInbound    = $msg['fromEmail'] !== $advisorEmail;
        $contactEmail = $isInbound ? $msg['fromEmail'] : $msg['toEmail'];
        $contactName  = $isInbound ? ($msg['fromName'] ?: $this->deriveNameFromEmail($contactEmail))
                                   : $this->deriveNameFromEmail($msg['toEmail']);

        $snippet = substr(strip_tags($msg['bodyText'] ?: $msg['bodyHtml'] ?: ''), 0, 160);

        $threadId = $this->repo->upsertThread(
            $this->userId,
            $contactEmail,
            $contactName,
            $msg['subject'],
            $snippet
        );

        $msgId = $this->repo->insertMessage([
            'thread_id'        => $threadId,
            'user_id'          => $this->userId,
            'gmail_message_id' => 'imap:' . $uid,
            'direction'        => $isInbound ? 'inbound' : 'outbound',
            'from_email'       => $msg['fromEmail'],
            'from_name'        => $msg['fromName'],
            'to_email'         => $msg['toEmail'],
            'subject'          => $msg['subject'],
            'body_html'        => $msg['bodyHtml'],
            'body_text'        => $msg['bodyText'],
            'status'           => $isInbound ? 'received' : 'sent',
            'is_read'          => $isInbound ? 0 : 1,
            'sent_at'          => $msg['date'],
            'created_at'       => $msg['date'] ?? date('Y-m-d H:i:s'),
        ]);

        if ($isInbound && $msgId > 0) {
            $this->repo->incrementUnread($threadId);
        }
    }

    private function extractBody(\IMAP\Connection $mailbox, int $msgNum, object $structure): array
    {
        $html = '';
        $text = '';
        $this->walkStructure($mailbox, $msgNum, $structure, $html, $text, '');
        if ($html === '' && $text !== '') {
            $html = nl2br(htmlspecialchars($text));
        }
        return [$html, $text];
    }

    private function walkStructure(\IMAP\Connection $mailbox, int $msgNum, object $part, string &$html, string &$text, string $partNum): void
    {
        $type    = $part->type ?? 0;
        $subtype = strtolower($part->subtype ?? '');

        // text/plain or text/html
        if ($type === 0) {
            $section = $partNum !== '' ? $partNum : '1';
            $body    = imap_fetchbody($mailbox, $msgNum, $section);
            $enc     = $part->encoding ?? 0;
            $body    = $this->decode($body, $enc);

            $charset = 'UTF-8';
            if (!empty($part->parameters)) {
                foreach ($part->parameters as $p) {
                    if (strtolower($p->attribute) === 'charset') {
                        $charset = strtoupper($p->value);
                        break;
                    }
                }
            }
            if ($charset !== 'UTF-8') {
                $body = mb_convert_encoding($body, 'UTF-8', $charset) ?: $body;
            }

            if ($subtype === 'html' && $html === '') {
                $html = $body;
            } elseif ($subtype === 'plain' && $text === '') {
                $text = $body;
            }
            return;
        }

        // multipart — recurse into parts
        if (!empty($part->parts)) {
            foreach ($part->parts as $i => $subPart) {
                $subNum = $partNum !== '' ? $partNum . '.' . ($i + 1) : (string)($i + 1);
                $this->walkStructure($mailbox, $msgNum, $subPart, $html, $text, $subNum);
            }
        }
    }

    private function decode(string $body, int $encoding): string
    {
        return match ($encoding) {
            3 => base64_decode($body),         // BASE64
            4 => quoted_printable_decode($body), // QP
            default => $body,
        };
    }

    private function decodeHeader(string $header): string
    {
        if ($header === '') return '';
        $decoded = imap_mime_header_decode($header);
        $result  = '';
        foreach ($decoded as $part) {
            $charset = $part->charset ?? 'UTF-8';
            $text    = $part->text ?? '';
            $result .= ($charset !== 'default' && $charset !== 'UTF-8')
                ? (mb_convert_encoding($text, 'UTF-8', $charset) ?: $text)
                : $text;
        }
        return $result;
    }

    private function deriveNameFromEmail(string $email): string
    {
        $local = explode('@', $email)[0] ?? $email;
        return ucwords(str_replace(['.', '_', '-'], ' ', $local));
    }
}

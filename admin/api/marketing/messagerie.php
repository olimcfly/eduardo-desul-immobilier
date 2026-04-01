<?php
/**
 * /admin/api/marketing/messagerie.php
 * API Messagerie CRM — IMAP sync + envoi SMTP + CRUD emails
 *
 * Standalone: /admin/api/marketing/messagerie.php?action=list
 * Via dispatcher: ?route=marketing.messagerie&action=list
 */

// Standalone mode: when called directly (not via dispatcher)
$_standalone = !isset($ctx);

if ($_standalone) {
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');

    require_once dirname(__DIR__, 2) . '/includes/init.php';

    // CSRF check for POST
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($csrfToken) || $csrfToken !== ($_SESSION['auth_csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
            exit;
        }
    }

    $ctx = [
        'pdo' => $pdo,
        'action' => $_GET['action'] ?? $_POST['action'] ?? 'list',
        'method' => $_SERVER['REQUEST_METHOD'],
        'params' => array_merge($_GET, $_POST),
        'admin_id' => $_SESSION['auth_admin_id'] ?? null,
    ];
}

function _msg_normalize_subject(string $subject): string
{
    $clean = trim($subject);
    $prev = '';
    while ($clean !== $prev) {
        $prev = $clean;
        $clean = preg_replace('/^(re|fw|fwd)\s*:\s*/i', '', $clean) ?? $clean;
        $clean = trim($clean);
    }
    return mb_strtolower($clean !== '' ? $clean : '(sans objet)');
}

function _msg_thread_key(array $email): string
{
    $subject = _msg_normalize_subject((string)($email['subject'] ?? ''));
    $contact = (int)($email['contact_id'] ?? 0);
    $lead = (int)($email['lead_id'] ?? 0);

    if ($contact > 0) {
        return 'contact:' . $contact . '|' . $subject;
    }
    if ($lead > 0) {
        return 'lead:' . $lead . '|' . $subject;
    }

    $from = mb_strtolower(trim((string)($email['from_email'] ?? '')));
    $to = mb_strtolower(trim((string)($email['to_email'] ?? '')));
    $pair = [$from, $to];
    sort($pair);

    return 'pair:' . implode('|', $pair) . '|sub:' . $subject;
}

function _msg_find_emails_for_thread(array $emails, string $threadKey): array
{
    $found = [];
    foreach ($emails as $email) {
        if (_msg_thread_key($email) === $threadKey) {
            $found[] = $email;
        }
    }

    usort($found, static function ($a, $b) {
        return strcmp((string)($a['sent_at'] ?? $a['created_at'] ?? ''), (string)($b['sent_at'] ?? $b['created_at'] ?? ''));
    });

    return $found;
}

function _msg_base_list_sql(array $p): array
{
    $folder = $p['folder'] ?? 'all';
    $search = trim((string)($p['search'] ?? ''));

    $sql = "SELECT * FROM crm_emails WHERE 1=1";
    $params = [];

    if ($folder === 'inbox') {
        $sql .= " AND direction='inbound'";
    } elseif ($folder === 'sent') {
        $sql .= " AND direction='outbound'";
    } elseif ($folder === 'starred') {
        $sql .= " AND is_starred=1";
    } elseif ($folder === 'unread') {
        $sql .= " AND is_read=0 AND direction='inbound'";
    } elseif ($folder !== 'all') {
        $sql .= " AND folder=?";
        $params[] = $folder;
    }

    if (!empty($search)) {
        $sql .= " AND (subject LIKE ? OR from_email LIKE ? OR from_name LIKE ? OR to_email LIKE ? OR body_text LIKE ?)";
        $s = "%{$search}%";
        $params = array_merge($params, [$s, $s, $s, $s, $s]);
    }

    return [$sql, $params];
}

function _msg_fetch_emails(PDO $pdo, array $p, int $limit = 300): array
{
    [$sql, $params] = _msg_base_list_sql($p);
    $limit = max(1, min($limit, 500));
    $sql .= " ORDER BY COALESCE(sent_at, created_at) DESC LIMIT {$limit}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll() ?: [];
}

// ── Handler function ──
function _messagerie_handler(array $ctx): array {
    $pdo = $ctx['pdo'];
    $action = $ctx['action'];
    $method = $ctx['method'];
    $p = $ctx['params'];

    require_once dirname(__DIR__, 3) . '/includes/classes/EmailService.php';
    $emailService = new EmailService($pdo);

    // ── stats ──
    if ($action === 'stats') {
        $s = ['total' => 0, 'unread' => 0, 'sent' => 0, 'inbox' => 0];
        try { $s['total'] = (int)$pdo->query("SELECT COUNT(*) FROM crm_emails")->fetchColumn(); } catch (Exception $e) {}
        try { $s['unread'] = (int)$pdo->query("SELECT COUNT(*) FROM crm_emails WHERE is_read=0 AND direction='inbound'")->fetchColumn(); } catch (Exception $e) {}
        try { $s['sent'] = (int)$pdo->query("SELECT COUNT(*) FROM crm_emails WHERE direction='outbound'")->fetchColumn(); } catch (Exception $e) {}
        try { $s['inbox'] = (int)$pdo->query("SELECT COUNT(*) FROM crm_emails WHERE direction='inbound'")->fetchColumn(); } catch (Exception $e) {}
        return ['success' => true, 'stats' => $s];
    }

    // ── list (legacy emails) ──
    if ($action === 'list') {
        $folder = $p['folder'] ?? 'all';
        $limit = min((int)($p['limit'] ?? 50), 200);
        $offset = (int)($p['offset'] ?? 0);
        $search = $p['search'] ?? '';

        $sql = "SELECT id, contact_id, lead_id, direction, from_email, from_name, to_email, to_name, subject, is_read, is_starred, folder, message_id, sent_at, created_at, body_html, body_text FROM crm_emails WHERE 1=1";
        $params = [];

        if ($folder === 'inbox') {
            $sql .= " AND direction='inbound'";
        } elseif ($folder === 'sent') {
            $sql .= " AND direction='outbound'";
        } elseif ($folder === 'starred') {
            $sql .= " AND is_starred=1";
        } elseif ($folder === 'unread') {
            $sql .= " AND is_read=0 AND direction='inbound'";
        } elseif ($folder !== 'all') {
            $sql .= " AND folder=?";
            $params[] = $folder;
        }

        if (!empty($search)) {
            $sql .= " AND (subject LIKE ? OR from_email LIKE ? OR from_name LIKE ? OR to_email LIKE ?)";
            $s = "%{$search}%";
            $params = array_merge($params, [$s, $s, $s, $s]);
        }

        $countSql = str_replace("SELECT id, contact_id, lead_id, direction, from_email, from_name, to_email, to_name, subject, is_read, is_starred, folder, message_id, sent_at, created_at, body_html, body_text", "SELECT COUNT(*)", $sql);
        $cstmt = $pdo->prepare($countSql);
        $cstmt->execute($params);
        $total = (int)$cstmt->fetchColumn();

        $sql .= " ORDER BY COALESCE(sent_at, created_at) DESC LIMIT {$limit} OFFSET {$offset}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return ['success' => true, 'emails' => $stmt->fetchAll(), 'total' => $total];
    }

    // ── threads (phase 2) ──
    if ($action === 'threads') {
        $limit = min((int)($p['limit'] ?? 80), 200);
        $emails = _msg_fetch_emails($pdo, $p, 400);

        $threads = [];
        foreach ($emails as $email) {
            $key = _msg_thread_key($email);
            $sentAt = (string)($email['sent_at'] ?? $email['created_at'] ?? '');
            $isInboundUnread = (($email['direction'] ?? '') === 'inbound') && (int)($email['is_read'] ?? 0) === 0;
            $subject = trim((string)($email['subject'] ?? ''));
            $subject = $subject !== '' ? $subject : '(sans objet)';
            $isOut = ($email['direction'] ?? '') === 'outbound';

            if (!isset($threads[$key])) {
                $threads[$key] = [
                    'thread_key' => $key,
                    'thread_hash' => sha1($key),
                    'subject' => $subject,
                    'snippet' => trim(strip_tags((string)($email['body_text'] ?? $email['body_html'] ?? ''))),
                    'last_at' => $sentAt,
                    'unread_count' => $isInboundUnread ? 1 : 0,
                    'message_count' => 1,
                    'is_starred' => (int)($email['is_starred'] ?? 0),
                    'contact_id' => (int)($email['contact_id'] ?? 0),
                    'lead_id' => (int)($email['lead_id'] ?? 0),
                    'counterpart_name' => $isOut ? ($email['to_name'] ?: $email['to_email']) : ($email['from_name'] ?: $email['from_email']),
                    'counterpart_email' => $isOut ? ($email['to_email'] ?? '') : ($email['from_email'] ?? ''),
                    'last_email_id' => (int)($email['id'] ?? 0),
                ];
                continue;
            }

            $threads[$key]['message_count']++;
            if ($isInboundUnread) {
                $threads[$key]['unread_count']++;
            }
            if ((int)($email['is_starred'] ?? 0) === 1) {
                $threads[$key]['is_starred'] = 1;
            }

            if ($sentAt > (string)$threads[$key]['last_at']) {
                $threads[$key]['subject'] = $subject;
                $threads[$key]['snippet'] = trim(strip_tags((string)($email['body_text'] ?? $email['body_html'] ?? '')));
                $threads[$key]['last_at'] = $sentAt;
                $threads[$key]['counterpart_name'] = $isOut ? ($email['to_name'] ?: $email['to_email']) : ($email['from_name'] ?: $email['from_email']);
                $threads[$key]['counterpart_email'] = $isOut ? ($email['to_email'] ?? '') : ($email['from_email'] ?? '');
                $threads[$key]['last_email_id'] = (int)($email['id'] ?? 0);
            }
        }

        $threads = array_values($threads);
        usort($threads, static fn ($a, $b) => strcmp((string)$b['last_at'], (string)$a['last_at']));
        if (count($threads) > $limit) {
            $threads = array_slice($threads, 0, $limit);
        }

        return ['success' => true, 'threads' => $threads, 'total' => count($threads)];
    }

    // ── thread detail (phase 2) ──
    if ($action === 'thread') {
        $threadKey = (string)($p['thread_key'] ?? '');
        if ($threadKey === '') {
            return ['success' => false, 'error' => 'thread_key requis'];
        }

        $emails = _msg_fetch_emails($pdo, $p, 500);
        $messages = _msg_find_emails_for_thread($emails, $threadKey);

        if (empty($messages)) {
            return ['success' => false, 'error' => 'Thread introuvable', '_http_code' => 404];
        }

        $latest = end($messages);
        reset($messages);

        return [
            'success' => true,
            'thread' => [
                'thread_key' => $threadKey,
                'subject' => $latest['subject'] ?: '(sans objet)',
                'contact_id' => (int)($latest['contact_id'] ?? 0),
                'lead_id' => (int)($latest['lead_id'] ?? 0),
                'message_count' => count($messages),
            ],
            'messages' => array_values($messages),
        ];
    }

    // ── get ──
    if ($action === 'get') {
        $id = (int)($p['id'] ?? 0);
        if ($id <= 0) return ['success' => false, 'error' => 'ID requis'];

        $stmt = $pdo->prepare("SELECT * FROM crm_emails WHERE id = ?");
        $stmt->execute([$id]);
        $email = $stmt->fetch();
        if (!$email) return ['success' => false, 'error' => 'Email introuvable', '_http_code' => 404];

        if (!$email['is_read']) {
            $pdo->prepare("UPDATE crm_emails SET is_read=1 WHERE id=?")->execute([$id]);
            $email['is_read'] = 1;
        }

        return ['success' => true, 'email' => $email];
    }

    // ── send ──
    if ($action === 'send' && $method === 'POST') {
        $to = $p['to_email'] ?? '';
        $subject = $p['subject'] ?? '';
        $body = $p['body_html'] ?? $p['body'] ?? '';

        if (empty($to) || empty($subject)) {
            return ['success' => false, 'error' => 'Destinataire et sujet requis'];
        }

        $options = [
            'to_name' => $p['to_name'] ?? '',
            'contact_id' => $p['contact_id'] ?? null,
            'lead_id' => $p['lead_id'] ?? null,
        ];

        if (!empty($p['from_email'])) $options['from_email'] = $p['from_email'];
        if (!empty($p['from_name'])) $options['from_name'] = $p['from_name'];
        if (!empty($p['reply_to'])) $options['reply_to'] = $p['reply_to'];
        if (!empty($p['cc'])) $options['cc'] = $p['cc'];

        return $emailService->sendEmail($to, $subject, $body, $options);
    }

    // ── reply ──
    if ($action === 'reply' && $method === 'POST') {
        $originalId = (int)($p['original_id'] ?? 0);
        $body = $p['body_html'] ?? $p['body'] ?? '';

        if ($originalId <= 0 || empty($body)) {
            return ['success' => false, 'error' => 'ID original et corps requis'];
        }

        $stmt = $pdo->prepare("SELECT * FROM crm_emails WHERE id = ?");
        $stmt->execute([$originalId]);
        $original = $stmt->fetch();

        if (!$original) return ['success' => false, 'error' => 'Email original introuvable'];

        $to = $original['direction'] === 'inbound' ? $original['from_email'] : $original['to_email'];
        $subject = 'Re: ' . preg_replace('/^Re:\s*/i', '', (string)$original['subject']);

        $options = [
            'in_reply_to' => $original['message_id'] ?? '',
            'contact_id' => $original['contact_id'],
            'lead_id' => $original['lead_id'],
        ];

        return $emailService->sendEmail($to, $subject, $body, $options);
    }

    // ── mark-read ──
    if ($action === 'mark-read' && $method === 'POST') {
        $id = (int)($p['id'] ?? 0);
        $pdo->prepare("UPDATE crm_emails SET is_read=1 WHERE id=?")->execute([$id]);
        return ['success' => true];
    }

    // ── mark-unread ──
    if ($action === 'mark-unread' && $method === 'POST') {
        $id = (int)($p['id'] ?? 0);
        $pdo->prepare("UPDATE crm_emails SET is_read=0 WHERE id=?")->execute([$id]);
        return ['success' => true];
    }

    // ── star ──
    if ($action === 'star' && $method === 'POST') {
        $id = (int)($p['id'] ?? 0);
        $star = (int)($p['starred'] ?? 1);
        $pdo->prepare("UPDATE crm_emails SET is_starred=? WHERE id=?")->execute([$star, $id]);
        return ['success' => true];
    }

    // ── delete ──
    if ($action === 'delete' && $method === 'POST') {
        $id = (int)($p['id'] ?? 0);
        $pdo->prepare("UPDATE crm_emails SET folder='trash' WHERE id=?")->execute([$id]);
        return ['success' => true];
    }

    // ── archive ──
    if ($action === 'archive' && $method === 'POST') {
        $id = (int)($p['id'] ?? 0);
        $pdo->prepare("UPDATE crm_emails SET folder='archive' WHERE id=?")->execute([$id]);
        return ['success' => true];
    }

    // ── sync ──
    if ($action === 'sync') {
        $folder = $p['folder'] ?? 'INBOX';
        $limit = min((int)($p['limit'] ?? 30), 100);
        return $emailService->syncToDatabase($folder, $limit);
    }

    // ── folders ──
    if ($action === 'folders') {
        return ['success' => true, 'folders' => $emailService->listFolders()];
    }

    // ── accounts ──
    if ($action === 'accounts') {
        $config = $emailService->getConfig();
        return [
            'success' => true,
            'accounts' => $config['email_accounts'] ?? [],
            'aliases' => $config['email_aliases'] ?? [],
            'roles' => $config['email_roles'] ?? [],
        ];
    }

    return [
        'success' => false,
        'error' => "Action '{$action}' non reconnue",
        '_http_code' => 404,
        'actions' => ['stats', 'list', 'threads', 'thread', 'get', 'send', 'reply', 'mark-read', 'mark-unread', 'star', 'delete', 'archive', 'sync', 'folders', 'accounts'],
    ];
}

// Execute
$_result = _messagerie_handler($ctx);

if ($_standalone) {
    if (isset($_result['_http_code'])) {
        http_response_code($_result['_http_code']);
        unset($_result['_http_code']);
    }
    echo json_encode($_result, JSON_UNESCAPED_UNICODE);
    exit;
}

return $_result;

<?php

declare(strict_types=1);

class TelegramBotService
{
    private const BOT_TOKEN = '';
    private const API_URL = 'https://api.telegram.org/bot';
    private const ADMIN_CHAT_IDS = []; // À configurer avec les IDs des admins approuvés

    public static function handleWebhook(array $update): void
    {
        try {
            if (isset($update['message'])) {
                self::handleMessage($update['message']);
            } elseif (isset($update['callback_query'])) {
                self::handleCallback($update['callback_query']);
            }
        } catch (Throwable $e) {
            error_log('Telegram webhook error: ' . $e->getMessage());
        }
    }

    private static function handleMessage(array $message): void
    {
        $chatId = (int) $message['chat']['id'];
        $text = trim($message['text'] ?? '');
        $telegramId = (int) $message['from']['id'];

        if (empty($text)) {
            return;
        }

        // Vérifier si l'utilisateur est enregistré et approuvé
        $user = self::getTelegramUser($telegramId);

        if (!$user || !$user['is_approved']) {
            if (str_starts_with($text, '/start')) {
                self::handleStart($chatId, $message['from'] ?? []);
            } else {
                self::sendMessage($chatId, "🔒 Vous devez d'abord être approuvé par un administrateur. Utilisez /start pour vous enregistrer.");
            }
            return;
        }

        // Router les commandes
        if ($text === '/start' || $text === '/menu') {
            self::sendMainMenu($chatId, $user);
        } elseif ($text === '/sequences') {
            self::sendSequencesList($chatId, $user);
        } elseif ($text === '/prospects') {
            self::sendProspectsList($chatId, $user);
        } elseif ($text === '/stats') {
            self::sendStats($chatId, $user);
        } elseif ($text === '/aide') {
            self::sendHelp($chatId);
        } else {
            self::sendMessage($chatId, "❓ Commande non reconnue. Utilisez /menu pour voir les options disponibles.");
        }
    }

    private static function handleCallback(array $query): void
    {
        $chatId = (int) $query['message']['chat']['id'];
        $callbackId = $query['id'];
        $data = $query['data'] ?? '';
        $telegramId = (int) $query['from']['id'];

        $user = self::getTelegramUser($telegramId);
        if (!$user || !$user['is_approved']) {
            return;
        }

        // Parser la commande callback (format: "action:params")
        [$action, $params] = array_pad(explode(':', $data, 2), 2, '');

        try {
            match ($action) {
                'seq_list' => self::sendSequencesList($chatId, $user),
                'seq_view' => self::sendSequenceDetail($chatId, (int) $params, $user),
                'seq_activate' => self::activateSequence($chatId, (int) $params, $user),
                'seq_deactivate' => self::deactivateSequence($chatId, (int) $params, $user),
                'email_edit' => self::sendEmailEditForm($chatId, (int) $params, $user),
                'prospect_list' => self::sendProspectsList($chatId, $user),
                'api_keys' => self::sendAPIKeysList($chatId, $user),
                'back' => self::sendMainMenu($chatId, $user),
                default => null
            };

            self::answerCallback($callbackId);
        } catch (Throwable $e) {
            error_log('Callback error: ' . $e->getMessage());
            self::answerCallback($callbackId, 'Erreur: ' . $e->getMessage());
        }
    }

    private static function handleStart(int $chatId, array $from): void
    {
        $telegramId = (int) ($from['id'] ?? 0);
        $firstName = $from['first_name'] ?? 'Utilisateur';
        $username = $from['username'] ?? null;

        if (!$telegramId) {
            self::sendMessage($chatId, "❌ Erreur: impossible de récupérer votre ID Telegram.");
            return;
        }

        // Vérifier si l'utilisateur existe déjà
        $existing = self::getTelegramUser($telegramId);
        if ($existing) {
            if ($existing['is_approved']) {
                self::sendMainMenu($chatId, $existing);
            } else {
                self::sendMessage($chatId, "⏳ Votre demande d'accès est en attente d'approbation par un administrateur.");
            }
            return;
        }

        // Créer un nouvel utilisateur Telegram
        try {
            $db = db();
            $stmt = $db->prepare('
                INSERT INTO telegram_users (telegram_id, chat_id, first_name, username, is_approved)
                VALUES (?, ?, ?, ?, ?)
            ');
            $stmt->execute([$telegramId, $chatId, $firstName, $username, false]);

            $message = "👋 Bienvenue $firstName!\n\n";
            $message .= "✅ Votre demande d'accès a été enregistrée.\n";
            $message .= "⏳ Un administrateur doit approuver votre accès.\n\n";
            $message .= "👨‍💼 ID Telegram: `$telegramId`\n";
            $message .= "📱 Username: @" . ($username ?? 'non défini') . "\n\n";
            $message .= "Vous recevrez une notification une fois approuvé.";

            self::sendMessage($chatId, $message);

            // Notifier les admins
            self::notifyAdmins("🔔 Nouvelle demande d'accès Telegram:\nNom: $firstName\nUsername: @" . ($username ?? 'non défini') . "\nID: $telegramId");
        } catch (Throwable $e) {
            error_log('Error creating Telegram user: ' . $e->getMessage());
            self::sendMessage($chatId, "❌ Erreur lors de l'enregistrement.");
        }
    }

    private static function sendMainMenu(int $chatId, array $user): void
    {
        $message = "📱 *Menu Principal*\n\n";
        $message .= "Bienvenue " . htmlspecialchars($user['first_name']) . "!\n\n";
        $message .= "Que souhaitez-vous faire?";

        $keyboard = [
            [
                ['text' => '📧 Séquences Email', 'callback_data' => 'seq_list'],
                ['text' => '👥 Prospects', 'callback_data' => 'prospect_list'],
            ],
            [
                ['text' => '📊 Statistiques', 'callback_data' => 'stats'],
                ['text' => '🔐 Clés API', 'callback_data' => 'api_keys'],
            ],
            [
                ['text' => '❓ Aide', 'callback_data' => 'help'],
            ],
        ];

        self::sendMessage($chatId, $message, $keyboard);
    }

    private static function sendSequencesList(int $chatId, array $user): void
    {
        try {
            $stmt = db()->prepare('SELECT id, name, status FROM email_sequences ORDER BY created_at DESC LIMIT 10');
            $stmt->execute();
            $sequences = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($sequences)) {
                self::sendMessage($chatId, "📧 Aucune séquence trouvée.");
                return;
            }

            $message = "📧 *Séquences Email* (10 dernières)\n\n";
            $keyboard = [];

            foreach ($sequences as $seq) {
                $status = $seq['status'] === 'active' ? '🟢' : '🔴';
                $message .= "$status " . htmlspecialchars($seq['name']) . "\n";
                $keyboard[] = [['text' => $seq['name'], 'callback_data' => 'seq_view:' . $seq['id']]];
            }

            $keyboard[] = [['text' => '← Retour', 'callback_data' => 'back']];

            self::sendMessage($chatId, $message, $keyboard);
        } catch (Throwable $e) {
            self::sendMessage($chatId, "❌ Erreur: " . $e->getMessage());
        }
    }

    private static function sendSequenceDetail(int $chatId, int $sequenceId, array $user): void
    {
        try {
            $sequence = EmailSequenceService::getSequence($sequenceId);
            if (!$sequence) {
                self::sendMessage($chatId, "❌ Séquence non trouvée.");
                return;
            }

            $stats = EmailSequenceService::getSequenceStats($sequenceId);

            $message = "*" . htmlspecialchars($sequence['name']) . "*\n\n";
            $message .= "📍 *Infos*\n";
            $message .= "Objectif: " . htmlspecialchars($sequence['objective']) . "\n";
            $message .= "Persona: " . htmlspecialchars($sequence['persona']) . "\n";
            $message .= "Ville: " . htmlspecialchars($sequence['city']) . "\n";
            $message .= "Statut: " . ($sequence['status'] === 'active' ? '🟢 Actif' : '🔴 Inactif') . "\n\n";

            $message .= "📊 *Stats*\n";
            $message .= "Abonnés: " . $stats['total_subscribers'] . "\n";
            $message .= "Actifs: " . $stats['active_subscribers'] . "\n";
            $message .= "Ouverts: " . $stats['total_opened'] . "\n";
            $message .= "Cliqués: " . $stats['total_clicked'] . "\n";

            $keyboard = [];
            if ($sequence['status'] === 'active') {
                $keyboard[] = [['text' => '⛔ Désactiver', 'callback_data' => 'seq_deactivate:' . $sequenceId]];
            } else {
                $keyboard[] = [['text' => '✅ Activer', 'callback_data' => 'seq_activate:' . $sequenceId]];
            }
            $keyboard[] = [['text' => '← Retour', 'callback_data' => 'seq_list']];

            self::sendMessage($chatId, $message, $keyboard);
        } catch (Throwable $e) {
            self::sendMessage($chatId, "❌ Erreur: " . $e->getMessage());
        }
    }

    private static function activateSequence(int $chatId, int $sequenceId, array $user): void
    {
        try {
            $success = EmailSequenceService::activateSequence($sequenceId);
            if ($success) {
                self::logCommand($user['id'], 'sequence_activate', (string) $sequenceId, 'Séquence activée', 'success');
                self::sendMessage($chatId, "✅ Séquence activée avec succès!");
            } else {
                self::sendMessage($chatId, "❌ Erreur lors de l'activation.");
            }
            self::sendSequenceDetail($chatId, $sequenceId, $user);
        } catch (Throwable $e) {
            self::logCommand($user['id'], 'sequence_activate', (string) $sequenceId, $e->getMessage(), 'error');
            self::sendMessage($chatId, "❌ Erreur: " . $e->getMessage());
        }
    }

    private static function deactivateSequence(int $chatId, int $sequenceId, array $user): void
    {
        try {
            $success = EmailSequenceService::deactivateSequence($sequenceId);
            if ($success) {
                self::logCommand($user['id'], 'sequence_deactivate', (string) $sequenceId, 'Séquence désactivée', 'success');
                self::sendMessage($chatId, "✅ Séquence désactivée avec succès!");
            } else {
                self::sendMessage($chatId, "❌ Erreur lors de la désactivation.");
            }
            self::sendSequenceDetail($chatId, $sequenceId, $user);
        } catch (Throwable $e) {
            self::logCommand($user['id'], 'sequence_deactivate', (string) $sequenceId, $e->getMessage(), 'error');
            self::sendMessage($chatId, "❌ Erreur: " . $e->getMessage());
        }
    }

    private static function sendProspectsList(int $chatId, array $user): void
    {
        try {
            $stmt = db()->prepare('
                SELECT id, email, first_name, created_at
                FROM contacts
                ORDER BY created_at DESC
                LIMIT 10
            ');
            $stmt->execute();
            $prospects = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($prospects)) {
                self::sendMessage($chatId, "👥 Aucun prospect trouvé.");
                return;
            }

            $message = "👥 *Prospects Récents* (10 derniers)\n\n";

            foreach ($prospects as $prospect) {
                $name = htmlspecialchars($prospect['first_name'] ?? 'N/A');
                $email = htmlspecialchars($prospect['email']);
                $date = date('d/m/Y', strtotime($prospect['created_at']));
                $message .= "📧 $name ($email)\n";
                $message .= "   📅 $date\n\n";
            }

            $keyboard = [
                [['text' => '← Retour', 'callback_data' => 'back']],
            ];

            self::sendMessage($chatId, $message, $keyboard);
        } catch (Throwable $e) {
            self::sendMessage($chatId, "❌ Erreur: " . $e->getMessage());
        }
    }

    private static function sendStats(int $chatId, array $user): void
    {
        try {
            $db = db();

            // Stats séquences
            $stmt = $db->prepare('SELECT COUNT(*) as total FROM email_sequences WHERE status = ?');
            $stmt->execute(['active']);
            $activeSeqs = $stmt->fetch()['total'] ?? 0;

            $stmt = $db->prepare('SELECT COUNT(*) as total FROM email_sequences');
            $stmt->execute();
            $totalSeqs = $stmt->fetch()['total'] ?? 0;

            // Stats prospects
            $stmt = $db->prepare('SELECT COUNT(*) as total FROM contacts');
            $stmt->execute();
            $totalProspects = $stmt->fetch()['total'] ?? 0;

            // Stats emails
            $stmt = $db->prepare('SELECT COUNT(*) as total FROM email_sequence_subscriptions WHERE status = ?');
            $stmt->execute(['completed']);
            $completedEmails = $stmt->fetch()['total'] ?? 0;

            $message = "📊 *Statistiques Globales*\n\n";
            $message .= "*Séquences*\n";
            $message .= "✅ Actives: $activeSeqs\n";
            $message .= "📈 Total: $totalSeqs\n\n";

            $message .= "*Prospects*\n";
            $message .= "👥 Total: $totalProspects\n\n";

            $message .= "*Emails*\n";
            $message .= "✔️ Complétés: $completedEmails\n";

            $keyboard = [
                [['text' => '← Retour', 'callback_data' => 'back']],
            ];

            self::sendMessage($chatId, $message, $keyboard);
        } catch (Throwable $e) {
            self::sendMessage($chatId, "❌ Erreur: " . $e->getMessage());
        }
    }

    private static function sendAPIKeysList(int $chatId, array $user): void
    {
        $message = "🔐 *Gestion des Clés API*\n\n";
        $message .= "Clés configurées:\n";
        $message .= "🗺️ Google Maps\n";
        $message .= "🤖 OpenAI\n";
        $message .= "📍 Google My Business\n";
        $message .= "👥 Facebook\n\n";
        $message .= "Pour modifier les clés, utilisez le site web.";

        $keyboard = [
            [['text' => '← Retour', 'callback_data' => 'back']],
        ];

        self::sendMessage($chatId, $message, $keyboard);
    }

    private static function sendHelp(int $chatId): void
    {
        $message = "❓ *Aide - Commandes Disponibles*\n\n";
        $message .= "/menu - Afficher le menu principal\n";
        $message .= "/sequences - Lister les séquences\n";
        $message .= "/prospects - Lister les prospects\n";
        $message .= "/stats - Voir les statistiques\n";
        $message .= "/aide - Afficher cette aide\n";

        self::sendMessage($chatId, $message);
    }

    // Méthodes utilitaires
    private static function getTelegramUser(int $telegramId): ?array
    {
        try {
            $stmt = db()->prepare('SELECT * FROM telegram_users WHERE telegram_id = ?');
            $stmt->execute([$telegramId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) {
            error_log('Error getting Telegram user: ' . $e->getMessage());
            return null;
        }
    }

    private static function sendMessage(int $chatId, string $text, array $keyboard = []): void
    {
        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ];

        if (!empty($keyboard)) {
            $payload['reply_markup'] = json_encode(['inline_keyboard' => $keyboard]);
        }

        self::apiCall('sendMessage', $payload);
    }

    private static function answerCallback(string $callbackId, string $text = ''): void
    {
        self::apiCall('answerCallbackQuery', [
            'callback_query_id' => $callbackId,
            'text' => $text,
        ]);
    }

    private static function apiCall(string $method, array $params): void
    {
        if (empty(self::BOT_TOKEN)) {
            error_log('Telegram bot token not configured');
            return;
        }

        $url = self::API_URL . self::BOT_TOKEN . '/' . $method;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

    private static function notifyAdmins(string $message): void
    {
        foreach (self::ADMIN_CHAT_IDS as $chatId) {
            self::sendMessage((int) $chatId, $message);
        }
    }

    private static function logCommand(int $telegramUserId, string $command, string $params, string $response, string $status): void
    {
        try {
            $stmt = db()->prepare('
                INSERT INTO telegram_commands_log (telegram_user_id, command, parameters, response, status)
                VALUES (?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $telegramUserId,
                $command,
                json_encode(['params' => $params]),
                $response,
                $status,
            ]);
        } catch (Throwable $e) {
            error_log('Error logging command: ' . $e->getMessage());
        }
    }
}

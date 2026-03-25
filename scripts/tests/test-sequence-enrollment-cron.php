<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/classes/EmailService.php';
require_once dirname(__DIR__, 2) . '/includes/classes/SequenceEngineService.php';

final class FakeEmailService extends EmailService
{
    public array $sent = [];

    public function __construct()
    {
        parent::__construct(null);
    }

    public function sendEmail(string $to, string $subject, string $htmlBody, array $options = []): array
    {
        $this->sent[] = [
            'to' => $to,
            'subject' => $subject,
            'body' => $htmlBody,
            'options' => $options,
        ];

        return ['success' => true, 'message_id' => 'fake-message-id'];
    }
}

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "❌ {$message}\n");
        exit(1);
    }
}

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("CREATE TABLE leads (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT,
    last_name TEXT,
    email TEXT,
    phone TEXT,
    source TEXT
)");
$pdo->exec("CREATE TABLE crm_sequences (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    is_active INTEGER DEFAULT 1,
    from_name TEXT,
    from_email TEXT,
    reply_to TEXT
)");
$pdo->exec("CREATE TABLE crm_sequence_steps (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sequence_id INTEGER NOT NULL,
    step_order INTEGER NOT NULL,
    step_type TEXT DEFAULT 'email',
    delay_days INTEGER DEFAULT 0,
    delay_hours INTEGER DEFAULT 0,
    subject TEXT,
    body_html TEXT,
    is_active INTEGER DEFAULT 1
)");
$pdo->exec("CREATE TABLE crm_sequence_enrollments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sequence_id INTEGER NOT NULL,
    lead_id INTEGER NOT NULL,
    current_step INTEGER DEFAULT 1,
    status TEXT DEFAULT 'active',
    enrolled_at TEXT,
    next_action_at TEXT,
    completed_at TEXT,
    metadata TEXT
)");
$pdo->exec("CREATE TABLE crm_sequence_sends (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    enrollment_id INTEGER NOT NULL,
    step_id INTEGER NOT NULL,
    lead_id INTEGER NOT NULL,
    sequence_id INTEGER NOT NULL,
    subject TEXT,
    status TEXT,
    tracking_id TEXT,
    scheduled_at TEXT,
    sent_at TEXT,
    error_message TEXT
)");

$pdo->prepare("INSERT INTO leads (first_name, last_name, email, phone, source) VALUES (?, ?, ?, ?, ?)")
    ->execute(['Camille', 'Martin', 'camille@example.test', '0600000000', 'landing-page']);
$leadId = (int)$pdo->lastInsertId();

$pdo->prepare("INSERT INTO crm_sequences (name, is_active, from_name, from_email, reply_to) VALUES (?, 1, ?, ?, ?)")
    ->execute(['Séquence Bienvenue', 'Eduardo', 'noreply@example.test', 'contact@example.test']);
$sequenceId = (int)$pdo->lastInsertId();

$pdo->prepare("INSERT INTO crm_sequence_steps (sequence_id, step_order, step_type, delay_days, delay_hours, subject, body_html, is_active)
               VALUES (?, 1, 'email', 0, 0, ?, ?, 1)")
    ->execute([$sequenceId, 'Bonjour {{prenom}}', '<p>Bienvenue {{prenom}} {{nom}}</p>']);
$stepId = (int)$pdo->lastInsertId();

// Simule l'enrôlement manuel d'un lead (équivalent fonctionnel à INSERT IGNORE)
$insertEnrollment = $pdo->prepare("INSERT OR IGNORE INTO crm_sequence_enrollments (sequence_id, lead_id, current_step, status, enrolled_at, next_action_at)
                                   VALUES (?, ?, 1, 'active', ?, ?)");
$now = date('Y-m-d H:i:s', strtotime('-1 minute'));
$insertEnrollment->execute([$sequenceId, $leadId, $now, $now]);

$emailService = new FakeEmailService();
$engine = new SequenceEngineService($pdo, $emailService);
$result = $engine->run(10, false);

assertTrue($result['processed'] === 1, 'Le cron doit traiter exactement 1 enrôlement.');
assertTrue($result['sent'] === 1, 'Le cron doit envoyer 1 email pour l\'étape email.');
assertTrue($result['failed'] === 0, 'Le cron ne doit pas échouer.');

$send = $pdo->query("SELECT enrollment_id, step_id, status, subject FROM crm_sequence_sends LIMIT 1")
    ->fetch(PDO::FETCH_ASSOC);
assertTrue((int)$send['step_id'] === $stepId, 'Le log d\'envoi doit pointer sur l\'étape email créée.');
assertTrue($send['status'] === 'sent', 'Le statut d\'envoi doit être sent.');
assertTrue($send['subject'] === 'Bonjour Camille', 'Les variables du sujet doivent être remplacées.');

$enrollment = $pdo->query("SELECT status, completed_at, next_action_at FROM crm_sequence_enrollments LIMIT 1")
    ->fetch(PDO::FETCH_ASSOC);
assertTrue($enrollment['status'] === 'completed', 'L\'enrôlement doit être complété après la dernière étape.');
assertTrue($enrollment['completed_at'] !== null, 'La date de complétion doit être renseignée.');
assertTrue($enrollment['next_action_at'] === null, 'Aucune prochaine action ne doit rester après complétion.');

assertTrue(count($emailService->sent) === 1, 'Le service email doit être appelé une seule fois.');

fwrite(STDOUT, "✅ Test E2E enrôlement + cron séquence email: OK\n");

<?php
/**
 * Diagnostic SMTP + délivrabilité (SPF/DMARC)
 * Usage: /diagnostic-smtp.php
 *
 * ⚠️ Fichier de diagnostic temporaire.
 * À supprimer une fois les tests terminés.
 */

header('Content-Type: text/plain; charset=utf-8');

define('ROOT_PATH', __DIR__);

if (is_file(ROOT_PATH . '/config/config.php')) {
    require_once ROOT_PATH . '/config/config.php';
}

function mask(?string $value, int $visible = 2): string
{
    if ($value === null || $value === '') {
        return '(vide)';
    }
    $len = strlen($value);
    if ($len <= $visible * 2) {
        return str_repeat('*', $len);
    }
    return substr($value, 0, $visible) . str_repeat('*', $len - ($visible * 2)) . substr($value, -$visible);
}

function readSmtpConfig(): array
{
    $file = ROOT_PATH . '/config/smtp.php';
    if (is_file($file)) {
        $cfg = include $file;
        return is_array($cfg) ? $cfg : [];
    }
    return [];
}

function domainFromEmail(string $email): string
{
    $parts = explode('@', $email);
    return isset($parts[1]) ? strtolower(trim($parts[1])) : '';
}

function readMultilineResponse($socket): string
{
    $response = '';
    while (!feof($socket)) {
        $line = fgets($socket, 1024);
        if ($line === false) {
            break;
        }
        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }
    return trim($response);
}

function smtpCommand($socket, string $command): string
{
    fwrite($socket, $command . "\r\n");
    return readMultilineResponse($socket);
}

function firstCode(string $response): string
{
    if (preg_match('/^(\d{3})/m', $response, $m)) {
        return $m[1];
    }
    return '---';
}

function checkSpf(string $domain): array
{
    if ($domain === '') {
        return ['ok' => false, 'message' => 'domaine vide'];
    }
    $records = dns_get_record($domain, DNS_TXT);
    if ($records === false) {
        return ['ok' => false, 'message' => 'dns_get_record indisponible/erreur'];
    }

    foreach ($records as $r) {
        $txt = $r['txt'] ?? '';
        if (stripos($txt, 'v=spf1') !== false) {
            return ['ok' => true, 'message' => $txt];
        }
    }
    return ['ok' => false, 'message' => 'Aucun enregistrement SPF (v=spf1) trouvé'];
}

function checkDmarc(string $domain): array
{
    if ($domain === '') {
        return ['ok' => false, 'message' => 'domaine vide'];
    }
    $records = dns_get_record('_dmarc.' . $domain, DNS_TXT);
    if ($records === false) {
        return ['ok' => false, 'message' => 'dns_get_record indisponible/erreur'];
    }
    foreach ($records as $r) {
        $txt = $r['txt'] ?? '';
        if (stripos($txt, 'v=DMARC1') !== false) {
            return ['ok' => true, 'message' => $txt];
        }
    }
    return ['ok' => false, 'message' => 'Aucun enregistrement DMARC (v=DMARC1) trouvé'];
}

$smtp = readSmtpConfig();

$smtpHost = (string)($smtp['smtp_host'] ?? '');
$smtpPort = (int)($smtp['smtp_port'] ?? 0);
$smtpUser = (string)($smtp['smtp_user'] ?? '');
$smtpPass = (string)($smtp['smtp_pass'] ?? '');
$smtpFrom = (string)($smtp['smtp_from'] ?? $smtpUser);
$siteDomain = defined('SITE_DOMAIN') ? (string)SITE_DOMAIN : '';
$domain = domainFromEmail($smtpFrom) ?: $siteDomain;

echo "=== Diagnostic SMTP + Délivrabilité ===\n";
echo 'Date UTC: ' . gmdate('Y-m-d H:i:s') . "\n\n";

echo "[1] Fichier config SMTP\n";
$smtpFile = ROOT_PATH . '/config/smtp.php';
echo '- Path: ' . $smtpFile . "\n";
echo '- Existe: ' . (is_file($smtpFile) ? 'oui' : 'non') . "\n";
echo '- Lisible: ' . (is_readable($smtpFile) ? 'oui' : 'non') . "\n\n";

echo "[2] Valeurs SMTP\n";
echo '- smtp_host: ' . ($smtpHost !== '' ? $smtpHost : '(vide)') . "\n";
echo '- smtp_port: ' . ($smtpPort > 0 ? (string)$smtpPort : '(vide)') . "\n";
echo '- smtp_user: ' . ($smtpUser !== '' ? $smtpUser : '(vide)') . "\n";
echo '- smtp_pass: ' . mask($smtpPass) . "\n";
echo '- smtp_from: ' . ($smtpFrom !== '' ? $smtpFrom : '(vide)') . "\n";
echo '- domaine testé SPF/DMARC: ' . ($domain !== '' ? $domain : '(vide)') . "\n\n";

if ($smtpHost === '' || $smtpPort <= 0 || $smtpUser === '' || $smtpPass === '') {
    echo "[3] Test SMTP\n";
    echo "- ECHEC: configuration SMTP incomplète (smtp_host/smtp_port/smtp_user/smtp_pass)\n\n";
} else {
    echo "[3] Test SMTP (connexion + auth)\n";
    $resolvedIp = gethostbyname($smtpHost);
    echo '- DNS smtp_host: ' . ($resolvedIp !== $smtpHost ? $resolvedIp : 'non résolu') . "\n";

    $prefix = ($smtpPort === 465) ? 'ssl://' : '';
    $socket = @fsockopen($prefix . $smtpHost, $smtpPort, $errno, $errstr, 15);

    if (!$socket) {
        echo "- ECHEC connexion socket: {$errstr} ({$errno})\n\n";
    } else {
        stream_set_timeout($socket, 15);

        $banner = readMultilineResponse($socket);
        echo '- Banner: ' . $banner . "\n";

        $ehlo = smtpCommand($socket, 'EHLO diagnostic.local');
        echo '- EHLO [' . firstCode($ehlo) . "]: " . str_replace("\n", ' | ', $ehlo) . "\n";

        if ($smtpPort === 587) {
            $tls = smtpCommand($socket, 'STARTTLS');
            echo '- STARTTLS [' . firstCode($tls) . "]: " . $tls . "\n";

            if (firstCode($tls) === '220') {
                $tlsOk = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                echo '- TLS activé: ' . ($tlsOk ? 'oui' : 'non') . "\n";
                $ehlo2 = smtpCommand($socket, 'EHLO diagnostic.local');
                echo '- EHLO TLS [' . firstCode($ehlo2) . "]: " . str_replace("\n", ' | ', $ehlo2) . "\n";
            }
        }

        $auth = smtpCommand($socket, 'AUTH LOGIN');
        echo '- AUTH LOGIN [' . firstCode($auth) . "]: " . $auth . "\n";

        $userResp = smtpCommand($socket, base64_encode($smtpUser));
        echo '- USER [' . firstCode($userResp) . "]: " . $userResp . "\n";

        $passResp = smtpCommand($socket, base64_encode($smtpPass));
        echo '- PASS [' . firstCode($passResp) . "]: " . $passResp . "\n";

        if (firstCode($passResp) === '235') {
            echo "- OK: authentification SMTP réussie\n";
        } else {
            echo "- ECHEC: authentification SMTP refusée\n";
        }

        $quit = smtpCommand($socket, 'QUIT');
        echo '- QUIT [' . firstCode($quit) . "]: " . $quit . "\n\n";
        fclose($socket);
    }
}

echo "[4] Délivrabilité DNS (SPF / DMARC)\n";
$spf = checkSpf($domain);
echo '- SPF: ' . ($spf['ok'] ? 'OK' : 'ECHEC') . ' | ' . $spf['message'] . "\n";

$dmarc = checkDmarc($domain);
echo '- DMARC: ' . ($dmarc['ok'] ? 'OK' : 'ECHEC') . ' | ' . $dmarc['message'] . "\n\n";

echo "Conseils:\n";
echo "1) Si AUTH échoue: vérifier smtp_user/smtp_pass/port/chiffrement sur votre hébergeur.\n";
echo "2) Si SPF/DMARC échouent: corriger les enregistrements DNS du domaine.\n";
echo "3) Après correction DNS, attendre la propagation (souvent 5 min à 24h).\n";

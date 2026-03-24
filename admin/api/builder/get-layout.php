<?php
/**
 * /admin/api/builder/get-layout.php
 * Retourne les headers/footers disponibles + leur HTML
 * pour injection dans le preview de l'éditeur Builder Pro
 */

define('ADMIN_ROUTER', true);

$_initPath = dirname(__DIR__, 2) . '/includes/init.php';
if (!file_exists($_initPath)) $_initPath = $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/init.php';
if (!file_exists($_initPath)) $_initPath = '/home/mahe6420/public_html/admin/includes/init.php';
require_once $_initPath;

header('Content-Type: application/json; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$_isAuth = !empty($_SESSION['admin_logged_in'])
        || !empty($_SESSION['user_id'])
        || !empty($_SESSION['admin_id'])
        || !empty($_SESSION['logged_in'])
        || !empty($_SESSION['is_admin']);

if (!$_isAuth) {
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

$action = $_GET['action'] ?? 'list';

try {

    // ── LIST : retourne tous les headers + footers disponibles ────────────────
    if ($action === 'list') {

        $headers = [];
        $footers = [];

        try {
            $rs = $pdo->query("SELECT id, name, status, custom_css FROM headers ORDER BY status DESC, name ASC");
            $headers = $rs ? $rs->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            // table headers absente
        }

        try {
            $rs = $pdo->query("SELECT id, name, status, custom_css FROM footers ORDER BY status DESC, name ASC");
            $footers = $rs ? $rs->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            // table footers absente
        }

        // Identifier l'actif par défaut (status = 'active' ou premier publié)
        $defaultHeader = 0;
        $defaultFooter = 0;

        foreach ($headers as $h) {
            if ($h['status'] === 'active') { $defaultHeader = (int)$h['id']; break; }
        }
        if (!$defaultHeader && !empty($headers)) {
            foreach ($headers as $h) {
                if ($h['status'] === 'published') { $defaultHeader = (int)$h['id']; break; }
            }
        }
        if (!$defaultHeader && !empty($headers)) $defaultHeader = (int)$headers[0]['id'];

        foreach ($footers as $f) {
            if ($f['status'] === 'active') { $defaultFooter = (int)$f['id']; break; }
        }
        if (!$defaultFooter && !empty($footers)) {
            foreach ($footers as $f) {
                if ($f['status'] === 'published') { $defaultFooter = (int)$f['id']; break; }
            }
        }
        if (!$defaultFooter && !empty($footers)) $defaultFooter = (int)$footers[0]['id'];

        echo json_encode([
            'success'        => true,
            'headers'        => $headers,
            'footers'        => $footers,
            'defaultHeader'  => $defaultHeader,
            'defaultFooter'  => $defaultFooter,
        ]);
        exit;
    }

    // ── GET : retourne le HTML complet d'un header ou footer ─────────────────
    if ($action === 'get') {

        $type = $_GET['type'] ?? ''; // 'header' | 'footer'
        $id   = (int)($_GET['id'] ?? 0);

        if (!in_array($type, ['header', 'footer']) || $id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Paramètres invalides']);
            exit;
        }

        $table = $type === 'header' ? 'headers' : 'footers';

        $stmt = $pdo->prepare("SELECT id, name, custom_html, custom_css, custom_js, status FROM `{$table}` WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            echo json_encode(['success' => false, 'error' => ucfirst($type).' #'.$id.' introuvable']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'id'      => (int)$row['id'],
            'name'    => $row['name'],
            'html'    => $row['custom_html'] ?? '',
            'css'     => $row['custom_css']  ?? '',
            'js'      => $row['custom_js']   ?? '',
            'status'  => $row['status'],
            'type'    => $type,
        ]);
        exit;
    }

    // ── TOGGLE STATUS : active/désactive un header ou footer ─────────────────
    if ($action === 'toggle' && $_SERVER['REQUEST_METHOD'] === 'POST') {

        $body = json_decode(file_get_contents('php://input'), true);
        $type = $body['type'] ?? '';
        $id   = (int)($body['id'] ?? 0);

        if (!in_array($type, ['header', 'footer']) || $id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Paramètres invalides']);
            exit;
        }

        $table = $type === 'header' ? 'headers' : 'footers';

        // Lire le statut actuel
        $stmt = $pdo->prepare("SELECT status FROM `{$table}` WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $current = $stmt->fetchColumn();

        if ($current === false) {
            echo json_encode(['success' => false, 'error' => 'Introuvable']);
            exit;
        }

        // Basculer entre 'active' et 'inactive' (ou 'draft')
        // Si on active → désactiver tous les autres d'abord (un seul actif à la fois)
        $newStatus = ($current === 'active') ? 'inactive' : 'active';

        if ($newStatus === 'active') {
            // Désactiver les autres
            $pdo->prepare("UPDATE `{$table}` SET status = 'inactive' WHERE id != ?")->execute([$id]);
        }

        $pdo->prepare("UPDATE `{$table}` SET status = ? WHERE id = ?")->execute([$newStatus, $id]);

        echo json_encode([
            'success'   => true,
            'id'        => $id,
            'type'      => $type,
            'newStatus' => $newStatus,
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Action inconnue: '.$action]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
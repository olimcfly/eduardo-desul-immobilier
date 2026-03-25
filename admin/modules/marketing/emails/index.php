<?php
/**
 * Emails automatiques — Étape 1
 * Implémentation actuelle: CRUD Séquences + UI
 */

if (!defined('ADMIN_ROUTER')) {
    die("Accès direct interdit.");
}
?>

<div class="page-hd anim" style="margin-bottom:20px;display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;">
    <div>
        <h1><i class="fas fa-envelope-open-text" style="color:#0891b2;margin-right:8px"></i>Emails automatiques</h1>
        <div class="page-hd-sub">Étapes 1 & 2 activées : CRUD séquences + éditeur email + assistant IA</div>
    </div>
    <a href="?page=sequences" class="btn btn-p btn-sm">
        <i class="fas fa-external-link-alt"></i> Ouvrir le module Séquences plein écran
    </a>
</div>

<div class="card anim d1" style="padding:14px 18px;margin-bottom:18px;border-left:4px solid #0891b2;">
    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
        <span class="badge" style="background:#dbeafe;color:#1e40af;padding:4px 10px;border-radius:999px;font-weight:700;">STEP 1</span>
        <strong>Statut roadmap</strong>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:10px;margin-top:10px;">
        <div style="padding:10px 12px;border:1px solid var(--border);border-radius:10px;background:var(--surface-2);">
            <div style="font-weight:700;color:#065f46"><i class="fas fa-check-circle"></i> Étape 1 — CRUD Séquences + UI</div>
            <div style="font-size:.85rem;color:var(--text-3);">Disponible maintenant ci-dessous.</div>
        </div>
        <div style="padding:10px 12px;border:1px solid var(--border);border-radius:10px;background:var(--surface-2);opacity:.75;">
            <div style="font-weight:700;color:#065f46"><i class="fas fa-check-circle"></i> Étape 2 — Éditeur Email + IA</div>
            <div style="font-size:.85rem;color:var(--text-3);">Disponible dans la modale d’ajout/modif d’étape email.</div>
        </div>
        <div style="padding:10px 12px;border:1px solid var(--border);border-radius:10px;background:var(--surface-2);opacity:.75;">
            <div style="font-weight:700;color:#92400e"><i class="fas fa-clock"></i> Étape 3 — Moteur d'envoi / cron</div>
            <div style="font-size:.85rem;color:var(--text-3);">Prévu après validation étape 2.</div>
        </div>
    </div>
</div>

<?php
// Réutilisation du module existant pour éviter la duplication et rester modulaire.
// Cela permet d'exposer immédiatement le CRUD Séquences demandé dans la page Emails automatiques.
require __DIR__ . '/../sequences/index.php';

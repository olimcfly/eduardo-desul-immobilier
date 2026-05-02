<?php

declare(strict_types=1);

$pageTitle = 'Newsletter';
$pageDescription = 'Gestion de votre infolettre et abonnés';

function renderContent(): void {
    ?>
    <style>
        .hub-hero { background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%); border-radius: 16px; padding: 24px 20px; color: #fff; margin-bottom: 24px; }
        .hub-hero h1 { margin: 0 0 8px; font-size: 28px; font-weight: 700; }
        .hub-hero p { margin: 0; color: rgba(255,255,255,.78); font-size: 15px; }
        .hub-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-top: 16px; }
        .hub-stat { background: rgba(255,255,255,.1); padding: 12px; border-radius: 8px; text-align: center; }
        .hub-stat-value { font-size: 24px; font-weight: 700; margin-bottom: 4px; }
        .hub-stat-label { font-size: 12px; opacity: .85; }
        .tabs { display: flex; gap: 0; border-bottom: 1px solid #e5e7eb; margin-bottom: 24px; }
        .tab { padding: 12px 20px; border: none; background: none; cursor: pointer; font-weight: 600; color: #64748b; border-bottom: 2px solid transparent; }
        .tab.active { color: #0f172a; border-bottom-color: #c9a84c; }
        .newsletter-table { width: 100%; border-collapse: collapse; font-size: 14px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
        .newsletter-table th { padding: 12px 16px; text-align: left; font-weight: 600; color: #374151; background: #f9fafb; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
        .newsletter-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; }
        .newsletter-table tr:hover { background: #f9fafb; }
        .empty-state { padding: 32px 16px; text-align: center; color: #9ca3af; }
        .email-badge { display: inline-block; padding: 4px 8px; background: #dcfce7; color: #166534; border-radius: 4px; font-size: 11px; font-weight: 600; }
    </style>

    <div>
        <header class="hub-hero">
            <h1>Newsletter</h1>
            <p>Gérez vos campagnes et vos abonnés</p>
            <div class="hub-stats">
                <div class="hub-stat">
                    <div class="hub-stat-value">0</div>
                    <div class="hub-stat-label">Abonnés</div>
                </div>
                <div class="hub-stat">
                    <div class="hub-stat-value">0</div>
                    <div class="hub-stat-label">Campagnes</div>
                </div>
                <div class="hub-stat">
                    <div class="hub-stat-value">0%</div>
                    <div class="hub-stat-label">Taux d'ouverture</div>
                </div>
            </div>
        </header>

        <div class="tabs">
            <button class="tab active">📧 Newsletters</button>
            <button class="tab">👥 Abonnés</button>
        </div>

        <table class="newsletter-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Abonnés</th>
                    <th>Statut</th>
                    <th>Envoyée</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="4" class="empty-state">Aucune newsletter créée</td></tr>
            </tbody>
        </table>
    </div>
    <?php
}

<?php

declare(strict_types=1);

$pageTitle = 'Contacts & suivi';
$pageDescription = 'Vos demandes et contacts à traiter';

function renderContent(): void {
    ?>
    <style>
        .start-hero {
            background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
            border-radius: 16px;
            padding: 36px 40px;
            color: #fff;
            margin-bottom: 32px;
            box-shadow: 0 4px 20px rgba(15,34,55,.18);
        }
        .start-hero-badge {
            display: inline-block;
            background: rgba(201,168,76,.2);
            color: #c9a84c;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 14px;
            border: 1px solid rgba(201,168,76,.35);
        }
        .start-hero h1 {
            font-size: 28px;
            font-weight: 700;
            color: #fff;
            margin: 0 0 12px;
            line-height: 1.25;
        }
        .start-hero p {
            font-size: 15px;
            color: rgba(255,255,255,.7);
            line-height: 1.65;
            max-width: 640px;
            margin: 0;
        }
        .start-steps-title {
            font-size: 12px;
            font-weight: 700;
            color: #8a95a3;
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-bottom: 16px;
        }
        .start-steps {
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-bottom: 32px;
        }
        .start-step {
            display: flex;
            align-items: flex-start;
            gap: 18px;
            background: #fff;
            border-radius: 12px;
            padding: 20px 22px;
            box-shadow: 0 1px 6px rgba(0,0,0,.07);
            text-decoration: none;
            color: inherit;
            border-left: 4px solid #e8ecf0;
            transition: transform .15s, box-shadow .15s, border-color .15s;
        }
        .start-step:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 16px rgba(0,0,0,.1);
            border-color: #c9a84c;
        }
        .start-step-num {
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            color: #64748b;
        }
        .start-step-body { flex: 1; }
        .start-step-label {
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 3px;
        }
        .start-step-desc {
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
        }
        .start-step-arrow {
            flex-shrink: 0;
            color: #c9a84c;
            font-size: 16px;
            margin-top: 8px;
        }
        @media (max-width: 600px) {
            .start-hero { padding: 24px 20px; }
            .start-step { flex-wrap: wrap; }
        }
    </style>

    <div class="start-hero">
        <div class="start-hero-badge">Contacts & suivi</div>
        <h1>Vos demandes à traiter</h1>
        <p>Retrouvez les contacts reçus depuis le site, leur origine et les actions à mener.</p>
    </div>

    <?php
    $leadBySource = [];
    $leadTotal = 0;
    try {
        $stLeads = db()->query('SELECT id, source_type, email, phone FROM crm_leads ORDER BY created_at DESC, id DESC');
        if ($stLeads) {
            $seenLeadContacts = [];
            foreach ($stLeads->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $src = (string) ($row['source_type'] ?? 'autre');
                $emailKey = strtolower(trim((string) ($row['email'] ?? '')));
                $phoneKey = preg_replace('/\D+/', '', (string) ($row['phone'] ?? ''));
                $contactKey = $emailKey !== ''
                    ? 'email:' . $emailKey
                    : ($phoneKey !== '' ? 'phone:' . $phoneKey : 'id:' . (string) ($row['id'] ?? ''));

                if (isset($seenLeadContacts[$contactKey])) {
                    continue;
                }

                $seenLeadContacts[$contactKey] = true;
                $leadBySource[$src] = (int) ($leadBySource[$src] ?? 0) + 1;
                $leadTotal++;
            }
        }
    } catch (Throwable $e) {
        error_log('[crm-hub] crm_leads count: ' . $e->getMessage());
    }
    $leadSourceLabels = [
        'financement' => ['label' => 'Financement', 'color' => '#b45309', 'bg' => '#fffbeb'],
        'avis_valeur' => ['label' => 'Avis de valeur', 'color' => '#0e7490', 'bg' => '#ecfeff'],
        'estimation' => ['label' => 'Estimation', 'color' => '#1d4ed8', 'bg' => '#eff6ff'],
        'contact' => ['label' => 'Contact', 'color' => '#6d28d9', 'bg' => '#f5f3ff'],
        'telechargement' => ['label' => 'Téléchargement', 'color' => '#047857', 'bg' => '#ecfdf5'],
        'autre' => ['label' => 'Autre', 'color' => '#475569', 'bg' => '#f8fafc'],
    ];
    ?>
    <div style="margin-bottom: 40px;">
        <div class="start-steps-title">Demandes reçues</div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px; margin-bottom: 16px;">
            <a href="/admin?module=contacts" style="text-decoration: none; color: inherit;">
                <div style="background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%); border-radius: 12px; padding: 18px 16px; color: #fff; box-shadow: 0 1px 8px rgba(0,0,0,.08);">
                    <div style="font-size: 11px; font-weight: 700; color: #c9a84c; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px;">Total</div>
                    <div style="font-size: 32px; font-weight: 700;"><?= (int) $leadTotal ?></div>
                    <div style="font-size: 12px; color: rgba(255,255,255,.75); margin-top: 6px;">Voir les contacts</div>
                </div>
            </a>
            <?php foreach ($leadSourceLabels as $srcKey => $cfg):
                $n = (int) ($leadBySource[$srcKey] ?? 0);
                ?>
            <a href="/admin?module=contacts&amp;source=<?= htmlspecialchars($srcKey, ENT_QUOTES, 'UTF-8') ?>" style="text-decoration: none; color: inherit;">
                <div style="background: <?= htmlspecialchars($cfg['bg'], ENT_QUOTES, 'UTF-8') ?>; border-radius: 12px; padding: 16px 14px; border: 1px solid #e5e7eb; box-shadow: 0 1px 4px rgba(0,0,0,.04);">
                    <div style="font-size: 22px; font-weight: 700; color: <?= htmlspecialchars($cfg['color'], ENT_QUOTES, 'UTF-8') ?>;"><?= $n ?></div>
                    <div style="font-size: 13px; color: #64748b; margin-top: 4px;"><?= htmlspecialchars($cfg['label'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="start-steps-title">Accès rapides</div>
    <div class="start-steps">

        <a href="/admin/?module=contacts" class="start-step">
            <div class="start-step-num">1</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-address-book" style="color:#3b82f6;margin-right:6px;"></i>Contacts</div>
                <div class="start-step-desc">Consultez les demandes reçues et ouvrez chaque fiche contact.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin/?module=messagerie" class="start-step">
            <div class="start-step-num">2</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-envelope" style="color:#10b981;margin-right:6px;"></i>Messagerie</div>
                <div class="start-step-desc">Retrouvez les échanges liés à vos contacts.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin/?module=crm-hub&action=conversions" class="start-step">
            <div class="start-step-num">3</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-chart-line" style="color:#f59e0b;margin-right:6px;"></i>Conversions</div>
                <div class="start-step-desc">Suivez les actions importantes réalisées sur le site.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin/?module=scraper" class="start-step">
            <div class="start-step-num">4</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-spider" style="color:#10b981;margin-right:6px;"></i>Prospection web</div>
                <div class="start-step-desc">Ajoutez de nouveaux prospects à partir de sources publiques.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

    </div>
    <?php
}

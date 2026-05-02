<?php

declare(strict_types=1);

$pageTitle = 'Propriétés & Secteurs';
$pageDescription = 'Listings, fiches secteurs (marketing) et lien vers le guide local structuré';

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
        <div class="start-hero-badge">Propriétés & Secteurs</div>
        <h1>Gérez votre catalogue immobilier</h1>
        <p>
            <strong>Listings</strong> = vos annonces. <strong>Secteurs &amp; Zones</strong> = fiches marketing libres (table <code>sectors</code>), distinctes de la
            <strong>base guide local</strong> (villes, quartiers, POI, API, carte) gérée dans le module dédié — évitez les doublons de contenu entre les deux.
        </p>
    </div>

    <div class="start-steps-title">Les modules de cette section</div>
    <div class="start-steps">

        <a href="/admin/?module=listings" class="start-step">
            <div class="start-step-num">1</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-house" style="color:#3b82f6;margin-right:6px;"></i>Listings</div>
                <div class="start-step-desc">Créez et gérez votre catalogue de propriétés. Publiez les détails complets, photos et offres pour attirer les acheteurs et locataires.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin/?module=secteurs" class="start-step">
            <div class="start-step-num">2</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-map" style="color:#10b981;margin-right:6px;"></i>Secteurs &amp; Zones</div>
                <div class="start-step-desc">
                    Fiches éditoriales personnalisées (table <code>sectors</code>) : textes marketing par zone, statut brouillon/publié.
                    Ce n’est <strong>pas</strong> la liste des villes/quartiers ni les POI du site public — voir l’étape 3.
                </div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin/?module=annuaire-local" class="start-step">
            <div class="start-step-num">3</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-store" style="color:#7c3aed;margin-right:6px;"></i>Annuaire local</div>
                <div class="start-step-desc">
                    Commerces &amp; artisans (BDD <code>guide_pois</code>), API, carte <code>/guide-local</code> et fiches <code>/commerces/…</code>.
                    Ne pas dupliquer les fiches marketing « Secteurs » sans raison.
                </div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

    </div>
    <?php
}

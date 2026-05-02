<?php

declare(strict_types=1);

$pageTitle = 'Construire';
$pageDescription = 'Posez les bases solides de votre activité';

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
        <div class="start-hero-badge">Se positionner</div>
        <h1>Construire les fondations de votre système</h1>
        <p>
            Ici, vous définissez votre positionnement, votre cible et votre offre.
            C’est ce qui va faire toute la différence entre un conseiller invisible et un conseiller choisi.
        </p>
    </div>

    <div class="start-steps-title">Les modules de cette section</div>
    <div class="start-steps">

        <a href="/admin/?module=construire&action=ancre" class="start-step">
            <div class="start-step-num">1</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-target" style="color:#3b82f6;margin-right:6px;"></i>Méthode ANCRE+</div>
                <div class="start-step-desc">Clarifiez votre positionnement et générez un message clair et différenciant en quelques minutes.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin/?module=construire&action=profils" class="start-step">
            <div class="start-step-num">2</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-users" style="color:#10b981;margin-right:6px;"></i>NeuroPersona</div>
                <div class="start-step-desc">Identifiez vos clients idéaux et définissez les profils à cibler pour maximiser votre impact.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin/?module=construire&action=offre" class="start-step">
            <div class="start-step-num">3</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-lightbulb" style="color:#f59e0b;margin-right:6px;"></i>Offre Conseiller</div>
                <div class="start-step-desc">Construisez votre offre et créez une proposition claire qui donne envie de vous contacter.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin/?module=construire&action=zone" class="start-step">
            <div class="start-step-num">4</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-map-location-dot" style="color:#ef4444;margin-right:6px;"></i>Zone de Prospection</div>
                <div class="start-step-desc">Définissez votre terrain et concentrez vos efforts sur les zones les plus rentables.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

    </div>
    <?php
}

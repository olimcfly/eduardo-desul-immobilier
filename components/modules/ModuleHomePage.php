<?php
/**
 * Composant template — Page d'accueil standard d'un module (Version "En préparation")
 * /components/modules/ModuleHomePage.php
 */

if (!function_exists('renderModuleHomePage')) {
    /**
     * Affiche une page d'accueil standardisée pour un module
     *
     * @param array<string,mixed> $config Configuration du module
     *   - title: string - Titre du module
     *   - description: string - Description courte
     *   - icon: string - Classe FontAwesome (ex: 'fa-home')
     *   - quick_access: array - Accès rapides (titre, description, icon, url)
     *   - future_features: array - Fonctionnalités futures
     * @return void
     */
    function renderModuleHomePage(array $config): void
    {
        $title = $config['title'] ?? 'Module';
        $description = $config['description'] ?? 'Bienvenue dans ce module.';
        $icon = $config['icon'] ?? 'fa-box';
        $quickAccess = $config['quick_access'] ?? [];
        $futureFeatures = $config['future_features'] ?? [];
        ?>
        <style>
            .module-home {
                padding: 2rem;
                max-width: 1200px;
                margin: 0 auto;
                background: #f8f9fa;
                min-height: 100vh;
            }

            .module-header {
                text-align: center;
                margin-bottom: 2rem;
                background: #fff;
                padding: 3rem 2rem;
                border-radius: 0.75rem;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }

            .module-header i {
                color: #4f7df3;
                margin-bottom: 1rem;
                font-size: 2.5rem;
            }

            .module-header h1 {
                margin: 0.5rem 0;
                font-size: 2rem;
                color: #1f2937;
                font-weight: 700;
            }

            .module-header p {
                margin: 0.5rem 0 0;
                color: #6b7280;
                font-size: 1rem;
            }

            .alert {
                padding: 1rem 1.5rem;
                margin-bottom: 2rem;
                border-left: 4px solid #ffc107;
                background: #fff3cd;
                color: #856404;
                border-radius: 0.5rem;
                display: flex;
                align-items: flex-start;
                gap: 1rem;
            }

            .alert i {
                font-size: 1.25rem;
                flex-shrink: 0;
                margin-top: 0.125rem;
            }

            .alert-content h3 {
                margin: 0 0 0.5rem;
                font-size: 0.95rem;
                font-weight: 600;
            }

            .alert-content p {
                margin: 0;
                font-size: 0.9rem;
            }

            .quick-access {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            }

            .card {
                background: #fff;
                padding: 1.5rem;
                border-radius: 0.75rem;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                text-decoration: none;
                color: #1f2937;
                transition: all 0.2s ease;
                border: 1px solid #e5e7eb;
                display: flex;
                flex-direction: column;
            }

            .card:hover {
                transform: translateY(-4px);
                box-shadow: 0 8px 12px rgba(0,0,0,0.1);
                border-color: #4f7df3;
            }

            .card-icon {
                font-size: 1.75rem;
                color: #4f7df3;
                margin-bottom: 1rem;
            }

            .card h3 {
                margin: 0 0 0.5rem;
                font-size: 1rem;
                font-weight: 600;
                color: #1f2937;
            }

            .card p {
                margin: 0;
                color: #6b7280;
                font-size: 0.9rem;
                flex: 1;
            }

            .future-content {
                background: #fff;
                padding: 2rem;
                border-radius: 0.75rem;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                border: 1px solid #e5e7eb;
            }

            .future-content h3 {
                margin: 0 0 1.5rem;
                font-size: 1.25rem;
                color: #1f2937;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .future-content h3 i {
                color: #4f7df3;
            }

            .future-content ul {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .future-content li {
                padding: 0.75rem 0;
                padding-left: 1.75rem;
                color: #6b7280;
                position: relative;
                font-size: 0.95rem;
            }

            .future-content li:before {
                content: "✓";
                position: absolute;
                left: 0;
                color: #4f7df3;
                font-weight: 600;
            }

            /* Mobile Responsive */
            @media (max-width: 768px) {
                .module-home {
                    padding: 1rem;
                }

                .module-header {
                    padding: 2rem 1rem;
                }

                .module-header h1 {
                    font-size: 1.5rem;
                }

                .module-header p {
                    font-size: 0.9rem;
                }

                .quick-access {
                    grid-template-columns: 1fr;
                }

                .future-content {
                    padding: 1.5rem;
                }
            }
        </style>

        <div class="module-home">
            <!-- En-tête du module -->
            <div class="module-header">
                <i class="fas <?= htmlspecialchars($icon) ?>"></i>
                <h1><?= htmlspecialchars($title) ?></h1>
                <p><?= htmlspecialchars($description) ?></p>
            </div>

            <!-- Message "En préparation" -->
            <div class="alert">
                <i class="fas fa-info-circle"></i>
                <div class="alert-content">
                    <h3>Module en développement</h3>
                    <p>Ce module est en cours de développement. Voici les fonctionnalités disponibles pour l'instant :</p>
                </div>
            </div>

            <!-- Accès rapides -->
            <?php if (!empty($quickAccess)): ?>
                <div class="quick-access">
                    <?php foreach ($quickAccess as $item): ?>
                        <a href="<?= htmlspecialchars($item['url'] ?? '#') ?>" class="card">
                            <i class="fas <?= htmlspecialchars($item['icon'] ?? 'fa-link') ?> card-icon"></i>
                            <h3><?= htmlspecialchars($item['title'] ?? '') ?></h3>
                            <p><?= htmlspecialchars($item['description'] ?? '') ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Fonctionnalités futures -->
            <?php if (!empty($futureFeatures)): ?>
                <div class="future-content">
                    <h3><i class="fas fa-rocket"></i>Prochainement</h3>
                    <ul>
                        <?php foreach ($futureFeatures as $feature): ?>
                            <li><?= htmlspecialchars($feature) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}

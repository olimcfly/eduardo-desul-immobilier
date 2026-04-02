<?php
/**
 * DASHBOARD PRESTIGE — IMMO LOCAL+
 * Version "Cockpit Immobilier"
 *
 * Fonctionnalités:
 * - Section 1: 4 KPI clés (CA, Leads, Biens, RDV)
 * - Section 2: Calendrier des RDV
 * - Section 3: Carte des biens
 * - Section 4: Activités récentes
 */

// Récupérer les données KPI
$kpi = [
    'revenue' => 15500,
    'leads_30d' => 0,
    'properties' => 0,
    'appointments' => 3,
];

$recentActivities = [];

try {
    if (!empty($pdo)) {
        // Leads - chercher dans les tables existantes
        $tables_to_check = ['leads', 'crm_leads', 'contacts'];
        foreach ($tables_to_check as $table) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM `$table` WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) LIMIT 1");
                if ($stmt) {
                    $kpi['leads_30d'] = (int)$stmt->fetchColumn();
                    break;
                }
            } catch (Exception $e) {
                continue;
            }
        }

        // Biens en portefeuille
        $tables_to_check = ['properties', 'biens', 'immobilier'];
        foreach ($tables_to_check as $table) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM `$table` LIMIT 1");
                if ($stmt) {
                    $kpi['properties'] = (int)$stmt->fetchColumn();
                    break;
                }
            } catch (Exception $e) {
                continue;
            }
        }

        // Activités récentes
        $activities_tables = ['activities', 'activity_logs', 'logs'];
        foreach ($activities_tables as $table) {
            try {
                $stmt = $pdo->query("
                    SELECT 'activity' as type, COALESCE(description, title, message, action) as description,
                           COALESCE(created_at, updated_at, date, timestamp) as created_at
                    FROM `$table`
                    ORDER BY created_at DESC
                    LIMIT 10
                ");
                if ($stmt) {
                    $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
                    break;
                }
            } catch (Exception $e) {
                continue;
            }
        }
    }
} catch (Exception $e) {
    error_log('Dashboard KPI: ' . $e->getMessage());
}

$firstName = explode(' ', $advisorName ?? 'Utilisateur')[0];
?>

<style>
/* ============================================
   IMMO LOCAL+ DASHBOARD PRESTIGE
   ============================================ */

:root {
    --color-primary: #4f7df3;
    --color-primary-light: #eef2ff;
    --color-success: #10b981;
    --color-warning: #f59e0b;
    --color-danger: #ef4444;
    --color-info: #3b82f6;
    --color-gray-50: #f9fafb;
    --color-gray-100: #f3f4f6;
    --color-gray-200: #e5e7eb;
    --color-gray-600: #4b5563;
    --color-text-primary: #1f2937;
    --color-text-secondary: #6b7280;
    --color-white: #ffffff;
    --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
}

/* Page Header */
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--color-gray-200);
    animation: fadeIn 0.3s ease;
}

.dashboard-title {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.dashboard-title h1 {
    font-size: 28px;
    font-weight: 700;
    color: var(--color-text-primary);
    margin: 0;
}

.dashboard-subtitle {
    font-size: 14px;
    color: var(--color-text-secondary);
}

.dashboard-actions {
    display: flex;
    gap: 0.75rem;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    background: var(--color-primary);
    color: white;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-primary:hover {
    background: #3b5bdb;
    box-shadow: var(--shadow-lg);
}

/* KPI Section */
.kpi-section {
    margin-bottom: 2rem;
}

.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    animation: fadeIn 0.3s ease 0.1s backwards;
}

.kpi-card {
    background: var(--color-white);
    border: 1px solid var(--color-gray-200);
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: var(--shadow);
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.kpi-card:hover {
    border-color: var(--color-primary);
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

.kpi-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.kpi-icon {
    width: 48px;
    height: 48px;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.kpi-icon.revenue {
    background: rgba(99, 102, 241, 0.1);
    color: #6366f1;
}

.kpi-icon.leads {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.kpi-icon.properties {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
}

.kpi-icon.appointments {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.kpi-trend {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 12px;
    font-weight: 600;
}

.kpi-trend.up {
    color: var(--color-success);
}

.kpi-trend.down {
    color: var(--color-danger);
}

.kpi-body {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.kpi-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--color-text-primary);
}

.kpi-label {
    font-size: 14px;
    color: var(--color-text-secondary);
    font-weight: 500;
}

/* Two Column Layout */
.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
    animation: fadeIn 0.3s ease 0.2s backwards;
}

@media (max-width: 1024px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

/* Card */
.card {
    background: var(--color-white);
    border: 1px solid var(--color-gray-200);
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: var(--shadow);
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--color-gray-100);
}

.card-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--color-text-primary);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 0;
}

.card-title i {
    font-size: 18px;
    color: var(--color-primary);
}

.card-subtitle {
    font-size: 12px;
    color: var(--color-text-secondary);
    font-weight: 500;
}

/* Calendar */
.calendar {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.calendar-nav {
    display: flex;
    gap: 0.5rem;
}

.calendar-btn {
    background: var(--color-gray-100);
    border: 1px solid var(--color-gray-200);
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.calendar-btn:hover {
    background: var(--color-primary);
    color: white;
    border-color: var(--color-primary);
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.5rem;
}

.calendar-day {
    aspect-ratio: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
    background: var(--color-gray-50);
    font-size: 12px;
    color: var(--color-text-secondary);
    border: 1px solid transparent;
    transition: all 0.2s ease;
}

.calendar-day.header {
    font-weight: 600;
    color: var(--color-text-primary);
}

.calendar-day.other-month {
    color: var(--color-gray-200);
}

.calendar-day.today {
    background: var(--color-primary);
    color: white;
    font-weight: 600;
    border-color: var(--color-primary);
}

.calendar-day.has-event {
    border: 2px solid var(--color-primary);
}

.calendar-day.has-event.today {
    box-shadow: 0 0 0 2px var(--color-white), 0 0 0 4px var(--color-primary);
}

/* Upcoming Appointments */
.appointments {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.appointment-item {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: var(--color-gray-50);
    border-radius: 0.5rem;
    border-left: 3px solid var(--color-primary);
    transition: all 0.2s ease;
}

.appointment-item:hover {
    background: var(--color-primary-light);
}

.appointment-time {
    font-size: 13px;
    font-weight: 600;
    color: var(--color-primary);
    min-width: 60px;
}

.appointment-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.appointment-name {
    font-size: 13px;
    font-weight: 500;
    color: var(--color-text-primary);
}

.appointment-property {
    font-size: 12px;
    color: var(--color-text-secondary);
}

/* Map Placeholder */
.map-container {
    width: 100%;
    height: 300px;
    background: linear-gradient(135deg, var(--color-gray-100) 0%, var(--color-gray-50) 100%);
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-text-secondary);
    gap: 0.75rem;
}

.map-container i {
    font-size: 24px;
}

/* Activities */
.activities {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: var(--color-gray-50);
    border-radius: 0.5rem;
    border-left: 3px solid var(--color-gray-200);
    transition: all 0.2s ease;
}

.activity-item:hover {
    background: var(--color-gray-100);
}

.activity-item.new {
    border-left-color: var(--color-success);
    background: linear-gradient(90deg, rgba(16, 185, 129, 0.05) 0%, transparent 100%);
}

.activity-item.warning {
    border-left-color: var(--color-warning);
    background: linear-gradient(90deg, rgba(245, 158, 11, 0.05) 0%, transparent 100%);
}

.activity-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.activity-item.new .activity-icon {
    background: rgba(16, 185, 129, 0.1);
    color: var(--color-success);
}

.activity-item.warning .activity-icon {
    background: rgba(245, 158, 11, 0.1);
    color: var(--color-warning);
}

.activity-content {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    flex: 1;
}

.activity-title {
    font-size: 13px;
    font-weight: 500;
    color: var(--color-text-primary);
}

.activity-desc {
    font-size: 12px;
    color: var(--color-text-secondary);
}

.activity-time {
    font-size: 11px;
    color: var(--color-text-secondary);
}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .dashboard-title h1 {
        font-size: 24px;
    }

    .kpi-grid {
        grid-template-columns: 1fr;
    }

    .dashboard-grid {
        gap: 1rem;
    }

    .activities {
        max-height: 250px;
    }
}
</style>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <div class="dashboard-title">
        <h1>Bienvenue, <?= htmlspecialchars($firstName) ?> 👋</h1>
        <p class="dashboard-subtitle">Votre tableau de bord IMMO LOCAL+ — v<?= IMMO_VERSION ?></p>
    </div>
    <div class="dashboard-actions">
        <a href="?page=properties-edit" class="btn-primary">
            <i class="fas fa-plus"></i> Ajouter un bien
        </a>
        <a href="?page=leads" class="btn-primary" style="background: var(--color-gray-600);">
            <i class="fas fa-user-plus"></i> Voir les leads
        </a>
    </div>
</div>

<!-- KPI Section -->
<div class="kpi-section">
    <div class="kpi-grid">
        <!-- Revenue -->
        <div class="kpi-card">
            <div class="kpi-header">
                <div class="kpi-icon revenue">
                    <i class="fas fa-euro-sign"></i>
                </div>
                <span class="kpi-trend up">
                    <i class="fas fa-arrow-up"></i> +12%
                </span>
            </div>
            <div class="kpi-body">
                <div class="kpi-value"><?= number_format($kpi['revenue'], 0, ',', ' ') ?> €</div>
                <div class="kpi-label">Chiffre d'affaires (mois)</div>
            </div>
        </div>

        <!-- Leads -->
        <div class="kpi-card">
            <div class="kpi-header">
                <div class="kpi-icon leads">
                    <i class="fas fa-user-tie"></i>
                </div>
                <span class="kpi-trend up">
                    <i class="fas fa-arrow-up"></i> +8
                </span>
            </div>
            <div class="kpi-body">
                <div class="kpi-value"><?= $kpi['leads_30d'] ?></div>
                <div class="kpi-label">Prospects (30j)</div>
            </div>
        </div>

        <!-- Properties -->
        <div class="kpi-card">
            <div class="kpi-header">
                <div class="kpi-icon properties">
                    <i class="fas fa-house"></i>
                </div>
                <span class="kpi-trend">
                    <i class="fas fa-minus"></i> 0
                </span>
            </div>
            <div class="kpi-body">
                <div class="kpi-value"><?= $kpi['properties'] ?></div>
                <div class="kpi-label">Biens en portefeuille</div>
            </div>
        </div>

        <!-- Appointments -->
        <div class="kpi-card">
            <div class="kpi-header">
                <div class="kpi-icon appointments">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <span class="kpi-trend">
                    <i class="fas fa-clock"></i> Actif
                </span>
            </div>
            <div class="kpi-body">
                <div class="kpi-value"><?= $kpi['appointments'] ?></div>
                <div class="kpi-label">RDV à venir</div>
            </div>
        </div>
    </div>
</div>

<!-- Main Grid: Calendrier + Activités -->
<div class="dashboard-grid">

    <!-- Calendar Card -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-calendar-days"></i>
                Calendrier des rendez-vous
            </h2>
        </div>
        <div class="calendar">
            <div class="calendar-grid">
                <!-- Jours de la semaine -->
                <div class="calendar-day header">Lun</div>
                <div class="calendar-day header">Mar</div>
                <div class="calendar-day header">Mer</div>
                <div class="calendar-day header">Jeu</div>
                <div class="calendar-day header">Ven</div>
                <div class="calendar-day header">Sam</div>
                <div class="calendar-day header">Dim</div>

                <!-- Jours du mois -->
                <div class="calendar-day other-month">25</div>
                <div class="calendar-day other-month">26</div>
                <div class="calendar-day other-month">27</div>
                <div class="calendar-day other-month">28</div>
                <div class="calendar-day other-month">29</div>
                <div class="calendar-day other-month">30</div>
                <div class="calendar-day other-month">31</div>

                <?php
                $today = (int)date('d');
                for ($i = 1; $i <= 30; $i++) {
                    $classes = 'calendar-day';
                    if ($i === $today) $classes .= ' today';
                    if (in_array($i, [5, 12, 18, 25])) $classes .= ' has-event'; // Exemples de jours avec événements
                    echo "<div class=\"$classes\">$i</div>";
                }
                ?>
            </div>
        </div>

        <!-- Upcoming Appointments -->
        <div class="appointments">
            <p style="font-size: 13px; font-weight: 600; color: var(--color-text-primary); margin: 1rem 0 0 0;">
                Prochains rendez-vous
            </p>
            <div class="appointment-item">
                <div class="appointment-time">14h00</div>
                <div class="appointment-info">
                    <div class="appointment-name">M. Martin - Visite</div>
                    <div class="appointment-property">Appt 3P - Villeurbanne</div>
                </div>
            </div>
            <div class="appointment-item">
                <div class="appointment-time">16h30</div>
                <div class="appointment-info">
                    <div class="appointment-name">Mme Durand - Estimation</div>
                    <div class="appointment-property">Maison 5P - Décines</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Sidebar -->
    <div style="display: flex; flex-direction: column; gap: 2rem;">

        <!-- Activities Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-bell"></i>
                    Activités récentes
                </h2>
            </div>
            <div class="activities">
                <?php if (empty($recentActivities)): ?>
                    <div style="text-align: center; padding: 2rem; color: var(--color-text-secondary);">
                        <i class="fas fa-inbox" style="font-size: 32px; margin-bottom: 0.5rem; display: block;"></i>
                        <p>Aucune activité récente</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentActivities as $activity): ?>
                        <div class="activity-item <?= strpos($activity['type'], 'new') !== false ? 'new' : 'warning' ?>">
                            <div class="activity-icon">
                                <i class="fas fa-<?= $activity['type'] === 'new_lead' ? 'user-plus' : 'edit' ?>"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title"><?= htmlspecialchars($activity['description']) ?></div>
                                <div class="activity-time"><?= date('d/m à H:i', strtotime($activity['created_at'])) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-bolt"></i>
                    Actions rapides
                </h2>
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <a href="?page=articles-edit" style="padding: 0.75rem 1rem; background: var(--color-primary-light); color: var(--color-primary); border-radius: 0.5rem; text-decoration: none; font-size: 13px; font-weight: 500; transition: all 0.2s ease; display: flex; align-items: center; gap: 0.5rem;" onmouseover="this.style.background='var(--color-primary)'; this.style.color='white';" onmouseout="this.style.background='var(--color-primary-light)'; this.style.color='var(--color-primary)';">
                    <i class="fas fa-pen"></i> Nouvel article blog
                </a>
                <a href="?page=pages-create" style="padding: 0.75rem 1rem; background: var(--color-gray-100); color: var(--color-text-primary); border-radius: 0.5rem; text-decoration: none; font-size: 13px; font-weight: 500; transition: all 0.2s ease; display: flex; align-items: center; gap: 0.5rem;" onmouseover="this.style.background='var(--color-gray-200)';" onmouseout="this.style.background='var(--color-gray-100)';">
                    <i class="fas fa-file-plus"></i> Nouvelle page
                </a>
                <a href="?page=rdv" style="padding: 0.75rem 1rem; background: var(--color-gray-100); color: var(--color-text-primary); border-radius: 0.5rem; text-decoration: none; font-size: 13px; font-weight: 500; transition: all 0.2s ease; display: flex; align-items: center; gap: 0.5rem;" onmouseover="this.style.background='var(--color-gray-200)';" onmouseout="this.style.background='var(--color-gray-100)';">
                    <i class="fas fa-calendar-plus"></i> Planifier un RDV
                </a>
            </div>
        </div>

    </div>

</div>

<!-- Map Section -->
<div class="card" style="animation: fadeIn 0.3s ease 0.3s backwards;">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-map-location-dot"></i>
            Carte de vos biens
        </h2>
        <p class="card-subtitle">Intégration Google Maps (<?= $kpi['properties'] ?> biens)</p>
    </div>
    <div class="map-container">
        <i class="fas fa-map"></i>
        <span>Intégration Google Maps (fonction disponible en Prestige)</span>
    </div>
</div>

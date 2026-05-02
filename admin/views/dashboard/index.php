<style>
    .dashboard-container { display: grid; gap: 24px; }
    .dashboard-header {
        background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
        border-radius: 16px;
        padding: 24px 20px;
        color: #fff;
    }
    .dashboard-header h1 { margin: 0 0 8px; font-size: 28px; font-weight: 700; }
    .dashboard-header p { margin: 0; color: rgba(255,255,255,.78); font-size: 15px; }
    .dashboard-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
    }
    .stat-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
        text-align: center;
    }
    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 8px;
    }
    .stat-label {
        font-size: 13px;
        color: #64748b;
        font-weight: 600;
    }
    .stat-card.highlight .stat-value { color: #c9a84c; }
    .stat-card.danger .stat-value { color: #dc2626; }
    .leads-section {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
    }
    .leads-header {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        background: #f9fafb;
    }
    .leads-header h2 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #0f172a;
    }
    .leads-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    .leads-table th {
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        font-size: 13px;
    }
    .leads-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
    }
    .leads-table tr:hover { background: #f9fafb; }
    .empty-state {
        padding: 32px 16px;
        text-align: center;
        color: #9ca3af;
    }
    .source-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }
    .source-badge.financement { background: #dcfce7; color: #166534; }
    .source-badge.estimation { background: #dbeafe; color: #1d4ed8; }
    .source-badge.rdv { background: #fef3c7; color: #92400e; }
    .source-badge.contact { background: #f3f4f6; color: #6b7280; }
    .lead-nom {
        font-weight: 600;
        color: #0f172a;
        display: block;
        margin-bottom: 4px;
    }
    .lead-contact {
        font-size: 12px;
        color: #64748b;
    }
</style>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Tableau de bord</h1>
        <p>Bienvenue! Voici vos actions prioritaires.</p>
    </div>

    <div class="dashboard-stats">
        <div class="stat-card highlight">
            <div class="stat-value">0</div>
            <div class="stat-label">Leads à traiter</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">0</div>
            <div class="stat-label">Leads ce mois</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">0</div>
            <div class="stat-label">Biens en gestion</div>
        </div>
        <div class="stat-card danger">
            <div class="stat-value">0</div>
            <div class="stat-label">Financement</div>
        </div>
    </div>

    <div class="leads-section">
        <div class="leads-header">
            <h2>📞 Derniers contacts</h2>
        </div>
        <table class="leads-table">
            <thead>
                <tr>
                    <th>Nom & Contact</th>
                    <th>Type</th>
                    <th>Statut</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4" class="empty-state">Aucun lead à traiter pour le moment</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

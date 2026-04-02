<?php
// ============================================================
// ADMIN — Dashboard Controller
// ============================================================

class DashboardController
{
    public function index(): void
    {
        $adminUser = Auth::user();

        // Stats de base (sans DB si tables pas encore créées)
        $stats = [
            'biens_total'   => 0,
            'biens_actifs'  => 0,
            'contacts'      => 0,
            'estimations'   => 0,
        ];

        try {
            $db = Database::getInstance();
            $stats['biens_total']  = (int) $db->query('SELECT COUNT(*) FROM biens')->fetchColumn();
            $stats['biens_actifs'] = (int) $db->query("SELECT COUNT(*) FROM biens WHERE statut = 'actif'")->fetchColumn();
            $stats['contacts']     = (int) $db->query('SELECT COUNT(*) FROM contacts')->fetchColumn();
            $stats['estimations']  = (int) $db->query('SELECT COUNT(*) FROM estimations')->fetchColumn();
        } catch (Exception $e) {
            // Tables pas encore créées — on affiche des zéros
        }

        adminLayout('dashboard/index', [
            'pageTitle'    => 'Tableau de bord',
            'stats'        => $stats,
            'adminUser'    => $adminUser,
            'biensRecents' => [],
            'avisRecents'  => [],
            'topKeywords'  => [],
            'socialQueue'  => [],
        ]);
    }
}

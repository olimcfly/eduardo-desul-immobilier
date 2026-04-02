<?php
namespace Admin\Controllers;

use Core\Controller;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $bienModel   = $this->model('Bien');
        $gmbModel    = $this->model('GmbReview');
        $seoModel    = $this->model('SeoKeyword');
        $socialModel = $this->model('SocialPost');

        $stats = [
            'biens_total'   => $bienModel->count(),
            'biens_actifs'  => $bienModel->count(['statut' => 'actif']),
            'biens_pending' => $bienModel->count(['statut' => 'pending']),
            'gmb_note'      => $gmbModel->avgNote(),
            'avis_total'    => $gmbModel->count(),
            'keywords_top'  => $seoModel->countTop10(),
            'social_queued' => $socialModel->countQueued(),
        ];

        $biensRecents  = $bienModel->recent(5);
        $topKeywords   = $seoModel->top(8);
        $socialQueue   = $socialModel->nextScheduled(4);
        $gmbRecent     = $gmbModel->recent(3);

        $this->view('dashboard/index', compact(
            'stats',
            'biensRecents',
            'topKeywords',
            'socialQueue',
            'gmbRecent'
        ));
    }
}

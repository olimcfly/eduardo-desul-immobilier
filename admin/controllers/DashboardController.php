<?php
class DashboardController extends Controller {
    public function index() {
        $stats = $this->getStatsData();
        $this->render('admin/views/dashboard/index', ['stats' => $stats]);
    }

    public function getStats() {
        header('Content-Type: application/json');
        echo json_encode($this->getStatsData());
    }

    private function getStatsData(): array {
        $stats = ['biens' => 0, 'leads' => 0, 'visites' => 0, 'avis' => 0];

        try {
            if (class_exists('BienService')) {
                $stats['biens'] = BienService::countActiveProperties();
            }
        } catch (Exception $e) {}

        try {
            if (class_exists('ReviewService')) {
                $stats['avis'] = ReviewService::countApproved();
            }
        } catch (Exception $e) {}

        return $stats;
    }
}

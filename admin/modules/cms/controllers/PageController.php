<?php
namespace Admin\Modules\Cms\Controllers;

use Admin\Modules\Cms\Services\CmsService;

class PageController {
    private CmsService $cmsService;

    public function __construct() {
        $this->cmsService = new CmsService();
    }

    // Éditer une page
    public function edit($page_slug) {
        if (!\Auth::check()) {
            \Session::flash('error', 'Connectez-vous pour accéder à cette page.');
            header('Location: /admin/login');
            exit;
        }

        $page_slug = (string) $page_slug;
        $pageData = $this->cmsService->getPageContent($page_slug);
        $sections = $this->cmsService->getSectionsDefinition($page_slug);

        include __DIR__ . '/../views/pages/edit.php';
    }

    // Sauvegarder une page
    public function save() {
        if (!\Auth::check()) {
            \Session::flash('error', 'Connectez-vous pour accéder à cette page.');
            header('Location: /admin/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pageSlug = (string) ($_POST['page_slug'] ?? '');
            $sections = $_POST['sections'] ?? [];
            $this->cmsService->savePageContent($pageSlug, is_array($sections) ? $sections : []);
            header('Location: /admin/cms/edit/' . rawurlencode($pageSlug) . '?success=1');
            exit;
        }
    }
}

<?php
namespace Admin\Modules\Cms\Controllers;

use Core\Controller;
use Admin\Modules\Cms\Services\CmsService;

class PageController extends Controller {
    private $cmsService;

    public function __construct() {
        parent::__construct();
        $this->cmsService = new CmsService();
        $this->checkAuth(); // Vérifie que l'utilisateur est connecté
    }

    // Liste des pages
    public function index() {
        $pages = $this->cmsService->getPagesList();
        $this->view('cms/pages/index', ['pages' => $pages]);
    }

    // Éditer une page
    public function edit($page_slug) {
        $page = $this->cmsService->getPageData($page_slug);
        $this->view('cms/pages/edit', [
            'page' => $page,
            'page_slug' => $page_slug
        ]);
    }

    // Sauvegarder une page
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->cmsService->savePage($_POST);
            header("Location: /admin/cms/edit/{$_POST['page_slug']}?success=1");
            exit;
        }
    }
}

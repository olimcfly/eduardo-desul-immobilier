<?php
// core/controllers/ZoneController.php

class ZoneController
{
    public function show($type, $slug)
    {
        // Nettoyage des paramètres
        $type = $this->sanitizeParameter($type);
        $slug = $this->sanitizeParameter($slug);

        // Détermination du chemin du fichier
        $pageKey = $this->findPageKey($type, $slug);

        // Si aucune page trouvée
        if ($pageKey === null) {
            $this->renderErrorPage(404, 'Page introuvable', 'La page demandée est introuvable.');
            return;
        }

        // Vérification du fichier de page
        $pageFile = ROOT_PATH . '/public/pages/' . $pageKey . '.php';
        if (!is_file($pageFile) || !is_readable($pageFile)) {
            $this->renderErrorPage(500, 'Erreur interne', 'Le fichier de page est introuvable ou inaccessible.');
            return;
        }

        // Titre de repli (écrasé par $pageTitle défini dans le fichier de page)
        $pageTitle = $this->generatePageTitle($type, $slug);

        // Exécuter le fichier de page dans le scope courant pour que ses variables
        // ($pageTitle, $metaDesc, $extraCss, $extraJs…) soient disponibles pour layout.php
        ob_start();
        require $pageFile;
        $pageContent = ob_get_clean();

        require ROOT_PATH . '/public/templates/layout.php';
    }

    private function sanitizeParameter($param)
    {
        return preg_replace('/[^a-z0-9\-_]/i', '', $param);
    }

    private function findPageKey($type, $slug)
    {
        if (!empty($type) && in_array($type, ['villes', 'quartiers', 'regions'])) {
            $tryFile = ROOT_PATH . '/public/pages/zones/' . $type . '/' . $slug . '.php';
            if (is_file($tryFile)) {
                return 'zones/' . $type . '/' . $slug;
            }
        }

        if (!empty($slug)) {
            $cityFile = ROOT_PATH . '/public/pages/zones/villes/' . $slug . '.php';
            $districtFile = ROOT_PATH . '/public/pages/zones/quartiers/' . $slug . '.php';

            if (is_file($cityFile)) {
                return 'zones/villes/' . $slug;
            } elseif (is_file($districtFile)) {
                return 'zones/quartiers/' . $slug;
            }
        }

        return null;
    }

    private function generatePageTitle($type, $slug)
    {
        $typeLabel = '';
        if ($type === 'villes') {
            $typeLabel = 'Ville';
        } elseif ($type === 'quartiers') {
            $typeLabel = 'Quartier';
        } elseif ($type === 'regions') {
            $typeLabel = 'Région';
        }

        return !empty($typeLabel) ? $typeLabel . ' : ' . ucfirst($slug) : ucfirst($slug);
    }

    private function renderPageContent($pageFile)
    {
        ob_start();
        require $pageFile;
        return ob_get_clean();
    }

    private function renderErrorPage($statusCode, $title, $message)
    {
        http_response_code($statusCode);
        $pageTitle = $title;
        $pageContent = '<section class="section"><div class="container"><h1>' . $statusCode . '</h1><p>' . $message . '</p></div></section>';
        require ROOT_PATH . '/public/templates/layout.php';
        exit;
    }
}

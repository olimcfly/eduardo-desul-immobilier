<?php

class PromptController
{
    private Prompt $model;
    private PromptBuilderService $builder;

    private array $types = ['article', 'secteur', 'reseaux', 'image', 'email', 'seo', 'gmb'];
    private array $platforms = ['facebook', 'google', 'tiktok', 'linkedin'];

    public function __construct(PDO $db)
    {
        $rules = require dirname(__DIR__, 2) . '/config/prompt_rules.php';
        $this->model = new Prompt($db);
        $this->builder = new PromptBuilderService($rules);
    }

    public function handle(): void
    {
        $action = $_GET['action'] ?? 'index';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postAction = $_POST['action'] ?? '';
            if ($postAction === 'store') {
                $this->store();
                return;
            }
            if ($postAction === 'update') {
                $this->update();
                return;
            }
            if ($postAction === 'delete') {
                $this->delete();
                return;
            }
            if ($postAction === 'seed') {
                $this->seedDefaults();
                return;
            }
        }

        if ($action === 'create') {
            $this->create();
            return;
        }
        if ($action === 'edit') {
            $this->edit();
            return;
        }

        $this->index();
    }

    private function index(): void
    {
        $filterType = $_GET['type'] ?? null;
        if (!in_array($filterType, $this->types, true)) {
            $filterType = null;
        }

        $prompts = $this->model->all($filterType);
        $preview = null;
        $strategy = null;

        if (isset($_GET['test_id'])) {
            $testId = (int) $_GET['test_id'];
            $prompt = $this->model->find($testId);
            if ($prompt) {
                $data = [
                    'ville' => $_GET['ville'] ?? '',
                    'persona' => $_GET['persona'] ?? '',
                    'objectif' => $_GET['objectif'] ?? '',
                    'mot_cle' => $_GET['mot_cle'] ?? '',
                    'niveau_conscience' => $_GET['niveau_conscience'] ?? '',
                    'type_contenu' => $_GET['type_contenu'] ?? '',
                ];
                $preview = [
                    'id' => $prompt['id'],
                    'name' => $prompt['name'],
                    'compiled_template' => $this->builder->renderTemplate($prompt['template'], $data),
                    'full_prompt' => $this->builder->buildPrompt((string) $prompt['type'], $prompt['plateforme'], $data),
                ];
                $strategy = $this->builder->suggestStrategy($data);
            }
        }

        $types = $this->types;
        $platforms = $this->platforms;
        include dirname(__DIR__, 2) . '/admin/prompts/index.php';
    }

    private function create(): void
    {
        $types = $this->types;
        $platforms = $this->platforms;
        include dirname(__DIR__, 2) . '/admin/prompts/create.php';
    }

    private function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $prompt = $this->model->find($id);
        if (!$prompt) {
            $_SESSION['auth_prompt_flash'] = ['type' => 'error', 'message' => 'Prompt introuvable'];
            header('Location: /admin/dashboard.php?page=ai-prompts');
            exit;
        }

        $types = $this->types;
        $platforms = $this->platforms;
        include dirname(__DIR__, 2) . '/admin/prompts/edit.php';
    }

    private function store(): void
    {
        $payload = $this->sanitizePayload($_POST);
        $this->model->create($payload);
        $_SESSION['auth_prompt_flash'] = ['type' => 'success', 'message' => 'Prompt créé avec succès'];
        header('Location: /admin/dashboard.php?page=ai-prompts');
        exit;
    }

    private function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $payload = $this->sanitizePayload($_POST);
        $this->model->update($id, $payload);
        $_SESSION['auth_prompt_flash'] = ['type' => 'success', 'message' => 'Prompt mis à jour'];
        header('Location: /admin/dashboard.php?page=ai-prompts');
        exit;
    }

    private function delete(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->delete($id);
            $_SESSION['auth_prompt_flash'] = ['type' => 'success', 'message' => 'Prompt supprimé'];
        }

        header('Location: /admin/dashboard.php?page=ai-prompts');
        exit;
    }

    private function seedDefaults(): void
    {
        if ($this->model->count() === 0) {
            foreach ($this->defaultTemplates() as $template) {
                $this->model->create($template);
            }
        }

        $_SESSION['auth_prompt_flash'] = ['type' => 'success', 'message' => '10 prompts système installés'];
        header('Location: /admin/dashboard.php?page=ai-prompts');
        exit;
    }

    private function sanitizePayload(array $input): array
    {
        $name = trim(strip_tags((string) ($input['name'] ?? '')));
        $template = trim((string) ($input['template'] ?? ''));

        return [
            'name' => $name !== '' ? $name : 'Prompt sans nom',
            'type' => (string) ($input['type'] ?? 'article'),
            'plateforme' => (string) ($input['plateforme'] ?? ''),
            'template' => htmlspecialchars($template, ENT_QUOTES, 'UTF-8'),
            'is_active' => !empty($input['is_active']) ? 1 : 0,
        ];
    }

    private function defaultTemplates(): array
    {
        return [
            ['name' => 'Article SEO Pilier', 'type' => 'article', 'plateforme' => 'google', 'template' => 'CONTEXTE : Tu es expert en SEO immobilier local. OBJECTIF : Rédiger un article pilier optimisé SEO pour {{ville}}. CIBLE : {{persona}}. SEO : Mot-clé principal {{mot_cle}}. FORMAT : Introduction, sections H1/H2/H3, conclusion + CTA discret.', 'is_active' => 1],
            ['name' => 'Articles Satellites', 'type' => 'seo', 'plateforme' => 'google', 'template' => 'OBJECTIF : Créer 5 idées d\'articles satellites autour du pilier pour {{ville}} et {{persona}}. FORMAT : titre, mot-clé, angle.', 'is_active' => 1],
            ['name' => 'Page Secteur', 'type' => 'secteur', 'plateforme' => 'google', 'template' => 'OBJECTIF : Rédiger une page secteur immobilière locale pour {{ville}}, utile pour les lecteurs humains et le SEO local Google. CONTRAINTES : ne jamais inventer de données, ne jamais donner de chiffres précis non confirmés, style naturel et professionnel, sans ton robotique ni bourrage SEO. Adapter le contenu à la ville, au secteur, à l’intention de page et aux données de recherche locales fournies. STRUCTURE OBLIGATOIRE : 1) Hero 2) Vue d’ensemble du secteur 3) Pourquoi ce secteur attire 4) Marché immobilier local 5) À qui s’adresse ce secteur 6) Vendre dans ce secteur 7) Acheter dans ce secteur 8) Regard d’expert 9) FAQ 10) CTA final.', 'is_active' => 1],
            ['name' => 'Post Facebook', 'type' => 'reseaux', 'plateforme' => 'facebook', 'template' => 'OBJECTIF : Créer un post Facebook engageant en storytelling, ton humain, question finale. Thème : {{type_contenu}} à {{ville}}.', 'is_active' => 1],
            ['name' => 'Script TikTok', 'type' => 'reseaux', 'plateforme' => 'tiktok', 'template' => 'OBJECTIF : Script TikTok immobilier avec hook < 3 sec, punch, fin ouverte. Sujet : {{type_contenu}}.', 'is_active' => 1],
            ['name' => 'Email vendeur', 'type' => 'email', 'plateforme' => 'linkedin', 'template' => 'OBJECTIF : Relancer un prospect vendeur à {{ville}} avec un email court, humain et non agressif.', 'is_active' => 1],
            ['name' => 'Google Business Post', 'type' => 'gmb', 'plateforme' => 'google', 'template' => 'OBJECTIF : Générer un post Google Business Profile local avec CTA discret autour de {{mot_cle}} à {{ville}}.', 'is_active' => 1],
            ['name' => 'Prompt image IA', 'type' => 'image', 'plateforme' => 'facebook', 'template' => 'OBJECTIF : Générer un prompt image immobilier (sujet, style, ambiance, angle) pour {{ville}}.', 'is_active' => 1],
            ['name' => 'Analyse mot-clé + GKR', 'type' => 'seo', 'plateforme' => 'google', 'template' => 'OBJECTIF : Analyser {{mot_cle}} : volume, concurrence, GKR, opportunité locale.', 'is_active' => 1],
            ['name' => 'Stratégie de contenu', 'type' => 'article', 'plateforme' => 'google', 'template' => 'OBJECTIF : Générer une stratégie complète (pilier, satellites, calendrier) selon {{objectif}} pour {{ville}}.', 'is_active' => 1],
        ];
    }
}

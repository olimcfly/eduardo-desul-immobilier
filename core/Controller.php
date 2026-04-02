<?php
// ============================================================
// CONTROLLER DE BASE
// ============================================================

abstract class Controller
{
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data);
        $viewFile   = ROOT . '/views/' . $view . '.php';
        $layoutFile = ROOT . '/views/layouts/' . $layout . '.php';

        if (!file_exists($viewFile)) {
            throw new RuntimeException("Vue introuvable : {$view}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout && file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    protected function renderPartial(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = ROOT . '/views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new RuntimeException("Vue introuvable : {$view}");
        }
        require $viewFile;
    }

    protected function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    protected function back(): never
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? APP_URL;
        $this->redirect($ref);
    }

    protected function requireAuth(): void
    {
        if (!isLoggedIn()) {
            flash('error', 'Vous devez être connecté.');
            $this->redirect(APP_URL . '/login');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireAuth();
        if (!isAdmin()) {
            flash('error', 'Accès réservé aux administrateurs.');
            $this->redirect(APP_URL);
        }
    }

    protected function input(string $key, mixed $default = ''): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function validate(array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $rule) {
            $value = $this->input($field);
            if (str_contains($rule, 'required') && empty($value)) {
                $errors[$field] = "Le champ {$field} est obligatoire.";
            }
            if (str_contains($rule, 'email') && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = "L'email est invalide.";
            }
            if (preg_match('/min:(\d+)/', $rule, $m) && mb_strlen($value) < (int)$m[1]) {
                $errors[$field] = "Le champ {$field} doit contenir au moins {$m[1]} caractères.";
            }
            if (preg_match('/max:(\d+)/', $rule, $m) && mb_strlen($value) > (int)$m[1]) {
                $errors[$field] = "Le champ {$field} ne doit pas dépasser {$m[1]} caractères.";
            }
        }
        return $errors;
    }
}

<?php

class Router
{
    private array $routes = [];
    private array $namedRoutes = [];
    private ?\Closure $handler404 = null;

    public function get(string $path, callable|array|string $handler, string $name = ''): void
    {
        $this->add('GET', $path, $handler, $name);
    }

    public function post(string $path, callable|array|string $handler, string $name = ''): void
    {
        $this->add('POST', $path, $handler, $name);
    }

    public function add(string $method, string $path, callable|array|string $handler, string $name = ''): void
    {
        $pattern = preg_replace('/\{([a-z_]+)\}/', '([^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';
        $this->routes[] = [
            'method'  => strtoupper($method),
            'path'    => $path,
            'pattern' => $pattern,
            'handler' => $this->resolveHandler($handler),
        ];
        if ($name) {
            $this->namedRoutes[$name] = $path;
        }
    }

    private function resolveHandler(callable|array|string $handler): array|callable
    {
        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            if (!class_exists($class)) {
                throw new \RuntimeException("Contrôleur {$class} introuvable");
            }
            return [$class, $method];
        }
        return $handler;
    }

    public function set404(callable $handler): void
    {
        $this->handler404 = \Closure::fromCallable($handler);
    }

    public function route(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \RuntimeException("Route inconnue : {$name}");
        }
        $url = $this->namedRoutes[$name];
        foreach ($params as $key => $val) {
            $url = str_replace('{' . $key . '}', $val, $url);
        }
        return APP_URL . $url;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $base   = parse_url(APP_URL, PHP_URL_PATH) ?? '';
        if ($base && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        $uri = '/' . ltrim($uri, '/');
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            if (!preg_match($route['pattern'], $uri, $matches)) continue;

            array_shift($matches);
            $handler = $route['handler'];

            if (is_array($handler)) {
                [$class, $method_name] = $handler;
                $controller = new $class();
                call_user_func_array([$controller, $method_name], $matches);
            } else {
                call_user_func_array($handler, $matches);
            }
            return;
        }

        $this->notFound();
    }

    private function notFound(): void
    {
        http_response_code(404);
        if ($this->handler404) {
            ($this->handler404)();
            return;
        }
        echo '<h1>404 — Page introuvable</h1>';
    }
}

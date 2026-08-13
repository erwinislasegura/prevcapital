<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $action, array $middleware = []): void
    {
        $this->add('GET', $path, $action, $middleware);
    }

    public function post(string $path, array $action, array $middleware = []): void
    {
        $this->add('POST', $path, $action, $middleware);
    }

    private function add(string $method, string $path, array $action, array $middleware): void
    {
        $this->routes[$method][$this->normalize($path)] = compact('action', 'middleware');
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $base = base_url_path();
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base)) ?: '/';
        }
        $path = $this->normalize($path);
        $route = $this->routes[$method][$path] ?? null;

        if (!$route) {
            http_response_code(404);
            View::render('errors/404', [], 'public');
            return;
        }

        foreach ($route['middleware'] as $middleware) {
            if ($middleware === 'auth' && !Auth::check()) {
                flash('error', 'Inicie sesión para continuar.');
                redirect('/login');
            }
            if (str_starts_with($middleware, 'permission:')) {
                $permission = substr($middleware, 11);
                if (!Auth::can($permission)) {
                    http_response_code(403);
                    View::render('errors/403', [], 'admin');
                    return;
                }
            }
        }

        [$controller, $action] = $route['action'];
        (new $controller())->{$action}();
    }

    private function normalize(string $path): string
    {
        $normalized = '/' . trim($path, '/');
        return $normalized === '//' ? '/' : $normalized;
    }
}

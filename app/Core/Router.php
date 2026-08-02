<?php

namespace App\Core;

class Router
{
    public function __construct(private array $routes, private array $config)
    {
        require_once BASE_PATH . '/app/Core/helpers.php';
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $base = parse_url($this->config['base_url'], PHP_URL_PATH) ?: '';
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base)) ?: '/';
        }
        $path = '/' . trim($path, '/');
        $path = $path === '/' ? '/' : rtrim($path, '/');

        foreach ($this->routes as [$routeMethod, $pattern, $handler]) {
            if ($routeMethod !== $method) {
                continue;
            }

            $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '([^/]+)', $pattern);
            if (preg_match('#^' . $regex . '$#', $path, $matches)) {
                array_shift($matches);
                [$class, $action] = $handler;
                $controller = new $class($this->config);
                $controller->$action(...$matches);
                return;
            }
        }

        http_response_code(404);
        (new Controller($this->config))->view('errors/404', ['title' => 'Pagina nao encontrada']);
    }
}

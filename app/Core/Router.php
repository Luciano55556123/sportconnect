<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    public function __construct(
        private array $routes,
        private array $config
    ) {
        require_once BASE_PATH . '/app/Core/helpers.php';
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $baseUrl = $this->config['base_url'] ?? '';
        $base = parse_url($baseUrl, PHP_URL_PATH) ?: '';

        $path = rawurldecode($path);
        $base = rawurldecode($base);

        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        // Remove index.php da rota.
        if (str_starts_with($path, '/index.php')) {
            $path = substr($path, strlen('/index.php'));
        }

        $path = '/' . trim($path, '/');

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        foreach ($this->routes as [$routeMethod, $pattern, $handler]) {
            if (strtoupper($routeMethod) !== strtoupper($method)) {
                continue;
            }

            $parameterNames = [];

            $regex = preg_replace_callback(
                '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
                function (array $matches) use (&$parameterNames): string {
                    $parameterNames[] = $matches[1];
                    return '([^/]+)';
                },
                $pattern
            );

            if (
                $regex !== null
                && preg_match('#^' . $regex . '$#', $path, $matches)
            ) {
                array_shift($matches);

                $matches = array_map(
                    static fn(string $value): string => rawurldecode($value),
                    $matches
                );

                [$controllerClass, $action] = $handler;

                $controller = new $controllerClass($this->config);
                $controller->$action(...$matches);

                return;
            }
        }

        http_response_code(404);

        (new Controller($this->config))->view(
            'errors/404',
            ['title' => 'Página não encontrada']
        );
    }
}
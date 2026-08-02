<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (str_starts_with($class, $prefix)) {
        $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix)));
        $file = BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . $relative . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

$config = require BASE_PATH . '/config/app.php';
$GLOBALS['app_config'] = $config;
require_once BASE_PATH . '/app/Core/helpers.php';
App\Core\Security::bootstrap($config);

$routes = require BASE_PATH . '/routes/web.php';

$router = new App\Core\Router($routes, $config);
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

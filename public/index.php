<?php

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

define('BASE_PATH', dirname(__DIR__));

$composerAutoload = BASE_PATH . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require_once $composerAutoload;
}

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));

    $relative = str_replace(
        '\\',
        DIRECTORY_SEPARATOR,
        $relative
    );

    $file = BASE_PATH
        . DIRECTORY_SEPARATOR
        . 'app'
        . DIRECTORY_SEPARATOR
        . $relative
        . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

$config = require BASE_PATH . '/config/app.php';
$routes = require BASE_PATH . '/routes/web.php';

$router = new App\Core\Router($routes, $config);

$router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/'
);

<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/env.php';

$debug = filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('display_startup_errors', $debug ? '1' : '0');
error_reporting(E_ALL);

$sessionPath = BASE_PATH . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0775, true);
}
if (is_dir($sessionPath) && is_writable($sessionPath)) {
    session_save_path($sessionPath);
}

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

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

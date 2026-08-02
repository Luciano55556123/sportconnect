<?php

require_once __DIR__ . '/env.php';

$env = static function (string $key, ?string $default = null): ?string {
    $value = getenv($key);
    return $value === false ? $default : $value;
};

return [
    'name' => $env('APP_NAME', 'SportConnect'),
    'env' => $env('APP_ENV', 'local'),
    'debug' => filter_var($env('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOLEAN),
    'base_url' => rtrim((string) $env('APP_URL', ''), '/'),
    'trusted_proxies' => $env('TRUSTED_PROXIES', ''),
    'health_token' => $env('HEALTH_TOKEN', ''),
    'database' => [
        'driver' => $env('DB_DRIVER', 'pgsql'),
        'host' => $env('DB_HOST', ''),
        'port' => $env('DB_PORT', '5432'),
        'name' => $env('DB_NAME', ''),
        'user' => $env('DB_USER', ''),
        'password' => $env('DB_PASSWORD', ''),
        'sslmode' => $env('DB_SSLMODE', 'require'),
    ],
];

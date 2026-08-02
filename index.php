<?php

declare(strict_types=1);

$uri = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
) ?: '/';

if (
    $uri === '/index.php'
    || $uri === '/public'
    || $uri === '/public/'
    || $uri === '/public/index.php'
) {
    $uri = '/';
}

$_SERVER['REQUEST_URI'] = $uri;
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

require __DIR__ . '/public/index.php';
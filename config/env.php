<?php

declare(strict_types=1);

$envPath = dirname(__DIR__) . '/.env';

if (!is_file($envPath) || !is_readable($envPath)) {
    return;
}

$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {
    $line = trim($line);

    if ($line === '' || str_starts_with($line, '#')) {
        continue;
    }

    $separator = strpos($line, '=');
    if ($separator === false) {
        continue;
    }

    $key = trim(substr($line, 0, $separator));
    $value = trim(substr($line, $separator + 1));

    if ($key === '') {
        continue;
    }

    if (
        strlen($value) >= 2
        && (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))
    ) {
        $value = substr($value, 1, -1);
    }

    putenv($key . '=' . $value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

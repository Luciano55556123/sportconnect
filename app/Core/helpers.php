<?php

use App\Core\Auth;

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $config = require BASE_PATH . '/config/app.php';
    $base = rtrim($config['base_url'], '/');
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host !== '' && preg_match('/^(localhost|127\.0\.0\.1|\[::1\])(:\d+)?$/', $host)) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $base = $scheme . '://' . $host;
    }
    if ($base === '') {
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $base = $scriptDir === '/' ? '' : $scriptDir;
    }
    return $base . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function versioned_asset(string $path): string
{
    $relative = ltrim($path, '/');
    $file = BASE_PATH . '/public/assets/' . $relative;
    $version = is_file($file) ? (string) filemtime($file) : (string) time();

    return asset($relative) . '?v=' . $version;
}

function whatsapp_number(?string $value): string
{
    $number = preg_replace('/\D/', '', (string) $value);
    if ($number === '') {
        return '';
    }

    if (str_starts_with($number, '00')) {
        $number = substr($number, 2);
    }

    if (!str_starts_with($number, '55') && strlen($number) <= 11) {
        $number = '55' . $number;
    }

    return $number;
}

function whatsapp_url(?string $phone, string $message): ?string
{
    $number = whatsapp_number($phone);
    if ($number === '') {
        return null;
    }

    return 'https://wa.me/' . $number . '?text=' . rawurlencode($message);
}

function money_br(float $amount): string
{
    return 'R$ ' . number_format($amount, 2, ',', '.');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['_csrf'] ?? '';
        if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
            http_response_code(419);
            exit('Sessao expirada. Recarregue a pagina.');
        }
    }
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

function is_role(string $role): bool
{
    return Auth::check() && Auth::user()['role'] === $role;
}

<?php

use App\Core\Auth;

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $config = $GLOBALS['app_config'] ?? require BASE_PATH . '/config/app.php';
    $base = rtrim($config['base_url'], '/');
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

function championship_image_url(array $championship): string
{
    $path = $championship['imagem'] ?? null;
    if (!$path || $path === 'assets/img/default-event.svg') {
        $path = $championship['image'] ?? null;
    }
    if (!$path || $path === 'assets/img/default-event.svg' || !preg_match('/^[a-z0-9_\/.-]+$/i', $path)) {
        $path = 'assets/images/campeonato-placeholder.jpg';
    }

    return url($path);
}

function sport_slug(string $name): string
{
    $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: $name;
    $slug = strtolower(trim((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $normalized), '-'));
    return $slug !== '' ? $slug : 'default-sport';
}

function sport_image_url(array $sport): string
{
    foreach (['image', 'imagem', 'cover_image', 'banner'] as $field) {
        $path = $sport[$field] ?? '';
        if (is_string($path) && preg_match('/^assets\/images\/sports\/[a-z0-9\/._-]+\.(jpg|jpeg|png|webp|svg)$/i', $path)) {
            return url($path);
        }
    }

    $slug = sport_slug((string) ($sport['slug'] ?? $sport['name'] ?? ''));
    foreach (['jpg', 'jpeg', 'webp', 'png', 'svg'] as $extension) {
        $candidate = 'assets/images/sports/' . $slug . '.' . $extension;
        if (is_file(BASE_PATH . '/public/' . $candidate)) {
            return url($candidate);
        }
    }

    return url('assets/images/sports/default-sport.svg');
}

function team_shield_url(array $team): string
{
    foreach (['shield', 'shield_image', 'logo', 'image', 'imagem'] as $field) {
        $path = $team[$field] ?? '';
        if (is_string($path) && preg_match('/^assets\/images\/teams\/[a-z0-9\/._-]+\.(jpg|jpeg|png|webp|svg)$/i', $path)) {
            return url($path);
        }
        if (is_string($path) && preg_match('/^uploads\/teams\/[a-z0-9\/._-]+\.(jpg|jpeg|png|webp)$/i', $path) && is_file(BASE_PATH . '/public/' . $path)) {
            return url($path);
        }
    }

    return url('assets/images/team-shield.svg');
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
            error_log('csrf_invalid path=' . ($_SERVER['REQUEST_URI'] ?? '') . ' ip=' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            \App\Core\Security::abort(419, 'Sessao expirada. Recarregue a pagina e tente novamente.');
        }
    }
}

function is_production(): bool
{
    return ($GLOBALS['app_config']['env'] ?? 'local') === 'production';
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

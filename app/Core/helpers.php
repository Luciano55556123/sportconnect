<?php

use App\Core\Auth;

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

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

function championship_is_demo(array $c): bool
{
    return in_array($c['is_demo'] ?? false, [true, 1, '1', 't', 'true'], true);
}

function championship_is_public(array $c): bool
{
    return in_array($c['editorial_status'] ?? '', ['published', 'registration_open'], true);
}

function championship_can_view(array $c): bool
{
    if (championship_is_public($c)) return true;
    $user = Auth::user();
    return $user && (($user['role'] ?? '') === 'admin' || (int) ($user['id'] ?? 0) === (int) ($c['organizer_id'] ?? -1));
}

function championship_registration_open(array $c): bool
{
    return championship_is_public($c) && in_array($c['status'] ?? '', ['ativo', 'registration_open', 'published'], true)
        && in_array($c['registrations_open'] ?? false, [true, 1, '1', 't', 'true', 'yes', 'on'], true)
        && !empty($c['event_date']) && $c['event_date'] >= date('Y-m-d')
        && (empty($c['registration_deadline']) || $c['registration_deadline'] >= date('Y-m-d'));
}

function championship_status_label(array $c): string
{
    if (!championship_is_public($c)) return ['draft'=>'Rascunho', 'pending_review'=>'Aguardando aprovação', 'rejected'=>'Rejeitado', 'suspended'=>'Suspenso', 'cancelled'=>'Cancelado'][$c['editorial_status'] ?? ''] ?? 'Não publicado';
    if (championship_registration_open($c)) return 'Inscrições abertas';
    return ['ativo'=>'Inscrições encerradas', 'registration_open'=>'Inscrições encerradas',
        'published'=>'Inscrições encerradas', 'registration_closed'=>'Inscrições encerradas',
        'in_progress'=>'Em andamento', 'em_andamento'=>'Em andamento', 'encerrado'=>'Encerrado',
        'finished'=>'Encerrado', 'cancelado'=>'Cancelado', 'cancelled'=>'Cancelado',
        'draft'=>'Rascunho', 'pending_review'=>'Aguardando aprovação', 'rejected'=>'Rejeitado',
        'suspended'=>'Suspenso'][$c['status'] ?? ''] ?? 'Inscrições encerradas';
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
        $token = $_POST['_csrf'] ?? null;
        $expected = $_SESSION['csrf'] ?? '';
        if (!is_string($token) || !is_string($expected) || $expected === ''
            || !hash_equals($expected, $token)) {
            \App\Core\Security::abort(419, 'Sua sessao expirou. Recarregue a pagina e tente novamente.');
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

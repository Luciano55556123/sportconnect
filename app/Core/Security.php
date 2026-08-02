<?php

namespace App\Core;

final class Security
{
    private const INACTIVITY_SECONDS = 1800;
    private const ABSOLUTE_SECONDS = 28800;

    public static function bootstrap(array $config): void
    {
        self::configureErrors($config);
        self::registerErrorHandlers($config);
        self::enforceHttps($config);
        self::sendHeaders($config);
        self::configureSession($config);
        session_start();
        self::expireSession();
    }

    public static function env(array $config): string
    {
        return (string) ($config['env'] ?? 'local');
    }

    public static function isProduction(array $config): bool
    {
        return self::env($config) === 'production';
    }

    public static function isDebug(array $config): bool
    {
        return !empty($config['debug']);
    }

    public static function isHttps(array $config): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        $remote = $_SERVER['REMOTE_ADDR'] ?? '';
        $trusted = array_filter(array_map('trim', explode(',', (string) ($config['trusted_proxies'] ?? ''))));
        if ($remote !== '' && in_array($remote, $trusted, true)) {
            return strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
        }

        return false;
    }

    public static function abort(int $status, string $message = ''): never
    {
        http_response_code($status);
        $titles = [
            403 => 'Acesso negado',
            404 => 'Pagina nao encontrada',
            419 => 'Sessao expirada',
            429 => 'Muitas tentativas',
            500 => 'Erro interno',
            503 => 'Manutencao',
        ];
        $title = $titles[$status] ?? 'Erro';
        $safeMessage = $message !== '' ? $message : 'Nao foi possivel concluir a solicitacao.';
        require BASE_PATH . '/app/Views/errors/simple.php';
        exit;
    }

    public static function rateLimit(string $action, int $limit, int $seconds): void
    {
        $now = time();
        $user = $_SESSION['user']['id'] ?? 'guest';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = hash('sha256', $action . '|' . $user . '|' . $ip);
        $_SESSION['rate_limits'][$key] = array_values(array_filter(
            $_SESSION['rate_limits'][$key] ?? [],
            static fn (int $time): bool => $time > ($now - $seconds)
        ));

        if (count($_SESSION['rate_limits'][$key]) >= $limit) {
            error_log('rate_limit action=' . $action . ' user=' . $user . ' ip=' . $ip);
            self::abort(429, 'Aguarde alguns minutos antes de tentar novamente.');
        }

        $_SESSION['rate_limits'][$key][] = $now;
    }

    private static function configureErrors(array $config): void
    {
        error_reporting(E_ALL);
        ini_set('log_errors', '1');
        ini_set('display_errors', self::isDebug($config) ? '1' : '0');
        ini_set('display_startup_errors', self::isDebug($config) ? '1' : '0');
    }

    private static function registerErrorHandlers(array $config): void
    {
        set_exception_handler(static function (\Throwable $exception) use ($config): void {
            error_log($exception);
            if (self::isDebug($config)) {
                self::abort(500, $exception->getMessage());
            }
            self::abort(500, 'Encontramos uma instabilidade. Tente novamente em instantes.');
        });

        set_error_handler(static function (int $severity, string $message, string $file, int $line) use ($config): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            error_log('php_error severity=' . $severity . ' message=' . $message . ' file=' . $file . ' line=' . $line);
            return self::isProduction($config);
        });
    }

    private static function enforceHttps(array $config): void
    {
        if (!self::isProduction($config) || self::isHttps($config)) {
            return;
        }

        $host = $_SERVER['HTTP_HOST'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        if ($host !== '' && !str_contains($host, 'localhost') && !str_starts_with($host, '127.')) {
            header('Location: https://' . $host . $uri, true, 301);
            exit;
        }
    }

    private static function sendHeaders(array $config): void
    {
        if (headers_sent()) {
            return;
        }

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https:; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; script-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com data:; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");

        if (self::isProduction($config) && self::isHttps($config)) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    private static function configureSession(array $config): void
    {
        $sessionPath = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'sportconnect_sessions';
        if (!is_dir($sessionPath)) {
            mkdir($sessionPath, 0755, true);
        }
        if (is_dir($sessionPath)) {
            session_save_path($sessionPath);
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.sid_length', '48');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => self::isProduction($config) && self::isHttps($config),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function expireSession(): void
    {
        $now = time();
        $_SESSION['created_at'] ??= $now;
        $_SESSION['last_activity_at'] ??= $now;

        if (($now - (int) $_SESSION['last_activity_at']) > self::INACTIVITY_SECONDS || ($now - (int) $_SESSION['created_at']) > self::ABSOLUTE_SECONDS) {
            Auth::logout();
            session_start();
            $_SESSION['flash']['error'] = 'Sua sessao expirou. Entre novamente.';
        }

        $_SESSION['last_activity_at'] = $now;
    }
}

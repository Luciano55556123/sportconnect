<?php

namespace App\Core;

class Auth
{
    public static function user(): ?array
    {
        if (!isset($_SESSION['user'])) {
            return null;
        }

        $stmt = Database::connection()->prepare('SELECT id, name, email, role, city FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user']['id']]);
        $user = $stmt->fetch();

        if (!$user) {
            self::logout();
            return null;
        }

        self::storeUser($user);
        return $_SESSION['user'];
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        self::storeUser($user);
    }

    public static function refresh(array $user): void
    {
        self::storeUser($user);
    }

    private static function storeUser(array $user): void
    {
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'city' => $user['city'] ?? '',
        ];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'] ?? '/',
                'domain' => $params['domain'] ?? '',
                'secure' => (bool) ($params['secure'] ?? false),
                'httponly' => true,
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
    }
}

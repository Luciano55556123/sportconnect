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

        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'city' => $user['city'] ?? '',
        ];

        return $_SESSION['user'];
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
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
        session_destroy();
    }
}

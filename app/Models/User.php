<?php

namespace App\Models;

class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password, role, phone, city, birth_date, preferred_price_max)
             VALUES (:name, :email, :password, :role, :phone, :city, :birth_date, :preferred_price_max)'
        );
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => 'athlete',
            'phone' => $data['phone'] ?? null,
            'city' => $data['city'] ?? null,
            'birth_date' => $data['birth_date'] ?: null,
            'preferred_price_max' => $data['preferred_price_max'] ?: null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateProfile(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET name = ?, phone = ?, city = ?, birth_date = ?, preferred_price_max = ? WHERE id = ?'
        );
        $stmt->execute([
            $data['name'],
            $data['phone'] ?? null,
            $data['city'] ?? null,
            $data['birth_date'] ?: null,
            $data['preferred_price_max'] ?: null,
            $id,
        ]);
    }

    public function syncFavoriteSports(int $userId, array $sportIds): void
    {
        $this->db->prepare('DELETE FROM user_sports WHERE user_id = ?')->execute([$userId]);
        $stmt = $this->db->prepare('INSERT INTO user_sports (user_id, sport_id) VALUES (?, ?)');
        foreach ($sportIds as $sportId) {
            $stmt->execute([$userId, (int) $sportId]);
        }
    }

    public function favoriteSportIds(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT sport_id FROM user_sports WHERE user_id = ?');
        $stmt->execute([$userId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'sport_id'));
    }

    public function all(string $role = ''): array
    {
        if ($role !== '') {
            $stmt = $this->db->prepare('SELECT * FROM users WHERE role = ? ORDER BY name');
            $stmt->execute([$role]);
            return $stmt->fetchAll();
        }
        return $this->db->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
    }

    public function admins(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE role = 'admin' ORDER BY name");
        $stmt->execute();

        return $stmt->fetchAll();
    }
}

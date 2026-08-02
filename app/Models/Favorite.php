<?php

namespace App\Models;

class Favorite extends Model
{
    public function toggle(int $userId, int $championshipId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM favorites WHERE user_id = ? AND championship_id = ?');
        $stmt->execute([$userId, $championshipId]);
        $id = $stmt->fetchColumn();
        if ($id) {
            $this->db->prepare('DELETE FROM favorites WHERE id = ?')->execute([$id]);
            return false;
        }
        $this->db->prepare('INSERT INTO favorites (user_id, championship_id) VALUES (?, ?)')->execute([$userId, $championshipId]);
        return true;
    }

    public function byUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, s.name AS sport_name FROM favorites f
             JOIN championships c ON c.id = f.championship_id
             JOIN sports s ON s.id = c.sport_id
             WHERE f.user_id = ? ORDER BY f.created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}

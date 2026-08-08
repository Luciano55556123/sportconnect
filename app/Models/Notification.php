<?php

namespace App\Models;

class Notification extends Model
{
    public function forUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function createForFavoriteSport(int $sportId, string $message): void
    {
        $stmt = $this->db->prepare('SELECT user_id FROM user_sports WHERE sport_id = ?');
        $stmt->execute([$sportId]);
        $insert = $this->db->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)');
        foreach ($stmt->fetchAll() as $row) {
            $insert->execute([(int) $row['user_id'], $message]);
        }
    }

    public function create(int $userId, string $message): void
    {
        $stmt = $this->db->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)');
        $stmt->execute([$userId, $message]);
    }
}

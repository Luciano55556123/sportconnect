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

    public function unreadCount(int $userId): int
    {
        try {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND COALESCE(is_read, false) = false AND read_at IS NULL');
            $stmt->execute([$userId]);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable) {
            return 0;
        }
    }

    public function create(int $userId, string $title, string $message, ?string $link = null, string $type = 'info'): void
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO notifications (user_id, title, message, link, type) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$userId, $title, $message, $link, $type]);
        } catch (\Throwable) {
            $stmt = $this->db->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)');
            $stmt->execute([$userId, $message]);
        }
    }

    public function markAsRead(int $id, int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE notifications SET is_read = true, read_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
    }

    public function createForFavoriteSport(int $sportId, string $message): void
    {
        $stmt = $this->db->prepare('SELECT user_id FROM user_sports WHERE sport_id = ?');
        $stmt->execute([$sportId]);
        foreach ($stmt->fetchAll() as $row) {
            $this->create((int) $row['user_id'], 'Novo campeonato', $message, null, 'championship');
        }
    }
}

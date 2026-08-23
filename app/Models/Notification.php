<?php

namespace App\Models;

use PDO;

class Notification extends Model
{
    private ?array $columns = null;

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
        foreach ($stmt->fetchAll() as $row) {
            $this->create((int) $row['user_id'], $message);
        }
    }

    public function create(int $userId, string $message, string $title = '', string $link = '', string $type = 'info'): void
    {
        $fields = [
            'user_id' => $userId,
            'message' => $message,
        ];

        if ($title !== '' && $this->hasColumn('title')) {
            $fields['title'] = $title;
        }

        if ($link !== '' && $this->hasColumn('link')) {
            $fields['link'] = $link;
        }

        if ($this->hasColumn('type')) {
            $fields['type'] = $type;
        }

        if ($this->hasColumn('is_read')) {
            $fields['is_read'] = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql' ? 'false' : 0;
        }

        $columns = array_keys($fields);
        $placeholders = array_map(static fn(string $column): string => ':' . $column, $columns);
        $stmt = $this->db->prepare(
            'INSERT INTO notifications (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')'
        );
        $stmt->execute($fields);
    }

    public function createForAdmins(string $message, string $title = '', string $link = '', string $type = 'admin'): void
    {
        $admins = (new User())->admins();
        foreach ($admins as $admin) {
            $this->create((int) $admin['id'], $message, $title, $link, $type);
        }
    }

    private function hasColumn(string $column): bool
    {
        return in_array($column, $this->columns(), true);
    }

    private function columns(): array
    {
        if ($this->columns !== null) {
            return $this->columns;
        }

        $this->columns = [];

        if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            $stmt = $this->db->prepare(
                "SELECT column_name
                 FROM information_schema.columns
                 WHERE table_name = 'notifications'
                 AND table_schema = ANY (current_schemas(false))"
            );
            $stmt->execute();
            $this->columns = array_column($stmt->fetchAll(), 'column_name');
        }

        if ($this->columns === []) {
            $stmt = $this->db->prepare(
                "SELECT column_name
                 FROM information_schema.columns
                 WHERE table_name = 'notifications'"
            );
            $stmt->execute();
            $this->columns = array_column($stmt->fetchAll(), 'column_name');
        }

        return $this->columns;
    }
}

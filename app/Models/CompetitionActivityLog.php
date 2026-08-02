<?php

namespace App\Models;

class CompetitionActivityLog extends Model
{
    public function create(int $championshipId, ?int $userId, string $action, string $description): void
    {
        if ($championshipId <= 0) {
            return;
        }
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO competition_activity_logs (championship_id, user_id, action, description)
                 VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$championshipId, $userId, $action, $description]);
        } catch (\Throwable $exception) {
            error_log($exception->getMessage());
        }
    }

    public function byChampionship(int $championshipId, int $limit = 12): array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT l.*, u.name AS user_name
                 FROM competition_activity_logs l
                 LEFT JOIN users u ON u.id = l.user_id
                 WHERE l.championship_id = ?
                 ORDER BY l.created_at DESC
                 LIMIT ' . (int) $limit
            );
            $stmt->execute([$championshipId]);
            return $stmt->fetchAll();
        } catch (\Throwable $exception) {
            error_log($exception->getMessage());
            return [];
        }
    }
}

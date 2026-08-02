<?php

namespace App\Models;

class Team extends Model
{
    public function byChampionship(int $championshipId): array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*, COUNT(a.id) AS athletes_count
             FROM teams t
             LEFT JOIN athletes a ON a.team_id = t.id
             WHERE t.championship_id = ?
             GROUP BY t.id
             ORDER BY t.name'
        );
        $stmt->execute([$championshipId]);
        return $stmt->fetchAll();
    }

    public function findInChampionship(int $id, int $championshipId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM teams WHERE id = ? AND championship_id = ?');
        $stmt->execute([$id, $championshipId]);
        return $stmt->fetch() ?: null;
    }

    public function save(array $data, int $championshipId): int
    {
        if (!empty($data['id'])) {
            $stmt = $this->db->prepare(
                'UPDATE teams
                 SET name = :name, city = :city, responsible_name = :responsible_name,
                     responsible_phone = :responsible_phone, status = :status, updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id AND championship_id = :championship_id
                 RETURNING id'
            );
            $stmt->execute($this->payload($data, $championshipId) + ['id' => (int) $data['id']]);
            return (int) $stmt->fetchColumn();
        }

        $stmt = $this->db->prepare(
            'INSERT INTO teams (championship_id, name, city, responsible_name, responsible_phone, status)
             VALUES (:championship_id, :name, :city, :responsible_name, :responsible_phone, :status)
             RETURNING id'
        );
        $stmt->execute($this->payload($data, $championshipId));
        return (int) $stmt->fetchColumn();
    }

    public function delete(int $id, int $championshipId): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM teams
             WHERE id = ? AND championship_id = ?
             AND NOT EXISTS (SELECT 1 FROM matches m WHERE m.home_team_id = teams.id OR m.away_team_id = teams.id)'
        );
        $stmt->execute([$id, $championshipId]);
    }

    private function payload(array $data, int $championshipId): array
    {
        return [
            'championship_id' => $championshipId,
            'name' => trim($data['name'] ?? ''),
            'city' => trim($data['city'] ?? '') ?: null,
            'responsible_name' => trim($data['responsible_name'] ?? '') ?: null,
            'responsible_phone' => trim($data['responsible_phone'] ?? '') ?: null,
            'status' => in_array($data['status'] ?? '', ['pendente', 'aprovado', 'rejeitado', 'cancelado'], true) ? $data['status'] : 'aprovado',
        ];
    }
}

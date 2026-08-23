<?php

namespace App\Models;

class Athlete extends Model
{
    public function byChampionship(int $championshipId): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, t.name AS team_name
             FROM athletes a
             LEFT JOIN teams t ON t.id = a.team_id
             WHERE a.championship_id = ?
             ORDER BY a.name'
        );
        $stmt->execute([$championshipId]);
        return $stmt->fetchAll();
    }

    public function byTeam(int $teamId, int $championshipId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM athletes WHERE team_id = ? AND championship_id = ? ORDER BY shirt_number NULLS LAST, name'
        );
        $stmt->execute([$teamId, $championshipId]);
        return $stmt->fetchAll();
    }

    public function findInChampionship(int $id, int $championshipId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, t.name AS team_name
             FROM athletes a
             LEFT JOIN teams t ON t.id = a.team_id
             WHERE a.id = ? AND a.championship_id = ?'
        );
        $stmt->execute([$id, $championshipId]);
        return $stmt->fetch() ?: null;
    }

    public function save(array $data, int $championshipId): int
    {
        if (!empty($data['id'])) {
            $stmt = $this->db->prepare(
                'UPDATE athletes
                 SET team_id = :team_id, name = :name, birth_date = :birth_date, city = :city,
                     shirt_number = :shirt_number, position = :position, category = :category,
                     photo = :photo, status = :status, updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id AND championship_id = :championship_id
                 RETURNING id'
            );
            $stmt->execute($this->payload($data, $championshipId) + ['id' => (int) $data['id']]);
            return (int) $stmt->fetchColumn();
        }

        $stmt = $this->db->prepare(
            'INSERT INTO athletes (championship_id, team_id, name, birth_date, city, shirt_number, position, category, photo, status)
             VALUES (:championship_id, :team_id, :name, :birth_date, :city, :shirt_number, :position, :category, :photo, :status)
             RETURNING id'
        );
        $stmt->execute($this->payload($data, $championshipId));
        return (int) $stmt->fetchColumn();
    }

    public function delete(int $id, int $championshipId): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM athletes
             WHERE id = ? AND championship_id = ?
             AND NOT EXISTS (
                 SELECT 1 FROM matches m
                 WHERE m.home_athlete_id = athletes.id OR m.away_athlete_id = athletes.id
             )'
        );
        $stmt->execute([$id, $championshipId]);
    }

    private function payload(array $data, int $championshipId): array
    {
        $name = trim($data['name'] ?? '');
        if ($name === '') {
            throw new \InvalidArgumentException('Informe o nome do atleta.');
        }

        $teamId = !empty($data['team_id']) ? (int) $data['team_id'] : null;
        if ($teamId && !(new Team())->findInChampionship($teamId, $championshipId)) {
            throw new \InvalidArgumentException('Equipe invalida para este campeonato.');
        }

        return [
            'championship_id' => $championshipId,
            'team_id' => $teamId,
            'name' => $name,
            'birth_date' => ($data['birth_date'] ?? '') !== '' ? $data['birth_date'] : null,
            'city' => trim($data['city'] ?? '') ?: null,
            'shirt_number' => ($data['shirt_number'] ?? '') !== '' ? (int) $data['shirt_number'] : null,
            'position' => trim($data['position'] ?? '') ?: null,
            'category' => trim($data['category'] ?? '') ?: null,
            'photo' => trim($data['photo'] ?? '') ?: null,
            'status' => in_array($data['status'] ?? '', ['pendente', 'aprovado', 'rejeitado', 'cancelado'], true) ? $data['status'] : 'aprovado',
        ];
    }
}

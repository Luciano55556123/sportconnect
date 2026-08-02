<?php

namespace App\Models;

class MatchEvent extends Model
{
    public function byChampionship(int $championshipId): array
    {
        $stmt = $this->db->prepare(
            'SELECT e.*, m.championship_id, a.name AS athlete_name, t.name AS team_name
             FROM match_events e
             JOIN matches m ON m.id = e.match_id
             LEFT JOIN athletes a ON a.id = e.athlete_id
             LEFT JOIN teams t ON t.id = e.team_id
             WHERE m.championship_id = ?
             ORDER BY e.minute NULLS LAST, e.additional_time NULLS LAST, e.id'
        );
        $stmt->execute([$championshipId]);
        $events = [];
        foreach ($stmt->fetchAll() as $event) {
            $events[(int) $event['match_id']][] = $event;
        }
        return $events;
    }

    public function save(array $data, int $championshipId): void
    {
        if (!empty($data['id'])) {
            $stmt = $this->db->prepare(
                'UPDATE match_events e
                 SET team_id = :team_id, athlete_id = :athlete_id, event_type = :event_type,
                     minute = :minute, additional_time = :additional_time, value = :value, description = :description
                 FROM matches m
                 WHERE m.id = e.match_id AND e.id = :id AND m.championship_id = :championship_id'
            );
            $stmt->execute([
                'id' => (int) $data['id'],
                'team_id' => !empty($data['team_id']) ? (int) $data['team_id'] : null,
                'athlete_id' => !empty($data['athlete_id']) ? (int) $data['athlete_id'] : null,
                'event_type' => $data['event_type'] ?? 'observacao',
                'minute' => ($data['minute'] ?? '') !== '' ? (int) $data['minute'] : null,
                'additional_time' => ($data['additional_time'] ?? '') !== '' ? (int) $data['additional_time'] : null,
                'value' => ($data['value'] ?? '') !== '' ? (int) $data['value'] : null,
                'description' => trim($data['description'] ?? '') ?: null,
                'championship_id' => $championshipId,
            ]);
            (new AthleteStatistic())->recalculate($championshipId);
            return;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO match_events (match_id, team_id, athlete_id, event_type, minute, additional_time, value, description)
             SELECT :match_id, :team_id, :athlete_id, :event_type, :minute, :additional_time, :value, :description
             WHERE EXISTS (SELECT 1 FROM matches WHERE id = :match_id AND championship_id = :championship_id)'
        );
        $stmt->execute([
            'match_id' => (int) $data['match_id'],
            'team_id' => !empty($data['team_id']) ? (int) $data['team_id'] : null,
            'athlete_id' => !empty($data['athlete_id']) ? (int) $data['athlete_id'] : null,
            'event_type' => $data['event_type'] ?? 'observacao',
            'minute' => ($data['minute'] ?? '') !== '' ? (int) $data['minute'] : null,
            'additional_time' => ($data['additional_time'] ?? '') !== '' ? (int) $data['additional_time'] : null,
            'value' => ($data['value'] ?? '') !== '' ? (int) $data['value'] : null,
            'description' => trim($data['description'] ?? '') ?: null,
            'championship_id' => $championshipId,
        ]);
        (new AthleteStatistic())->recalculate($championshipId);
    }

    public function delete(int $id, int $championshipId): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM match_events e
             USING matches m
             WHERE m.id = e.match_id AND e.id = ? AND m.championship_id = ?'
        );
        $stmt->execute([$id, $championshipId]);
        (new AthleteStatistic())->recalculate($championshipId);
    }

    public function byMatch(int $matchId, int $championshipId): array
    {
        $events = $this->byChampionship($championshipId);
        return $events[$matchId] ?? [];
    }
}

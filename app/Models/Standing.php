<?php

namespace App\Models;

class Standing extends Model
{
    public function byChampionship(int $championshipId): array
    {
        $stmt = $this->db->prepare(
            'SELECT s.*, t.name AS team_name, a.name AS athlete_name
             FROM standings s
             LEFT JOIN teams t ON t.id = s.team_id
             LEFT JOIN athletes a ON a.id = s.athlete_id
             WHERE s.championship_id = ?
             ORDER BY s.group_name NULLS FIRST, s.points DESC, s.wins DESC, s.score_difference DESC, s.score_for DESC'
        );
        $stmt->execute([$championshipId]);
        return $stmt->fetchAll();
    }

    public function recalculate(int $championshipId): void
    {
        $this->db->prepare('DELETE FROM standings WHERE championship_id = ?')->execute([$championshipId]);
        $stmt = $this->db->prepare(
            'SELECT * FROM matches
             WHERE championship_id = ? AND status = ? AND home_score IS NOT NULL AND away_score IS NOT NULL
             ORDER BY id'
        );
        $stmt->execute([$championshipId, 'finalizada']);
        $rows = [];
        foreach ($stmt->fetchAll() as $match) {
            $this->apply($rows, $championshipId, $match, true);
            $this->apply($rows, $championshipId, $match, false);
        }

        $insert = $this->db->prepare(
            'INSERT INTO standings
             (championship_id, team_id, athlete_id, group_name, played, wins, draws, losses, score_for, score_against, score_difference, points)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($rows as $row) {
            $insert->execute([
                $row['championship_id'], $row['team_id'], $row['athlete_id'], $row['group_name'],
                $row['played'], $row['wins'], $row['draws'], $row['losses'],
                $row['score_for'], $row['score_against'], $row['score_for'] - $row['score_against'], $row['points'],
            ]);
        }
    }

    private function apply(array &$rows, int $championshipId, array $match, bool $home): void
    {
        $teamId = $home ? $match['home_team_id'] : $match['away_team_id'];
        $athleteId = $home ? $match['home_athlete_id'] : $match['away_athlete_id'];
        if (!$teamId && !$athleteId) {
            return;
        }

        $for = (int) ($home ? $match['home_score'] : $match['away_score']);
        $against = (int) ($home ? $match['away_score'] : $match['home_score']);
        $key = ($teamId ? 't' . $teamId : 'a' . $athleteId) . ':' . ($match['group_name'] ?? '');
        $rows[$key] ??= [
            'championship_id' => $championshipId,
            'team_id' => $teamId ? (int) $teamId : null,
            'athlete_id' => $athleteId ? (int) $athleteId : null,
            'group_name' => $match['group_name'] ?: null,
            'played' => 0,
            'wins' => 0,
            'draws' => 0,
            'losses' => 0,
            'score_for' => 0,
            'score_against' => 0,
            'points' => 0,
        ];

        $rows[$key]['played']++;
        $rows[$key]['score_for'] += $for;
        $rows[$key]['score_against'] += $against;
        if ($for > $against) {
            $rows[$key]['wins']++;
            $rows[$key]['points'] += 3;
        } elseif ($for === $against) {
            $rows[$key]['draws']++;
            $rows[$key]['points']++;
        } else {
            $rows[$key]['losses']++;
        }
    }
}

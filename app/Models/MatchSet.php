<?php

namespace App\Models;

class MatchSet extends Model
{
    public function byChampionship(int $championshipId): array
    {
        $stmt = $this->db->prepare(
            'SELECT s.*
             FROM match_sets s
             JOIN matches m ON m.id = s.match_id
             WHERE m.championship_id = ?
             ORDER BY s.set_number'
        );
        $stmt->execute([$championshipId]);
        $sets = [];
        foreach ($stmt->fetchAll() as $set) {
            $sets[(int) $set['match_id']][] = $set;
        }
        return $sets;
    }

    public function save(array $data, int $championshipId): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO match_sets (match_id, set_number, home_score, away_score, winner_team_id, winner_athlete_id)
             SELECT :match_id, :set_number, :home_score, :away_score, :winner_team_id, :winner_athlete_id
             WHERE EXISTS (SELECT 1 FROM matches WHERE id = :match_id AND championship_id = :championship_id)
             ON CONFLICT (match_id, set_number) DO UPDATE
             SET home_score = EXCLUDED.home_score,
                 away_score = EXCLUDED.away_score,
                 winner_team_id = EXCLUDED.winner_team_id,
                 winner_athlete_id = EXCLUDED.winner_athlete_id'
        );
        $homeScore = (int) ($data['home_score'] ?? 0);
        $awayScore = (int) ($data['away_score'] ?? 0);
        $match = (new CompetitionMatch())->findInChampionship((int) $data['match_id'], $championshipId);
        $stmt->execute([
            'match_id' => (int) $data['match_id'],
            'set_number' => (int) ($data['set_number'] ?? 1),
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'winner_team_id' => $homeScore === $awayScore ? null : ($homeScore > $awayScore ? ($match['home_team_id'] ?? null) : ($match['away_team_id'] ?? null)),
            'winner_athlete_id' => $homeScore === $awayScore ? null : ($homeScore > $awayScore ? ($match['home_athlete_id'] ?? null) : ($match['away_athlete_id'] ?? null)),
            'championship_id' => $championshipId,
        ]);
        (new AthleteStatistic())->recalculate($championshipId);
    }
}

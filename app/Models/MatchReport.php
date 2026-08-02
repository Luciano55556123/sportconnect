<?php

namespace App\Models;

class MatchReport extends Model
{
    public function forMatch(int $matchId): array
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM match_reports WHERE match_id = ? LIMIT 1');
            $stmt->execute([$matchId]);
            return $stmt->fetch() ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

    public function save(int $matchId, array $data, bool $isAdmin = false): bool
    {
        $existing = $this->forMatch($matchId);
        if (!empty($existing['finalized_at']) && !$isAdmin) {
            return false;
        }

        $finalize = !empty($data['finalize']);
        $stmt = $this->db->prepare(
            'INSERT INTO match_reports
             (match_id, referee_name, summary, incidents, organizer_confirmation, home_team_confirmation, away_team_confirmation, finalized_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, CASE WHEN ? = true THEN CURRENT_TIMESTAMP ELSE NULL END)
             ON CONFLICT (match_id) DO UPDATE SET
                referee_name = EXCLUDED.referee_name,
                summary = EXCLUDED.summary,
                incidents = EXCLUDED.incidents,
                organizer_confirmation = EXCLUDED.organizer_confirmation,
                home_team_confirmation = EXCLUDED.home_team_confirmation,
                away_team_confirmation = EXCLUDED.away_team_confirmation,
                finalized_at = CASE WHEN ? = true THEN CURRENT_TIMESTAMP ELSE match_reports.finalized_at END,
                updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            $matchId,
            trim($data['referee_name'] ?? ''),
            trim($data['summary'] ?? ''),
            trim($data['incidents'] ?? ''),
            !empty($data['organizer_confirmation']),
            !empty($data['home_team_confirmation']),
            !empty($data['away_team_confirmation']),
            $finalize,
            $finalize,
        ]);
        return true;
    }
}

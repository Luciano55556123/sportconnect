<?php

namespace App\Models;

class CompetitionManagement extends Model
{
    public function overview(int $championshipId): array
    {
        return [
            'teams' => $this->teams($championshipId),
            'athletes' => $this->athletes($championshipId),
            'matches' => $this->matches($championshipId),
            'standings' => $this->standings($championshipId),
            'events' => $this->events($championshipId),
            'statistics' => $this->statistics($championshipId),
            'sets' => $this->sets($championshipId),
            'reports' => $this->reports($championshipId),
            'reschedules' => $this->reschedules($championshipId),
        ];
    }

    public function hasAnyData(int $championshipId): bool
    {
        foreach ($this->counts($championshipId) as $count) {
            if ($count > 0) {
                return true;
            }
        }

        return false;
    }

    public function counts(int $championshipId): array
    {
        return [
            'teams' => $this->countByChampionship('teams', $championshipId),
            'athletes' => $this->countByChampionship('athletes', $championshipId),
            'matches' => $this->countByChampionship('matches', $championshipId),
            'events' => $this->countByMatches('match_events', $championshipId),
            'sets' => $this->countByMatches('match_sets', $championshipId),
            'standings' => $this->countByChampionship('standings', $championshipId),
            'statistics' => $this->countByChampionship('athlete_statistics', $championshipId),
            'reports' => $this->countByMatches('match_reports', $championshipId),
            'reschedules' => $this->countByMatches('match_reschedules', $championshipId),
        ];
    }

    public function teams(int $championshipId): array
    {
        if (!$this->tableExists('teams')) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT id, name, shield, city, responsible_name, responsible_phone, status
             FROM teams
             WHERE championship_id = ?
             ORDER BY name ASC'
        );
        $stmt->execute([$championshipId]);
        return $stmt->fetchAll();
    }

    public function athletes(int $championshipId): array
    {
        if (!$this->tableExists('athletes')) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT a.*, t.name AS team_name
             FROM athletes a
             LEFT JOIN teams t ON t.id = a.team_id
             WHERE a.championship_id = ?
             ORDER BY t.name ASC NULLS LAST, a.name ASC'
        );
        $stmt->execute([$championshipId]);
        return $stmt->fetchAll();
    }

    public function matches(int $championshipId): array
    {
        if (!$this->tableExists('matches')) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT m.*, ht.name AS home_team, at.name AS away_team,
                    ha.name AS home_athlete, aa.name AS away_athlete
             FROM matches m
             LEFT JOIN teams ht ON ht.id = m.home_team_id
             LEFT JOIN teams at ON at.id = m.away_team_id
             LEFT JOIN athletes ha ON ha.id = m.home_athlete_id
             LEFT JOIN athletes aa ON aa.id = m.away_athlete_id
             WHERE m.championship_id = ?
             ORDER BY m.match_date ASC NULLS LAST, m.match_time ASC NULLS LAST, m.round_number ASC NULLS LAST, m.id ASC'
        );
        $stmt->execute([$championshipId]);
        return $stmt->fetchAll();
    }

    public function standings(int $championshipId): array
    {
        if (!$this->tableExists('standings')) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT s.*, t.name AS team_name, a.name AS athlete_name
             FROM standings s
             LEFT JOIN teams t ON t.id = s.team_id
             LEFT JOIN athletes a ON a.id = s.athlete_id
             WHERE s.championship_id = ?
             ORDER BY s.group_name ASC NULLS LAST, s.points DESC, s.score_difference DESC, s.score_for DESC'
        );
        $stmt->execute([$championshipId]);
        return $stmt->fetchAll();
    }

    public function events(int $championshipId): array
    {
        if (!$this->tableExists('match_events') || !$this->tableExists('matches')) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT e.*, m.round_number, m.match_date, m.match_time,
                    ht.name AS home_team, at.name AS away_team,
                    t.name AS team_name, a.name AS athlete_name
             FROM match_events e
             JOIN matches m ON m.id = e.match_id
             LEFT JOIN teams ht ON ht.id = m.home_team_id
             LEFT JOIN teams at ON at.id = m.away_team_id
             LEFT JOIN teams t ON t.id = e.team_id
             LEFT JOIN athletes a ON a.id = e.athlete_id
             WHERE m.championship_id = ?
             ORDER BY m.match_date ASC NULLS LAST, m.match_time ASC NULLS LAST, e.minute ASC NULLS LAST, e.id ASC'
        );
        $stmt->execute([$championshipId]);
        return $stmt->fetchAll();
    }

    public function statistics(int $championshipId): array
    {
        if (!$this->tableExists('athlete_statistics')) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT st.*, a.name AS athlete_name, t.name AS team_name
             FROM athlete_statistics st
             LEFT JOIN athletes a ON a.id = st.athlete_id
             LEFT JOIN teams t ON t.id = st.team_id
             WHERE st.championship_id = ?
             ORDER BY st.goals DESC, st.points DESC, st.wins DESC, a.name ASC'
        );
        $stmt->execute([$championshipId]);
        return $stmt->fetchAll();
    }

    public function sets(int $championshipId): array
    {
        if (!$this->tableExists('match_sets') || !$this->tableExists('matches')) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT ms.*, m.round_number, m.match_date, ht.name AS home_team, at.name AS away_team,
                    wt.name AS winner_team, wa.name AS winner_athlete
             FROM match_sets ms
             JOIN matches m ON m.id = ms.match_id
             LEFT JOIN teams ht ON ht.id = m.home_team_id
             LEFT JOIN teams at ON at.id = m.away_team_id
             LEFT JOIN teams wt ON wt.id = ms.winner_team_id
             LEFT JOIN athletes wa ON wa.id = ms.winner_athlete_id
             WHERE m.championship_id = ?
             ORDER BY m.match_date ASC NULLS LAST, m.match_time ASC NULLS LAST, ms.set_number ASC'
        );
        $stmt->execute([$championshipId]);
        return $stmt->fetchAll();
    }

    public function reports(int $championshipId): array
    {
        if (!$this->tableExists('match_reports') || !$this->tableExists('matches')) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT mr.*, m.round_number, m.match_date, ht.name AS home_team, at.name AS away_team
             FROM match_reports mr
             JOIN matches m ON m.id = mr.match_id
             LEFT JOIN teams ht ON ht.id = m.home_team_id
             LEFT JOIN teams at ON at.id = m.away_team_id
             WHERE m.championship_id = ?
             ORDER BY mr.created_at DESC'
        );
        $stmt->execute([$championshipId]);
        return $stmt->fetchAll();
    }

    public function reschedules(int $championshipId): array
    {
        if (!$this->tableExists('match_reschedules') || !$this->tableExists('matches')) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT mr.*, m.round_number, ht.name AS home_team, at.name AS away_team, u.name AS changed_by_name
             FROM match_reschedules mr
             JOIN matches m ON m.id = mr.match_id
             LEFT JOIN teams ht ON ht.id = m.home_team_id
             LEFT JOIN teams at ON at.id = m.away_team_id
             LEFT JOIN users u ON u.id = mr.changed_by
             WHERE m.championship_id = ?
             ORDER BY mr.created_at DESC'
        );
        $stmt->execute([$championshipId]);
        return $stmt->fetchAll();
    }

    private function countByChampionship(string $table, int $championshipId): int
    {
        if (!$this->tableExists($table)) {
            return 0;
        }

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM ' . $table . ' WHERE championship_id = ?');
        $stmt->execute([$championshipId]);
        return (int) $stmt->fetchColumn();
    }

    private function countByMatches(string $table, int $championshipId): int
    {
        if (!$this->tableExists($table) || !$this->tableExists('matches')) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM ' . $table . '
             WHERE match_id IN (SELECT id FROM matches WHERE championship_id = ?)'
        );
        $stmt->execute([$championshipId]);
        return (int) $stmt->fetchColumn();
    }

    private function tableExists(string $table): bool
    {
        $stmt = $this->db->prepare(
            "SELECT EXISTS (
                SELECT 1
                FROM information_schema.tables
                WHERE table_schema = 'public' AND table_name = ?
            )"
        );
        $stmt->execute([$table]);
        return (bool) $stmt->fetchColumn();
    }
}

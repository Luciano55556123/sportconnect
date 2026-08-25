<?php

namespace App\Models;

class CompetitionManagement extends Model
{
    private array $tableExistsCache = [];

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
            'summary' => $this->summary($championshipId),
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
        $registrations = new Registration();

        return [
            'teams' => $registrations->countApprovedTeamsForChampionship($championshipId),
            'athletes' => $this->isTeamRegistrationChampionship($championshipId)
                ? $this->countByChampionship('athletes', $championshipId)
                : $registrations->countApprovedIndividualRegistrationsForChampionship($championshipId),
            'matches' => $this->countByChampionship('matches', $championshipId),
            'events' => $this->countByMatches('match_events', $championshipId),
            'goals' => $this->countEventsByType($championshipId, ['gol', 'penalti_convertido', 'ponto', 'ataque', 'ace', 'bloqueio']),
            'cards' => $this->countEventsByType($championshipId, ['cartao_amarelo', 'cartao_vermelho']),
            'sets' => $this->countByMatches('match_sets', $championshipId),
            'standings' => $this->countByChampionship('standings', $championshipId),
            'statistics' => $this->countByChampionship('athlete_statistics', $championshipId),
            'reports' => $this->countByMatches('match_reports', $championshipId),
            'reschedules' => $this->countByMatches('match_reschedules', $championshipId),
            'completed_matches' => $this->countCompletedMatches($championshipId),
        ];
    }

    public function summary(int $championshipId): array
    {
        $counts = $this->counts($championshipId);
        $matches = max(1, (int) ($counts['matches'] ?? 0));

        return [
            'progress' => (int) round(((int) ($counts['completed_matches'] ?? 0) / $matches) * 100),
            'counts' => $counts,
        ];
    }

    public function teams(int $championshipId): array
    {
        if (!$this->tableExists('teams')) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT t.id, t.name, t.shield, t.city, t.responsible_name, t.responsible_phone, t.status,
                    COUNT(a.id) AS athletes_count
             FROM teams t
             LEFT JOIN athletes a ON a.team_id = t.id
             WHERE t.championship_id = ?
             GROUP BY t.id, t.name, t.shield, t.city, t.responsible_name, t.responsible_phone, t.status
             ORDER BY t.name ASC'
        );
        $stmt->execute([$championshipId]);
        return $stmt->fetchAll();
    }

    public function matchDetails(int $championshipId, int $matchId): ?array
    {
        if (!$this->tableExists('matches')) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT m.*, ht.name AS home_team, at.name AS away_team,
                    ha.name AS home_athlete, aa.name AS away_athlete,
                    wt.name AS winner_team, wa.name AS winner_athlete
             FROM matches m
             LEFT JOIN teams ht ON ht.id = m.home_team_id
             LEFT JOIN teams at ON at.id = m.away_team_id
             LEFT JOIN athletes ha ON ha.id = m.home_athlete_id
             LEFT JOIN athletes aa ON aa.id = m.away_athlete_id
             LEFT JOIN teams wt ON wt.id = m.winner_team_id
             LEFT JOIN athletes wa ON wa.id = m.winner_athlete_id
             WHERE m.championship_id = ?
             AND m.id = ?'
        );
        $stmt->execute([$championshipId, $matchId]);
        return $stmt->fetch() ?: null;
    }

    public function matchOverview(int $championshipId, int $matchId): array
    {
        return [
            'match' => $this->matchDetails($championshipId, $matchId),
            'events' => $this->eventsForMatch($championshipId, $matchId),
            'sets' => $this->setsForMatch($championshipId, $matchId),
            'lineups' => $this->lineupsForMatch($championshipId, $matchId),
            'reports' => $this->reportsForMatch($championshipId, $matchId),
            'athletes' => $this->athletes($championshipId),
        ];
    }

    public function eventsForMatch(int $championshipId, int $matchId): array
    {
        if (!$this->tableExists('match_events') || !$this->tableExists('matches')) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT e.*, t.name AS team_name, a.name AS athlete_name
             FROM match_events e
             JOIN matches m ON m.id = e.match_id
             LEFT JOIN teams t ON t.id = e.team_id
             LEFT JOIN athletes a ON a.id = e.athlete_id
             WHERE m.championship_id = ?
             AND e.match_id = ?
             ORDER BY (e.minute IS NULL), e.minute ASC, e.id ASC'
        );
        $stmt->execute([$championshipId, $matchId]);
        return $stmt->fetchAll();
    }

    public function setsForMatch(int $championshipId, int $matchId): array
    {
        if (!$this->tableExists('match_sets') || !$this->tableExists('matches')) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT ms.*, wt.name AS winner_team, wa.name AS winner_athlete
             FROM match_sets ms
             JOIN matches m ON m.id = ms.match_id
             LEFT JOIN teams wt ON wt.id = ms.winner_team_id
             LEFT JOIN athletes wa ON wa.id = ms.winner_athlete_id
             WHERE m.championship_id = ?
             AND ms.match_id = ?
             ORDER BY ms.set_number ASC'
        );
        $stmt->execute([$championshipId, $matchId]);
        return $stmt->fetchAll();
    }

    public function lineupsForMatch(int $championshipId, int $matchId): array
    {
        if (!$this->tableExists('match_lineups') || !$this->tableExists('matches')) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT ml.*, t.name AS team_name, a.name AS athlete_name
             FROM match_lineups ml
             JOIN matches m ON m.id = ml.match_id
             LEFT JOIN teams t ON t.id = ml.team_id
             LEFT JOIN athletes a ON a.id = ml.athlete_id
             WHERE m.championship_id = ?
             AND ml.match_id = ?
             ORDER BY (t.name IS NULL), t.name ASC, ml.is_starter DESC, a.name ASC'
        );
        $stmt->execute([$championshipId, $matchId]);
        return $stmt->fetchAll();
    }

    public function reportsForMatch(int $championshipId, int $matchId): array
    {
        if (!$this->tableExists('match_reports') || !$this->tableExists('matches')) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT mr.*
             FROM match_reports mr
             JOIN matches m ON m.id = mr.match_id
             WHERE m.championship_id = ?
             AND mr.match_id = ?
             ORDER BY mr.created_at DESC'
        );
        $stmt->execute([$championshipId, $matchId]);
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
             ORDER BY (t.name IS NULL), t.name ASC, a.name ASC'
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
             ORDER BY (m.match_date IS NULL), m.match_date ASC, (m.match_time IS NULL), m.match_time ASC, (m.round_number IS NULL), m.round_number ASC, m.id ASC'
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
             ORDER BY (s.group_name IS NULL), s.group_name ASC, s.points DESC, s.score_difference DESC, s.score_for DESC'
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
             ORDER BY (m.match_date IS NULL), m.match_date ASC, (m.match_time IS NULL), m.match_time ASC, (e.minute IS NULL), e.minute ASC, e.id ASC'
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
             ORDER BY (m.match_date IS NULL), m.match_date ASC, (m.match_time IS NULL), m.match_time ASC, ms.set_number ASC'
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

    private function countApprovedTeams(int $championshipId): int
    {
        if (!$this->tableExists('teams')) {
            return 0;
        }

        $stmt = $this->db->prepare(
            "SELECT COUNT(*)
             FROM teams
             WHERE championship_id = ?
             AND status = 'aprovado'"
        );
        $stmt->execute([$championshipId]);
        return (int) $stmt->fetchColumn();
    }

    private function isTeamRegistrationChampionship(int $championshipId): bool
    {
        $stmt = $this->db->prepare('SELECT registration_type FROM championships WHERE id = ?');
        $stmt->execute([$championshipId]);
        return $stmt->fetchColumn() === 'team';
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

    private function countCompletedMatches(int $championshipId): int
    {
        if (!$this->tableExists('matches')) {
            return 0;
        }

        $stmt = $this->db->prepare(
            "SELECT COUNT(*)
             FROM matches
             WHERE championship_id = ?
             AND status IN ('finalizada', 'completed', 'encerrada')"
        );
        $stmt->execute([$championshipId]);
        return (int) $stmt->fetchColumn();
    }

    private function countEventsByType(int $championshipId, array $types): int
    {
        if (!$types || !$this->tableExists('match_events') || !$this->tableExists('matches')) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM match_events e
             JOIN matches m ON m.id = e.match_id
             WHERE m.championship_id = ?
             AND e.event_type IN (' . implode(',', array_fill(0, count($types), '?')) . ')'
        );
        $stmt->execute(array_merge([$championshipId], $types));
        return (int) $stmt->fetchColumn();
    }

    private function tableExists(string $table): bool
    {
        if (array_key_exists($table, $this->tableExistsCache)) {
            return $this->tableExistsCache[$table];
        }

        if ($this->db->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            $stmt = $this->db->prepare(
                "SELECT EXISTS (
                    SELECT 1
                    FROM information_schema.tables
                    WHERE table_schema = 'public' AND table_name = ?
                )"
            );
            $stmt->execute([$table]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*)
                 FROM information_schema.tables
                 WHERE table_schema = DATABASE() AND table_name = ?"
            );
            $stmt->execute([$table]);
        }

        $this->tableExistsCache[$table] = (bool) $stmt->fetchColumn();
        return $this->tableExistsCache[$table];
    }
}

<?php

namespace App\Models;

class AthleteStatistic extends Model
{
    public function byChampionship(int $championshipId): array
    {
        $stmt = $this->db->prepare(
            'SELECT s.*, a.name AS athlete_name, t.name AS team_name
             FROM athlete_statistics s
             JOIN athletes a ON a.id = s.athlete_id
             LEFT JOIN teams t ON t.id = s.team_id
             WHERE s.championship_id = ?
             ORDER BY s.goals DESC, s.points DESC, s.wins DESC, a.name'
        );
        $stmt->execute([$championshipId]);
        return $stmt->fetchAll();
    }

    public function recalculate(int $championshipId): void
    {
        $this->db->prepare('DELETE FROM athlete_statistics WHERE championship_id = ?')->execute([$championshipId]);
        $stmt = $this->db->prepare(
            'INSERT INTO athlete_statistics
             (championship_id, athlete_id, team_id, goals, yellow_cards, red_cards, points, aces, blocks)
             SELECT m.championship_id,
                    e.athlete_id,
                    MAX(e.team_id) AS team_id,
                    SUM(CASE WHEN e.event_type IN (' . $this->db->quote('gol') . ', ' . $this->db->quote('penalti_convertido') . ') THEN 1 ELSE 0 END) AS goals,
                    SUM(CASE WHEN e.event_type = ' . $this->db->quote('cartao_amarelo') . ' THEN 1 ELSE 0 END) AS yellow_cards,
                    SUM(CASE WHEN e.event_type = ' . $this->db->quote('cartao_vermelho') . ' THEN 1 ELSE 0 END) AS red_cards,
                    SUM(CASE WHEN e.event_type = ' . $this->db->quote('ponto') . ' THEN COALESCE(e.value, 1) ELSE 0 END) AS points,
                    SUM(CASE WHEN e.event_type = ' . $this->db->quote('saque') . ' THEN 1 ELSE 0 END) AS aces,
                    SUM(CASE WHEN e.event_type = ' . $this->db->quote('bloqueio') . ' THEN 1 ELSE 0 END) AS blocks
             FROM match_events e
             JOIN matches m ON m.id = e.match_id
             WHERE m.championship_id = ? AND e.athlete_id IS NOT NULL
             GROUP BY m.championship_id, e.athlete_id'
        );
        $stmt->execute([$championshipId]);
    }
}

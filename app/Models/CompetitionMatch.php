<?php

namespace App\Models;

class CompetitionMatch extends Model
{
    public function byChampionship(int $championshipId): array
    {
        $stmt = $this->db->prepare(
            'SELECT m.*,
                    ht.name AS home_team_name, at.name AS away_team_name,
                    ha.name AS home_athlete_name, aa.name AS away_athlete_name,
                    wt.name AS winner_team_name, wa.name AS winner_athlete_name
             FROM matches m
             LEFT JOIN teams ht ON ht.id = m.home_team_id
             LEFT JOIN teams at ON at.id = m.away_team_id
             LEFT JOIN athletes ha ON ha.id = m.home_athlete_id
             LEFT JOIN athletes aa ON aa.id = m.away_athlete_id
             LEFT JOIN teams wt ON wt.id = m.winner_team_id
             LEFT JOIN athletes wa ON wa.id = m.winner_athlete_id
             WHERE m.championship_id = ?
             ORDER BY m.phase, m.round_number NULLS LAST, m.match_date NULLS LAST, m.match_time NULLS LAST, m.id'
        );
        $stmt->execute([$championshipId]);
        return $stmt->fetchAll();
    }

    public function findInChampionship(int $id, int $championshipId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT m.*,
                    ht.name AS home_team_name, at.name AS away_team_name,
                    ha.name AS home_athlete_name, aa.name AS away_athlete_name,
                    wt.name AS winner_team_name, wa.name AS winner_athlete_name
             FROM matches m
             LEFT JOIN teams ht ON ht.id = m.home_team_id
             LEFT JOIN teams at ON at.id = m.away_team_id
             LEFT JOIN athletes ha ON ha.id = m.home_athlete_id
             LEFT JOIN athletes aa ON aa.id = m.away_athlete_id
             LEFT JOIN teams wt ON wt.id = m.winner_team_id
             LEFT JOIN athletes wa ON wa.id = m.winner_athlete_id
             WHERE m.id = ? AND m.championship_id = ?'
        );
        $stmt->execute([$id, $championshipId]);
        return $stmt->fetch() ?: null;
    }

    public function save(array $data, int $championshipId): int
    {
        $payload = $this->payload($data, $championshipId);
        $this->validateParticipants($payload, $championshipId);

        if (!empty($data['id'])) {
            $stmt = $this->db->prepare(
                'UPDATE matches
                 SET phase = :phase, group_name = :group_name, round_number = :round_number,
                     home_team_id = :home_team_id, away_team_id = :away_team_id,
                     home_athlete_id = :home_athlete_id, away_athlete_id = :away_athlete_id,
                     match_date = :match_date, match_time = :match_time, venue = :venue,
                     court_or_field = :court_or_field, referee = :referee, status = :status,
                     next_match_id = :next_match_id, next_match_position = :next_match_position,
                     notes = :notes, updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id AND championship_id = :championship_id
                 RETURNING id'
            );
            $stmt->execute($payload + ['id' => (int) $data['id']]);
            return (int) $stmt->fetchColumn();
        }

        $stmt = $this->db->prepare(
            'INSERT INTO matches
             (championship_id, phase, group_name, round_number, home_team_id, away_team_id,
              home_athlete_id, away_athlete_id, match_date, match_time, venue, court_or_field,
              referee, status, next_match_id, next_match_position, notes)
             VALUES
             (:championship_id, :phase, :group_name, :round_number, :home_team_id, :away_team_id,
              :home_athlete_id, :away_athlete_id, :match_date, :match_time, :venue, :court_or_field,
              :referee, :status, :next_match_id, :next_match_position, :notes)
             RETURNING id'
        );
        $stmt->execute($payload);
        return (int) $stmt->fetchColumn();
    }

    public function recordResult(int $id, int $championshipId, array $data): void
    {
        $this->db->beginTransaction();
        try {
            $match = $this->findInChampionship($id, $championshipId);
            if (!$match) {
                throw new \RuntimeException('Partida nao encontrada.');
            }

            $homeScore = (int) ($data['home_score'] ?? 0);
            $awayScore = (int) ($data['away_score'] ?? 0);
            $status = in_array($data['status'] ?? '', $this->allowedStatuses(), true) ? $data['status'] : 'finalizada';
            $winnerTeamId = null;
            $winnerAthleteId = null;

            // Uma partida nao finalizada nao deve enviar vencedor a fase seguinte.
            if ($status === 'finalizada' && $homeScore > $awayScore) {
                $winnerTeamId = $match['home_team_id'] ? (int) $match['home_team_id'] : null;
                $winnerAthleteId = $match['home_athlete_id'] ? (int) $match['home_athlete_id'] : null;
            } elseif ($status === 'finalizada' && $awayScore > $homeScore) {
                $winnerTeamId = $match['away_team_id'] ? (int) $match['away_team_id'] : null;
                $winnerAthleteId = $match['away_athlete_id'] ? (int) $match['away_athlete_id'] : null;
            }

            $stmt = $this->db->prepare(
                'UPDATE matches
                 SET home_score = ?, away_score = ?, status = ?, winner_team_id = ?, winner_athlete_id = ?, notes = ?, updated_at = CURRENT_TIMESTAMP
                 WHERE id = ? AND championship_id = ?'
            );
            $stmt->execute([$homeScore, $awayScore, $status, $winnerTeamId, $winnerAthleteId, trim($data['notes'] ?? '') ?: ($match['notes'] ?? null), $id, $championshipId]);

            $this->advanceWinner($match, $winnerTeamId, $winnerAthleteId);
            (new Standing())->recalculate($championshipId);
            (new AthleteStatistic())->recalculate($championshipId);

            $this->db->commit();
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            error_log($exception->getMessage());
            throw $exception;
        }
    }

    public function deleteIfNoResult(int $id, int $championshipId): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM matches
             WHERE id = ? AND championship_id = ? AND home_score IS NULL AND away_score IS NULL
             AND NOT EXISTS (SELECT 1 FROM match_events e WHERE e.match_id = matches.id)
             AND NOT EXISTS (SELECT 1 FROM match_sets s WHERE s.match_id = matches.id)'
        );
        $stmt->execute([$id, $championshipId]);
    }

    private function advanceWinner(array $match, ?int $winnerTeamId, ?int $winnerAthleteId): void
    {
        if (empty($match['next_match_id']) || empty($match['next_match_position'])) {
            return;
        }

        $previousTeam = !empty($match['winner_team_id']) ? (int) $match['winner_team_id'] : null;
        $previousAthlete = !empty($match['winner_athlete_id']) ? (int) $match['winner_athlete_id'] : null;
        if ($previousTeam === $winnerTeamId && $previousAthlete === $winnerAthleteId
            && $winnerTeamId === null && $winnerAthleteId === null) {
            return;
        }

        $nextId = (int) $match['next_match_id'];
        if ($nextId === (int) $match['id']) {
            throw new \DomainException('Uma partida nao pode avancar para ela mesma.');
        }

        $position = $match['next_match_position'] === 'away' ? 'away' : 'home';
        $teamColumn = $position . '_team_id';
        $athleteColumn = $position . '_athlete_id';

        // A partida de destino e bloqueada para impedir alteracoes concorrentes.
        $stmt = $this->db->prepare(
            'SELECT * FROM matches WHERE id = ? AND championship_id = ? FOR UPDATE'
        );
        $stmt->execute([$nextId, (int) $match['championship_id']]);
        $next = $stmt->fetch();
        if (!$next) {
            throw new \DomainException('A proxima partida configurada nao foi encontrada.');
        }

        $currentTeam = !empty($next[$teamColumn]) ? (int) $next[$teamColumn] : null;
        $currentAthlete = !empty($next[$athleteColumn]) ? (int) $next[$athleteColumn] : null;

        if ($currentTeam === $winnerTeamId && $currentAthlete === $winnerAthleteId) {
            return; // O vencedor correto ja esta na chave.
        }

        // Nao sobrescrever um participante colocado manualmente ou de outra origem.
        if (($currentTeam !== null || $currentAthlete !== null)
            && ($currentTeam !== $previousTeam || $currentAthlete !== $previousAthlete)) {
            throw new \DomainException(
                'A vaga na proxima partida pertence a outro participante. Confira o chaveamento antes de alterar.'
            );
        }

        // Nunca mudar os participantes de uma partida com resultado ou ja iniciada.
        if (!in_array($next['status'] ?? '', ['agendada', 'adiada'], true)
            || $next['home_score'] !== null || $next['away_score'] !== null) {
            throw new \DomainException(
                'A proxima partida ja foi iniciada ou possui resultado. Revise essa partida antes de corrigir o classificado.'
            );
        }

        $update = $this->db->prepare(
            "UPDATE matches SET {$teamColumn} = ?, {$athleteColumn} = ?,
             updated_at = CURRENT_TIMESTAMP WHERE id = ? AND championship_id = ?"
        );
        $update->execute([$winnerTeamId, $winnerAthleteId, $nextId, (int) $match['championship_id']]);
    }

    private function payload(array $data, int $championshipId): array
    {
        return [
            'championship_id' => $championshipId,
            'phase' => trim($data['phase'] ?? '') ?: 'fase unica',
            'group_name' => trim($data['group_name'] ?? '') ?: null,
            'round_number' => ($data['round_number'] ?? '') !== '' ? (int) $data['round_number'] : null,
            'home_team_id' => !empty($data['home_team_id']) ? (int) $data['home_team_id'] : null,
            'away_team_id' => !empty($data['away_team_id']) ? (int) $data['away_team_id'] : null,
            'home_athlete_id' => !empty($data['home_athlete_id']) ? (int) $data['home_athlete_id'] : null,
            'away_athlete_id' => !empty($data['away_athlete_id']) ? (int) $data['away_athlete_id'] : null,
            'match_date' => ($data['match_date'] ?? '') !== '' ? $data['match_date'] : null,
            'match_time' => ($data['match_time'] ?? '') !== '' ? $data['match_time'] : null,
            'venue' => trim($data['venue'] ?? '') ?: null,
            'court_or_field' => trim($data['court_or_field'] ?? '') ?: null,
            'referee' => trim($data['referee'] ?? '') ?: null,
            'status' => in_array($data['status'] ?? '', $this->allowedStatuses(), true) ? $data['status'] : 'agendada',
            'next_match_id' => !empty($data['next_match_id']) ? (int) $data['next_match_id'] : null,
            'next_match_position' => in_array($data['next_match_position'] ?? '', ['home', 'away'], true) ? $data['next_match_position'] : null,
            'notes' => trim($data['notes'] ?? '') ?: null,
        ];
    }

    private function allowedStatuses(): array
    {
        return ['agendada', 'em_andamento', 'finalizada'];
    }

    private function validateParticipants(array $payload, int $championshipId): void
    {
        $homeTeamId = $payload['home_team_id'];
        $awayTeamId = $payload['away_team_id'];
        if ($homeTeamId && $awayTeamId && (int) $homeTeamId === (int) $awayTeamId) {
            throw new \InvalidArgumentException('Mandante e visitante nao podem ser iguais.');
        }

        foreach (['home_team_id' => $homeTeamId, 'away_team_id' => $awayTeamId] as $field => $teamId) {
            if (!$teamId) {
                continue;
            }
            $stmt = $this->db->prepare('SELECT 1 FROM teams WHERE id = ? AND championship_id = ? LIMIT 1');
            $stmt->execute([$teamId, $championshipId]);
            if (!$stmt->fetchColumn()) {
                throw new \InvalidArgumentException('Equipe invalida para este campeonato: ' . $field);
            }
        }

        foreach (['home_athlete_id', 'away_athlete_id'] as $field) {
            $athleteId = $payload[$field];
            if (!$athleteId) {
                continue;
            }
            $stmt = $this->db->prepare('SELECT 1 FROM athletes WHERE id = ? AND championship_id = ? LIMIT 1');
            $stmt->execute([$athleteId, $championshipId]);
            if (!$stmt->fetchColumn()) {
                throw new \InvalidArgumentException('Atleta invalido para este campeonato: ' . $field);
            }
        }

        if ($payload['next_match_id']) {
            $stmt = $this->db->prepare('SELECT 1 FROM matches WHERE id = ? AND championship_id = ? LIMIT 1');
            $stmt->execute([$payload['next_match_id'], $championshipId]);
            if (!$stmt->fetchColumn()) {
                throw new \InvalidArgumentException('Proxima partida invalida para este campeonato.');
            }
        }
    }
}

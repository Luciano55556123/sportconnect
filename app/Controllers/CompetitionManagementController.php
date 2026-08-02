<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Athlete;
use App\Models\AthleteStatistic;
use App\Models\Championship;
use App\Models\CompetitionActivityLog;
use App\Models\CompetitionMatch;
use App\Models\MatchEvent;
use App\Models\MatchReport;
use App\Models\MatchSet;
use App\Models\Registration;
use App\Models\Standing;
use App\Models\Team;
use App\Models\User;

class CompetitionManagementController extends Controller
{
    public function manage(string $id): void
    {
        $championship = $this->authorizedChampionship((int) $id);
        $this->view('organizer/manage', $this->viewData($championship));
    }

    public function updateInfo(string $id): void
    {
        $championship = $this->authorizedChampionship((int) $id);
        verify_csrf();
        (new Championship())->updateCompetitionInfo((int) $championship['id'], array_merge($championship, $_POST), Auth::user()['id'], $this->isAdmin());
        $this->log((int) $championship['id'], 'informacoes_atualizadas', 'Informacoes gerais e regulamento atualizados.');
        flash('success', 'Informacoes da competicao atualizadas.');
        $this->redirect('/organizador/campeonatos/' . $id . '/gerenciar');
    }

    public function saveTeam(string $id): void
    {
        $championship = $this->authorizedChampionship((int) $id);
        verify_csrf();
        (new Team())->save($_POST, (int) $championship['id']);
        $this->log((int) $championship['id'], 'equipe_salva', 'Equipe cadastrada ou atualizada: ' . ($_POST['name'] ?? 'sem nome'));
        flash('success', 'Equipe salva.');
        $this->redirect('/organizador/campeonatos/' . $id . '/gerenciar#equipes');
    }

    public function deleteTeam(string $id, string $teamId): void
    {
        $championship = $this->authorizedChampionship((int) $id);
        verify_csrf();
        (new Team())->delete((int) $teamId, (int) $championship['id']);
        flash('success', 'Equipe excluida quando nao havia vinculos com jogos.');
        $this->redirect('/organizador/campeonatos/' . $id . '/gerenciar#equipes');
    }

    public function saveAthlete(string $id): void
    {
        $championship = $this->authorizedChampionship((int) $id);
        verify_csrf();
        (new Athlete())->save($_POST, (int) $championship['id']);
        $this->log((int) $championship['id'], 'atleta_salvo', 'Atleta ou participante salvo: ' . ($_POST['name'] ?? 'sem nome'));
        flash('success', 'Atleta ou participante salvo.');
        $this->redirect('/organizador/campeonatos/' . $id . '/gerenciar#atletas');
    }

    public function deleteAthlete(string $id, string $athleteId): void
    {
        $championship = $this->authorizedChampionship((int) $id);
        verify_csrf();
        (new Athlete())->delete((int) $athleteId, (int) $championship['id']);
        flash('success', 'Participante excluido quando nao havia vinculos com jogos.');
        $this->redirect('/organizador/campeonatos/' . $id . '/gerenciar#atletas');
    }

    public function saveMatch(string $id): void
    {
        $championship = $this->authorizedChampionship((int) $id);
        verify_csrf();
        $matchId = (new CompetitionMatch())->save($_POST, (int) $championship['id']);
        $this->log((int) $championship['id'], 'partida_criada', 'Partida cadastrada ou atualizada #' . $matchId);
        flash('success', 'Jogo salvo.');
        $this->redirect('/organizador/campeonatos/' . $id . '/gerenciar#jogos');
    }

    public function deleteMatch(string $id, string $matchId): void
    {
        $championship = $this->authorizedChampionship((int) $id);
        verify_csrf();
        (new CompetitionMatch())->deleteIfNoResult((int) $matchId, (int) $championship['id']);
        flash('success', 'Jogo excluido quando nao havia resultado/eventos.');
        $this->redirect('/organizador/campeonatos/' . $id . '/gerenciar#jogos');
    }

    public function recordResult(string $id, string $matchId): void
    {
        $championship = $this->authorizedChampionship((int) $id);
        verify_csrf();
        try {
            (new CompetitionMatch())->recordResult((int) $matchId, (int) $championship['id'], $_POST);
            $this->log((int) $championship['id'], 'resultado_alterado', 'Resultado alterado na partida #' . $matchId);
            flash('success', 'Resultado registrado e classificacao recalculada.');
        } catch (\Throwable) {
            flash('error', 'Nao foi possivel registrar o resultado.');
        }
        $this->redirect($_POST['return_to'] ?? ('/organizador/campeonatos/' . $id . '/gerenciar#resultados'));
    }

    public function saveEvent(string $id): void
    {
        $championship = $this->authorizedChampionship((int) $id);
        verify_csrf();
        (new MatchEvent())->save($_POST, (int) $championship['id']);
        $this->log((int) $championship['id'], 'evento_registrado', 'Evento registrado na partida #' . (int) ($_POST['match_id'] ?? 0));
        flash('success', 'Evento registrado.');
        $this->redirect($_POST['return_to'] ?? ('/organizador/campeonatos/' . $id . '/gerenciar#eventos'));
    }

    public function saveSet(string $id): void
    {
        $championship = $this->authorizedChampionship((int) $id);
        verify_csrf();
        (new MatchSet())->save($_POST, (int) $championship['id']);
        $this->log((int) $championship['id'], 'set_salvo', 'Set salvo na partida #' . (int) ($_POST['match_id'] ?? 0));
        flash('success', 'Set salvo.');
        $this->redirect($_POST['return_to'] ?? ('/organizador/campeonatos/' . $id . '/gerenciar#sets'));
    }

    public function saveMatchReport(string $id, string $matchId): void
    {
        $championship = $this->authorizedChampionship((int) $id);
        verify_csrf();
        $match = (new CompetitionMatch())->findInChampionship((int) $matchId, (int) $championship['id']);
        if (!$match) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Partida nao encontrada']);
            return;
        }

        $saved = (new MatchReport())->save((int) $matchId, $_POST, $this->isAdmin());
        if ($saved) {
            $this->log((int) $championship['id'], !empty($_POST['finalize']) ? 'sumula_finalizada' : 'sumula_salva', 'Sumula atualizada na partida #' . (int) $matchId);
            flash('success', !empty($_POST['finalize']) ? 'Sumula finalizada.' : 'Sumula salva.');
        } else {
            flash('error', 'Sumula finalizada so pode ser alterada por administrador.');
        }
        $this->redirect($_POST['return_to'] ?? ('/organizador/campeonatos/' . $id . '/partidas/' . $matchId . '/gerenciar'));
    }

    public function recalculateStandings(string $id): void
    {
        $championship = $this->authorizedChampionship((int) $id);
        verify_csrf();
        (new Standing())->recalculate((int) $championship['id']);
        (new AthleteStatistic())->recalculate((int) $championship['id']);
        $this->log((int) $championship['id'], 'classificacao_recalculada', 'Classificacao reconstruida com base nas partidas finalizadas.');
        flash('success', 'Classificacao e estatisticas recalculadas.');
        $this->redirect('/organizador/campeonatos/' . $id . '/gerenciar#classificacao');
    }

    public function manageMatch(string $id, string $matchId): void
    {
        $championship = $this->authorizedChampionship((int) $id);
        $match = (new CompetitionMatch())->findInChampionship((int) $matchId, (int) $championship['id']);
        if (!$match) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Partida nao encontrada']);
            return;
        }

        $this->view('organizer/match_manage', [
            'title' => 'Gerenciar partida',
            'championship' => $championship,
            'match' => $match,
            'teams' => (new Team())->byChampionship((int) $championship['id']),
            'athletes' => (new Athlete())->byChampionship((int) $championship['id']),
            'events' => (new MatchEvent())->byMatch((int) $matchId, (int) $championship['id']),
            'sets' => (new MatchSet())->byChampionship((int) $championship['id'])[(int) $matchId] ?? [],
            'matchReport' => (new MatchReport())->forMatch((int) $matchId),
            'isAdmin' => $this->isAdmin(),
        ]);
    }

    public function deleteEvent(string $id, string $eventId): void
    {
        $championship = $this->authorizedChampionship((int) $id);
        verify_csrf();
        (new MatchEvent())->delete((int) $eventId, (int) $championship['id']);
        $this->log((int) $championship['id'], 'evento_excluido', 'Evento removido da linha do tempo.');
        flash('success', 'Evento excluido e estatisticas recalculadas.');
        $this->redirect($_POST['return_to'] ?? ('/organizador/campeonatos/' . $id . '/gerenciar#eventos'));
    }

    public function export(string $id, string $type): void
    {
        $championship = $this->authorizedChampionship((int) $id);
        $data = $this->viewData($championship);
        $map = [
            'participantes' => ['rows' => $data['athletes'], 'headers' => ['Nome', 'Equipe', 'Cidade', 'Categoria', 'Status'], 'fields' => ['name', 'team_name', 'city', 'category', 'status']],
            'jogos' => ['rows' => $data['matches'], 'headers' => ['Fase', 'Rodada', 'Data', 'Hora', 'Mandante', 'Visitante', 'Placar', 'Status'], 'fields' => ['phase', 'round_number', 'match_date', 'match_time', 'home_team_name', 'away_team_name', 'score', 'status']],
            'classificacao' => ['rows' => $data['standings'], 'headers' => ['Participante', 'J', 'V', 'E', 'D', 'GM', 'GS', 'SG', 'PTS'], 'fields' => ['name', 'played', 'wins', 'draws', 'losses', 'score_for', 'score_against', 'score_difference', 'points']],
            'artilharia' => ['rows' => $data['statistics'], 'headers' => ['Atleta', 'Equipe', 'Gols', 'Amarelos', 'Vermelhos', 'Pontos'], 'fields' => ['athlete_name', 'team_name', 'goals', 'yellow_cards', 'red_cards', 'points']],
        ];
        if (!isset($map[$type])) {
            http_response_code(404);
            exit('Relatorio nao encontrado.');
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $type . '-campeonato-' . (int) $championship['id'] . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, $map[$type]['headers']);
        foreach ($map[$type]['rows'] as $row) {
            $line = [];
            foreach ($map[$type]['fields'] as $field) {
                $line[] = match ($field) {
                    'score' => ($row['home_score'] ?? '-') . ' x ' . ($row['away_score'] ?? '-'),
                    'name' => $row['team_name'] ?? $row['athlete_name'] ?? '',
                    default => $row[$field] ?? '',
                };
            }
            fputcsv($out, $line);
        }
        fclose($out);
    }

    private function viewData(array $championship): array
    {
        $championshipId = (int) $championship['id'];
        return [
            'title' => 'Gerenciar competicao',
            'championship' => $championship,
            'teams' => (new Team())->byChampionship($championshipId),
            'athletes' => (new Athlete())->byChampionship($championshipId),
            'matches' => (new CompetitionMatch())->byChampionship($championshipId),
            'eventsByMatch' => (new MatchEvent())->byChampionship($championshipId),
            'setsByMatch' => (new MatchSet())->byChampionship($championshipId),
            'standings' => (new Standing())->byChampionship($championshipId),
            'statistics' => (new AthleteStatistic())->byChampionship($championshipId),
            'summary' => $this->summary($championship),
            'activityLogs' => (new CompetitionActivityLog())->byChampionship($championshipId),
        ];
    }

    private function summary(array $championship): array
    {
        $id = (int) $championship['id'];
        $teams = (new Team())->byChampionship($id);
        $athletes = (new Athlete())->byChampionship($id);
        $matches = (new CompetitionMatch())->byChampionship($id);
        $events = (new MatchEvent())->byChampionship($id);
        $flatEvents = array_merge(...array_values($events ?: [[]]));
        $finished = array_filter($matches, fn ($match) => ($match['status'] ?? '') === 'finalizada');
        $goals = array_filter($flatEvents, fn ($event) => in_array($event['event_type'] ?? '', ['gol', 'gol_contra', 'penalti_convertido'], true));
        $next = null;
        foreach ($matches as $match) {
            if (($match['status'] ?? '') === 'agendada') {
                $next = $match;
                break;
            }
        }

        return [
            'teams_count' => count($teams),
            'athletes_count' => count($athletes),
            'matches_count' => count($matches),
            'finished_matches' => count($finished),
            'pending_registrations' => (new Registration())->countPendingForChampionship($id),
            'goals_count' => count($goals),
            'next_match' => $next,
            'status' => $championship['status'] ?? 'ativo',
            'steps' => $this->steps($championship, $teams, $athletes, $matches),
            'stats_cards' => $this->statsCards($matches, $flatEvents),
        ];
    }

    private function steps(array $championship, array $teams, array $athletes, array $matches): array
    {
        $finished = array_filter($matches, fn ($match) => ($match['status'] ?? '') === 'finalizada');
        return [
            ['label' => 'Informacoes cadastradas', 'state' => !empty($championship['competition_format']) ? 'done' : 'problem'],
            ['label' => 'Inscricoes abertas', 'state' => !empty($championship['registrations_open']) ? 'done' : 'pending'],
            ['label' => 'Participantes definidos', 'state' => ($teams || $athletes) ? 'done' : 'problem'],
            ['label' => 'Jogos criados', 'state' => $matches ? 'done' : 'pending'],
            ['label' => 'Campeonato em andamento', 'state' => $finished ? 'done' : 'pending'],
            ['label' => 'Campeonato encerrado', 'state' => ($championship['status'] ?? '') === 'encerrado' ? 'done' : 'pending'],
        ];
    }

    private function statsCards(array $matches, array $events): array
    {
        $finished = array_filter($matches, fn ($match) => ($match['status'] ?? '') === 'finalizada');
        $goals = array_filter($events, fn ($event) => in_array($event['event_type'] ?? '', ['gol', 'gol_contra', 'penalti_convertido'], true));
        $yellow = array_filter($events, fn ($event) => ($event['event_type'] ?? '') === 'cartao_amarelo');
        $red = array_filter($events, fn ($event) => ($event['event_type'] ?? '') === 'cartao_vermelho');
        return [
            'total_goals' => count($goals),
            'avg_goals' => count($finished) ? round(count($goals) / count($finished), 2) : 0,
            'yellow_cards' => count($yellow),
            'red_cards' => count($red),
            'played_matches' => count($finished),
        ];
    }

    private function authorizedChampionship(int $id): array
    {
        $this->requireAuth('organizer');
        if (!$this->isAdmin() && (new User())->isSuspendedOrganizer(Auth::user()['id'])) {
            http_response_code(403);
            $this->view('errors/403', ['title' => 'Organizador suspenso']);
            exit;
        }
        $championship = (new Championship())->find($id);
        if (!$championship || !(new Championship())->canManage($id, Auth::user()['id'], $this->isAdmin())) {
            http_response_code(403);
            $this->view('errors/403', ['title' => 'Acesso negado']);
            exit;
        }
        return $championship;
    }

    private function isAdmin(): bool
    {
        return (Auth::user()['role'] ?? '') === 'admin';
    }

    private function log(int $championshipId, string $action, string $description): void
    {
        (new CompetitionActivityLog())->create($championshipId, Auth::user()['id'] ?? null, $action, $description);
    }
}

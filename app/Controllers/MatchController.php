<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Athlete;
use App\Models\Championship;
use App\Models\CompetitionMatch;
use App\Models\MatchEvent;
use App\Models\MatchSet;
use App\Models\Team;

class MatchController extends Controller
{
    public function show(string $championshipId, string $matchId): void
    {
        $championship = (new Championship())->find((int) $championshipId);
        $match = (new CompetitionMatch())->findInChampionship((int) $matchId, (int) $championshipId);
        if (!$championship || !$match) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Partida nao encontrada']);
            return;
        }

        $this->view('championships/match', [
            'title' => 'Detalhes da partida',
            'championship' => $championship,
            'match' => $match,
            'matches' => (new CompetitionMatch())->byChampionship((int) $championshipId),
            'eventsByMatch' => (new MatchEvent())->byChampionship((int) $championshipId),
            'setsByMatch' => (new MatchSet())->byChampionship((int) $championshipId),
        ]);
    }

    public function team(string $championshipId, string $teamId): void
    {
        $championship = (new Championship())->find((int) $championshipId);
        $team = (new Team())->findInChampionship((int) $teamId, (int) $championshipId);
        if (!$championship || !$team) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Equipe nao encontrada']);
            return;
        }

        $this->view('championships/team', [
            'title' => $team['name'],
            'championship' => $championship,
            'team' => $team,
            'athletes' => (new Athlete())->byTeam((int) $teamId, (int) $championshipId),
        ]);
    }

    public function athlete(string $championshipId, string $athleteId): void
    {
        $championship = (new Championship())->find((int) $championshipId);
        $athlete = (new Athlete())->findInChampionship((int) $athleteId, (int) $championshipId);
        if (!$championship || !$athlete) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Participante nao encontrado']);
            return;
        }

        $this->view('championships/athlete', [
            'title' => $athlete['name'],
            'championship' => $championship,
            'athlete' => $athlete,
        ]);
    }
}

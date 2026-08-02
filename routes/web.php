<?php

use App\Controllers\AdminController;
use App\Controllers\AthleteController;
use App\Controllers\AuthController;
use App\Controllers\ChampionshipController;
use App\Controllers\CompetitionManagementController;
use App\Controllers\HomeController;
use App\Controllers\HealthController;
use App\Controllers\MatchController;
use App\Controllers\NotificationController;
use App\Controllers\OrganizerController;
use App\Controllers\OrganizerRequestController;

return [
    ['GET', '/', [HomeController::class, 'index']],
    ['GET', '/health', [HealthController::class, 'show']],
    ['GET', '/privacidade', [HomeController::class, 'privacy']],
    ['GET', '/termos', [HomeController::class, 'terms']],
    ['GET', '/campeonatos', [ChampionshipController::class, 'index']],
    ['GET', '/campeonatos/{id}', [ChampionshipController::class, 'show']],
    ['GET', '/campeonatos/{championshipId}/partidas/{matchId}', [MatchController::class, 'show']],
    ['GET', '/campeonatos/{championshipId}/equipes/{teamId}', [MatchController::class, 'team']],
    ['GET', '/campeonatos/{championshipId}/atletas/{athleteId}', [MatchController::class, 'athlete']],
    ['POST', '/campeonatos/{id}/inscrever', [ChampionshipController::class, 'register']],
    ['POST', '/campeonatos/{id}/favoritar', [ChampionshipController::class, 'favorite']],
    ['POST', '/campeonatos/{id}/avaliar', [ChampionshipController::class, 'review']],
    ['POST', '/campeonatos/{id}/denunciar', [ChampionshipController::class, 'report']],
    ['GET', '/calendario', [ChampionshipController::class, 'calendar']],
    ['GET', '/login', [AuthController::class, 'loginForm']],
    ['POST', '/login', [AuthController::class, 'login']],
    ['GET', '/cadastro', [AuthController::class, 'registerForm']],
    ['POST', '/cadastro', [AuthController::class, 'register']],
    ['GET', '/logout', [AuthController::class, 'logout']],
    ['GET', '/notificacoes', [NotificationController::class, 'index']],
    ['POST', '/notificacoes/{id}/ler', [NotificationController::class, 'read']],
    ['GET', '/solicitar-organizador', [OrganizerRequestController::class, 'form']],
    ['POST', '/solicitar-organizador', [OrganizerRequestController::class, 'store']],
    ['GET', '/atleta', [AthleteController::class, 'dashboard']],
    ['POST', '/atleta/perfil', [AthleteController::class, 'updateProfile']],
    ['GET', '/atleta/favoritos', [AthleteController::class, 'favorites']],
    ['GET', '/atleta/historico', [AthleteController::class, 'history']],
    ['GET', '/atleta/recomendacoes', [AthleteController::class, 'recommendations']],
    ['GET', '/organizador', [OrganizerController::class, 'dashboard']],
    ['GET', '/organizador/campeonatos/novo', [OrganizerController::class, 'create']],
    ['POST', '/organizador/campeonatos', [OrganizerController::class, 'store']],
    ['GET', '/organizador/campeonatos/{id}/editar', [OrganizerController::class, 'edit']],
    ['POST', '/organizador/campeonatos/{id}', [OrganizerController::class, 'update']],
    ['POST', '/organizador/campeonatos/{id}/enviar-aprovacao', [OrganizerController::class, 'sendToReview']],
    ['GET', '/organizador/campeonatos/{id}/gerenciar', [CompetitionManagementController::class, 'manage']],
    ['GET', '/organizador/campeonatos/{id}/relatorios/{type}', [CompetitionManagementController::class, 'export']],
    ['GET', '/organizador/campeonatos/{id}/partidas/{matchId}/gerenciar', [CompetitionManagementController::class, 'manageMatch']],
    ['POST', '/organizador/campeonatos/{id}/gerenciar/informacoes', [CompetitionManagementController::class, 'updateInfo']],
    ['POST', '/organizador/campeonatos/{id}/gerenciar/equipes', [CompetitionManagementController::class, 'saveTeam']],
    ['POST', '/organizador/campeonatos/{id}/gerenciar/equipes/{teamId}/excluir', [CompetitionManagementController::class, 'deleteTeam']],
    ['POST', '/organizador/campeonatos/{id}/gerenciar/atletas', [CompetitionManagementController::class, 'saveAthlete']],
    ['POST', '/organizador/campeonatos/{id}/gerenciar/atletas/{athleteId}/excluir', [CompetitionManagementController::class, 'deleteAthlete']],
    ['POST', '/organizador/campeonatos/{id}/gerenciar/jogos', [CompetitionManagementController::class, 'saveMatch']],
    ['POST', '/organizador/campeonatos/{id}/gerenciar/jogos/{matchId}/excluir', [CompetitionManagementController::class, 'deleteMatch']],
    ['POST', '/organizador/campeonatos/{id}/gerenciar/jogos/{matchId}/resultado', [CompetitionManagementController::class, 'recordResult']],
    ['POST', '/organizador/campeonatos/{id}/gerenciar/eventos', [CompetitionManagementController::class, 'saveEvent']],
    ['POST', '/organizador/campeonatos/{id}/gerenciar/eventos/{eventId}/excluir', [CompetitionManagementController::class, 'deleteEvent']],
    ['POST', '/organizador/campeonatos/{id}/gerenciar/sets', [CompetitionManagementController::class, 'saveSet']],
    ['POST', '/organizador/campeonatos/{id}/gerenciar/jogos/{matchId}/sumula', [CompetitionManagementController::class, 'saveMatchReport']],
    ['POST', '/organizador/campeonatos/{id}/gerenciar/classificacao/recalcular', [CompetitionManagementController::class, 'recalculateStandings']],
    ['GET', '/organizador/inscricoes', [OrganizerController::class, 'registrations']],
    ['POST', '/organizador/inscricoes/{id}/status', [OrganizerController::class, 'registrationStatus']],
    ['GET', '/organizador/relatorios/{type}', [OrganizerController::class, 'report']],
    ['GET', '/admin', [AdminController::class, 'dashboard']],
    ['GET', '/admin/solicitacoes-organizadores', [AdminController::class, 'organizerRequests']],
    ['GET', '/admin/solicitacoes-organizadores/{id}', [AdminController::class, 'showOrganizerRequest']],
    ['POST', '/admin/solicitacoes-organizadores/{id}/revisar', [AdminController::class, 'reviewOrganizerRequest']],
    ['GET', '/admin/campeonatos-pendentes', [AdminController::class, 'pendingChampionships']],
    ['POST', '/admin/campeonatos-pendentes/{id}/revisar', [AdminController::class, 'reviewChampionship']],
    ['GET', '/admin/denuncias', [AdminController::class, 'reports']],
    ['POST', '/admin/denuncias/{id}/revisar', [AdminController::class, 'reviewReport']],
    ['GET', '/admin/{resource}', [AdminController::class, 'resource']],
];

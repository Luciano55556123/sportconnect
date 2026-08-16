<?php

use App\Controllers\AdminController;
use App\Controllers\AthleteController;
use App\Controllers\AuthController;
use App\Controllers\ChampionshipController;
use App\Controllers\HomeController;
use App\Controllers\OrganizerController;
use App\Controllers\OrganizerRequestController;

return [
    ['GET', '/', [HomeController::class, 'index']],
    ['GET', '/campeonatos', [ChampionshipController::class, 'index']],
    ['GET', '/campeonatos/{id}', [ChampionshipController::class, 'show']],
    ['POST', '/campeonatos/{id}/inscrever', [ChampionshipController::class, 'register']],
    ['POST', '/campeonatos/{id}/favoritar', [ChampionshipController::class, 'favorite']],
    ['POST', '/campeonatos/{id}/avaliar', [ChampionshipController::class, 'review']],
    ['GET', '/calendario', [ChampionshipController::class, 'calendar']],
    ['GET', '/login', [AuthController::class, 'loginForm']],
    ['POST', '/login', [AuthController::class, 'login']],
    ['GET', '/cadastro', [AuthController::class, 'registerForm']],
    ['POST', '/cadastro', [AuthController::class, 'register']],
    ['GET', '/logout', [AuthController::class, 'logout']],
    ['GET', '/atleta', [AthleteController::class, 'dashboard']],
    ['POST', '/atleta/perfil', [AthleteController::class, 'updateProfile']],
    ['GET', '/atleta/favoritos', [AthleteController::class, 'favorites']],
    ['GET', '/atleta/historico', [AthleteController::class, 'history']],
    ['POST', '/atleta/inscricoes/{id}/comprovante', [AthleteController::class, 'uploadReceipt']],
    ['GET', '/atleta/inscricoes/{id}/comprovante', [AthleteController::class, 'receipt']],
    ['GET', '/atleta/recomendacoes', [AthleteController::class, 'recommendations']],
    ['GET', '/organizador/solicitar', [OrganizerRequestController::class, 'create']],
    ['POST', '/organizador/solicitar', [OrganizerRequestController::class, 'store']],
    ['GET', '/organizador', [OrganizerController::class, 'dashboard']],
    ['GET', '/organizador/campeonatos/novo', [OrganizerController::class, 'create']],
    ['POST', '/organizador/campeonatos', [OrganizerController::class, 'store']],
    ['GET', '/organizador/campeonatos/{id}/gerenciar', [OrganizerController::class, 'manage']],
    ['GET', '/organizador/campeonatos/{championshipId}/partidas/{matchId}/gerenciar', [OrganizerController::class, 'matchManage']],
    ['GET', '/organizador/campeonatos/{id}/editar', [OrganizerController::class, 'edit']],
    ['POST', '/organizador/campeonatos/{id}', [OrganizerController::class, 'update']],
    ['GET', '/organizador/inscricoes', [OrganizerController::class, 'registrations']],
    ['POST', '/organizador/inscricoes/{id}/status', [OrganizerController::class, 'registrationStatus']],
    ['POST', '/organizador/inscricoes/{id}/pagamento', [OrganizerController::class, 'paymentStatus']],
    ['GET', '/organizador/inscricoes/{id}/comprovante', [OrganizerController::class, 'receipt']],
    ['GET', '/organizador/relatorios/{type}', [OrganizerController::class, 'report']],
    ['GET', '/admin', [AdminController::class, 'dashboard']],
    ['GET', '/admin/solicitacoes-organizador', [AdminController::class, 'organizerRequests']],
    ['GET', '/admin/solicitacoes-organizador/{id}', [AdminController::class, 'showOrganizerRequest']],
    ['POST', '/admin/solicitacoes-organizador/{id}/aprovar', [OrganizerRequestController::class, 'approve']],
    ['POST', '/admin/solicitacoes-organizador/{id}/rejeitar', [OrganizerRequestController::class, 'reject']],
    ['GET', '/admin/{resource}', [AdminController::class, 'resource']],
];

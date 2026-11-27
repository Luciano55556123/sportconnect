<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Championship;
use App\Models\Notification;
use App\Models\OrganizerRequest;
use App\Models\Sport;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard(): void
    {
        $this->requireAuth('admin');
        $organizerRequestModel = new OrganizerRequest();
        $this->view('admin/dashboard', [
            'title' => 'Painel administrativo',
            'users' => (new User())->all(),
            'championships' => (new Championship())->search([], 500),
            'sports' => (new Sport())->all(),
            'organizerRequests' => $organizerRequestModel->pending(),
            'pendingOrganizerRequestsCount' => $organizerRequestModel->pendingCount(),
            'notifications' => (new Notification())->forUser((int) Auth::user()['id']),
        ]);
    }

    public function pendingChampionships(): void
    {
        $this->requireAuth('admin');
        $this->view('admin/pending_championships', [
            'title' => 'Campeonatos pendentes',
            'championships' => (new Championship())->search(['editorial_status' => 'pending_review'], 500),
        ]);
    }

    public function reviewChampionship(string $id): void
    {
        $this->requireAuth('admin');
        verify_csrf();
        $decision = (string) ($_POST['editorial_status'] ?? '');
        $reason = trim((string) ($_POST['rejection_reason'] ?? ''));
        if (!in_array($decision, ['published', 'rejected'], true)
            || ($decision === 'rejected' && $reason === '') || strlen($reason) > 1000) {
            flash('error', 'Escolha uma decisão válida e informe o motivo da rejeição.');
            $this->redirect('/admin/campeonatos-pendentes');
        }
        $model = new Championship();
        $championship = $model->find((int) $id);
        if (!$championship || !$model->reviewPublication((int) $id, (int) Auth::user()['id'], $decision, $reason)) {
            flash('error', 'Este campeonato não está aguardando aprovação.');
            $this->redirect('/admin/campeonatos-pendentes');
        }
        try {
            (new Notification())->create((int) $championship['organizer_id'],
                'O campeonato ' . $championship['name'] . ($decision === 'published' ? ' foi aprovado.' : ' foi rejeitado: ' . $reason));
        } catch (\Throwable $exception) {
            error_log('Falha ao notificar revisão de campeonato: ' . $exception->getMessage());
        }
        flash('success', 'Decisão registrada com sucesso.');
        $this->redirect('/admin/campeonatos-pendentes');
    }

    public function organizerRequests(): void
    {
        $this->requireAuth('admin');
        $this->view('admin/organizer_requests', [
            'title' => 'Solicitacoes de Organizador',
            'requests' => (new OrganizerRequest())->all(),
        ]);
    }

    public function showOrganizerRequest(string $id): void
    {
        $this->requireAuth('admin');
        $request = (new OrganizerRequest())->findWithUser((int) $id);
        if (!$request) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Solicitacao nao encontrada']);
            return;
        }

        $this->view('admin/organizer_request_show', [
            'title' => 'Solicitacao de Organizador',
            'request' => $request,
        ]);
    }

    public function resource(string $resource): void
    {
        $this->requireAuth('admin');
        if (!in_array($resource, ['usuarios', 'organizadores', 'campeonatos'], true)) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Modulo nao disponivel']);
            return;
        }
        $this->view('admin/resource', [
            'title' => ucfirst($resource),
            'resource' => $resource,
            'users' => (new User())->all(),
            'championships' => (new Championship())->search([], 500),
        ]);
    }
}

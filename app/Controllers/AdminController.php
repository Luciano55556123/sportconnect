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
        $this->view('admin/resource', [
            'title' => ucfirst($resource),
            'resource' => $resource,
            'users' => (new User())->all(),
            'championships' => (new Championship())->search([], 500),
        ]);
    }
}

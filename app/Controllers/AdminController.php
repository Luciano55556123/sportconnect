<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Championship;
use App\Models\CompetitionActivityLog;
use App\Models\Notification;
use App\Models\OrganizerRequest;
use App\Models\Report;
use App\Models\Sport;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard(): void
    {
        $this->requireAuth('admin');
        $this->view('admin/dashboard', [
            'title' => 'Painel administrativo',
            'users' => (new User())->all(),
            'championships' => (new Championship())->search([], 500, false),
            'sports' => (new Sport())->all(),
            'organizerRequestCounts' => (new OrganizerRequest())->counts(),
            'championshipCounts' => (new Championship())->editorialCounts(),
            'openReports' => (new Report())->openCount(),
        ]);
    }

    public function organizerRequests(): void
    {
        $this->requireAuth('admin');
        $this->view('admin/organizer_requests', [
            'title' => 'Solicitacoes de organizadores',
            'requests' => (new OrganizerRequest())->byStatus($_GET['status'] ?? ''),
        ]);
    }

    public function reviewOrganizerRequest(string $id): void
    {
        $this->requireAuth('admin');
        verify_csrf();
        $status = $_POST['status'] ?? 'pending';
        if (!in_array($status, ['approved', 'rejected', 'suspended'], true)) {
            http_response_code(422);
            flash('error', 'Status invalido.');
            $this->redirect('/admin/solicitacoes-organizadores');
        }
        $request = (new OrganizerRequest())->review((int) $id, $status, Auth::user()['id'], $_POST['rejection_reason'] ?? null);
        if ($request) {
            $messages = [
                'approved' => 'Seu perfil de organizador foi aprovado.',
                'rejected' => 'Sua solicitacao de organizador foi rejeitada.',
                'suspended' => 'Seu perfil de organizador foi suspenso.',
            ];
            (new Notification())->create((int) $request['user_id'], 'Analise de organizador', $messages[$status], '/notificacoes', 'organizer_request');
            (new CompetitionActivityLog())->create(0, Auth::user()['id'], 'organizer_request_' . $status, 'Solicitacao de organizador #' . (int) $id . ' revisada.');
        }
        flash('success', 'Solicitacao atualizada.');
        $this->redirect('/admin/solicitacoes-organizadores');
    }

    public function pendingChampionships(): void
    {
        $this->requireAuth('admin');
        $this->view('admin/pending_championships', [
            'title' => 'Campeonatos pendentes',
            'championships' => (new Championship())->pendingReview(),
        ]);
    }

    public function reviewChampionship(string $id): void
    {
        $this->requireAuth('admin');
        verify_csrf();
        $status = $_POST['editorial_status'] ?? 'published';
        if (!in_array($status, ['published', 'registration_open', 'rejected', 'suspended', 'cancelled'], true)) {
            http_response_code(422);
            flash('error', 'Status invalido.');
            $this->redirect('/admin/campeonatos-pendentes');
        }
        $championship = (new Championship())->reviewPublication((int) $id, $status, Auth::user()['id'], $_POST['rejection_reason'] ?? null);
        if ($championship) {
            (new Notification())->create((int) $championship['organizer_id'], 'Analise de campeonato', 'Seu campeonato foi atualizado para: ' . $status, '/organizador/campeonatos/' . $id . '/gerenciar', 'championship_review');
            (new CompetitionActivityLog())->create((int) $id, Auth::user()['id'], 'championship_' . $status, 'Campeonato revisado pelo administrador.');
        }
        flash('success', 'Campeonato atualizado.');
        $this->redirect('/admin/campeonatos-pendentes');
    }

    public function reports(): void
    {
        $this->requireAuth('admin');
        $this->view('admin/reports', [
            'title' => 'Denuncias',
            'reports' => (new Report())->allForAdmin($_GET['status'] ?? ''),
            'status' => $_GET['status'] ?? '',
        ]);
    }

    public function reviewReport(string $id): void
    {
        $this->requireAuth('admin');
        verify_csrf();
        $status = $_POST['status'] ?? 'under_review';
        $report = (new Report())->review((int) $id, $status, Auth::user()['id'], $_POST['admin_notes'] ?? null);
        if (!$report) {
            flash('error', 'Nao foi possivel atualizar a denuncia.');
            $this->redirect('/admin/denuncias');
        }

        (new Notification())->create((int) $report['reporter_user_id'], 'Denuncia analisada', 'Sua denuncia foi atualizada para: ' . $status, '/notificacoes', 'report');
        if (!empty($report['championship_id'])) {
            (new CompetitionActivityLog())->create((int) $report['championship_id'], Auth::user()['id'], 'report_' . $status, 'Denuncia #' . (int) $id . ' revisada pelo administrador.');
        }
        flash('success', 'Denuncia atualizada.');
        $this->redirect('/admin/denuncias');
    }

    public function resource(string $resource): void
    {
        $this->requireAuth('admin');
        $this->view('admin/resource', [
            'title' => ucfirst($resource),
            'resource' => $resource,
            'users' => (new User())->all(),
            'championships' => (new Championship())->search([], 500, false),
        ]);
    }
}

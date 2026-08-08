<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\OrganizerRequest;
use App\Models\User;

class OrganizerRequestController extends Controller
{
    public function store(): void
    {
        $this->requireAuth('athlete');
        verify_csrf();

        $user = (new User())->find(Auth::user()['id']);
        if (!$user || $user['role'] !== 'athlete') {
            flash('error', 'Somente usuarios comuns podem solicitar perfil de organizador.');
            $this->redirect($user && $user['role'] === 'organizer' ? '/organizador' : '/admin');
        }

        $request = new OrganizerRequest();
        if ($request->hasPendingForUser((int) $user['id'])) {
            flash('error', 'Voce ja possui uma solicitacao pendente.');
            $this->redirect('/atleta');
        }

        if ($request->createForUser((int) $user['id']) === 0) {
            flash('error', 'Voce ja possui uma solicitacao pendente.');
            $this->redirect('/atleta');
        }

        flash('success', 'Solicitacao enviada para analise.');
        $this->redirect('/atleta');
    }

    public function approve(string $id): void
    {
        $this->requireAuth('admin');
        verify_csrf();

        if ((new OrganizerRequest())->approve((int) $id, Auth::user()['id'])) {
            flash('success', 'Solicitacao aprovada. O usuario ja pode acessar o painel do organizador.');
        } else {
            flash('error', 'Solicitacao pendente nao encontrada.');
        }

        $this->redirect('/admin/solicitacoes-organizador');
    }

    public function reject(string $id): void
    {
        $this->requireAuth('admin');
        verify_csrf();

        $reason = trim($_POST['rejection_reason'] ?? '');
        if ($reason === '') {
            flash('error', 'Informe o motivo da rejeicao.');
            $this->redirect('/admin/solicitacoes-organizador/' . $id);
        }

        if ((new OrganizerRequest())->reject((int) $id, $reason)) {
            flash('success', 'Solicitacao rejeitada.');
        } else {
            flash('error', 'Solicitacao pendente nao encontrada.');
        }

        $this->redirect('/admin/solicitacoes-organizador');
    }
}

<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->view('notifications/index', [
            'title' => 'Notificacoes',
            'notifications' => (new Notification())->forUser(Auth::user()['id']),
        ]);
    }

    public function read(string $id): void
    {
        $this->requireAuth();
        verify_csrf();
        (new Notification())->markAsRead((int) $id, Auth::user()['id']);
        flash('success', 'Notificacao marcada como lida.');
        $this->redirect('/notificacoes');
    }
}

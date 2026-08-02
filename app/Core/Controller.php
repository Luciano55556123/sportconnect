<?php

namespace App\Core;

use App\Models\User;

class Controller
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function view(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $config = $this->config;
        $currentUser = Auth::user();
        require BASE_PATH . '/app/Views/layouts/main.php';
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }

    protected function requireAuth(?string $role = null): void
    {
        if (!Auth::check()) {
            flash('error', 'Entre na sua conta para continuar.');
            $this->redirect('/login');
        }

        $freshUser = (new User())->find((int) Auth::user()['id']);
        if (!$freshUser) {
            Auth::logout();
            session_start();
            flash('error', 'Entre novamente para continuar.');
            $this->redirect('/login');
        }
        Auth::refresh($freshUser);

        if ($role !== null && Auth::user()['role'] !== $role && Auth::user()['role'] !== 'admin') {
            error_log('access_denied required=' . $role . ' user=' . Auth::user()['id']);
            http_response_code(403);
            $this->view('errors/403', ['title' => 'Acesso negado']);
            exit;
        }
    }
}

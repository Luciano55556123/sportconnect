<?php

namespace App\Core;

use App\Models\OrganizerRequest;
use PDOException;

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
        $adminPendingOrganizerRequests = 0;
        if ($currentUser && ($currentUser['role'] ?? '') === 'admin') {
            try {
                $adminPendingOrganizerRequests = (new OrganizerRequest())->pendingCount();
            } catch (PDOException $exception) {
                error_log('Erro ao contar solicitacoes de organizador pendentes: ' . $exception->getMessage());
            }
        }
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

        if ($role !== null && !$this->userHasRole($role)) {
            http_response_code(403);
            $this->view('errors/403', ['title' => 'Acesso negado']);
            exit;
        }
    }

    private function userHasRole(string $requiredRole): bool
    {
        $role = Auth::user()['role'] ?? '';
        if ($role === 'admin') {
            return true;
        }

        $aliases = [
            'athlete' => ['athlete', 'atleta'],
            'organizer' => ['organizer', 'organizador'],
            'admin' => ['admin'],
        ];

        return in_array($role, $aliases[$requiredRole] ?? [$requiredRole], true);
    }
}

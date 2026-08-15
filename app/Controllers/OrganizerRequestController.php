<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Notification;
use App\Models\OrganizerRequest;
use App\Models\User;
use PDOException;
use Throwable;

class OrganizerRequestController extends Controller
{
    public function create(): void
    {
        $this->requireAuth();

        $user = (new User())->find(Auth::user()['id']);
        if (!$user || $user['role'] !== 'athlete') {
            flash('error', 'Somente usuarios comuns podem solicitar perfil de organizador.');
            $this->redirect($user && $user['role'] === 'organizer' ? '/organizador' : '/admin');
        }

        $requestModel = new OrganizerRequest();
        $latestRequest = $requestModel->latestForUser((int) $user['id']);
        if ($latestRequest && $latestRequest['status'] === 'pending') {
            flash('error', 'Sua solicitacao esta em analise.');
            $this->redirect('/atleta');
        }

        $this->view('athlete/organizer_request_form', [
            'title' => 'Solicitar perfil de organizador',
            'user' => $user,
            'latestRequest' => $latestRequest,
            'old' => $_SESSION['old_organizer_request'] ?? [],
            'errors' => $_SESSION['organizer_request_errors'] ?? [],
        ]);

        unset($_SESSION['old_organizer_request'], $_SESSION['organizer_request_errors']);
    }

    public function store(): void
    {
        $this->requireAuth();
        verify_csrf();

        $user = (new User())->find(Auth::user()['id']);
        if (!$user || $user['role'] !== 'athlete') {
            flash('error', 'Somente usuarios comuns podem solicitar perfil de organizador.');
            $this->redirect($user && $user['role'] === 'organizer' ? '/organizador' : '/admin');
        }

        $data = $this->validatedData($user);
        if ($data['errors']) {
            $_SESSION['old_organizer_request'] = $data['values'];
            $_SESSION['organizer_request_errors'] = $data['errors'];
            $this->redirect('/organizador/solicitar');
        }

        $request = new OrganizerRequest();
        if ($request->hasPendingForUser((int) $user['id'])) {
            flash('error', 'Voce ja possui uma solicitacao pendente.');
            $this->redirect('/atleta');
        }

        try {
            $createdId = $request->createForUser((int) $user['id'], $data['values']);
        } catch (PDOException $exception) {
            error_log('Erro ao criar solicitacao de organizador: ' . $exception->getMessage());
            flash('error', 'Nao foi possivel enviar sua solicitacao. Tente novamente.');
            $_SESSION['old_organizer_request'] = $data['values'];
            $this->redirect('/organizador/solicitar');
        }

        if ($createdId === 0) {
            flash('error', 'Voce ja possui uma solicitacao pendente.');
            $this->redirect('/atleta');
        }

        $this->notifyAdminsAboutNewRequest($user, $createdId);

        flash('success', 'Solicitacao enviada para analise.');
        $this->redirect('/atleta');
    }

    public function approve(string $id): void
    {
        $this->requireAuth('admin');
        verify_csrf();

        $requestModel = new OrganizerRequest();
        $request = $requestModel->findWithUser((int) $id);

        try {
            $approved = $requestModel->approve((int) $id, Auth::user()['id']);
        } catch (PDOException $exception) {
            error_log('Erro ao aprovar solicitacao de organizador: ' . $exception->getMessage());
            flash('error', 'Nao foi possivel aprovar a solicitacao. Tente novamente.');
            $this->redirect('/admin/solicitacoes-organizador/' . $id);
        }

        if ($approved) {
            if ($request) {
                $this->notifyApplicant(
                    (int) $request['user_id'],
                    'Sua solicitacao para se tornar organizador foi aprovada.',
                    'Solicitacao aprovada',
                    '/organizador',
                    'organizer_request_approved'
                );
            }
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

        $requestModel = new OrganizerRequest();
        $request = $requestModel->findWithUser((int) $id);

        try {
            $rejected = $requestModel->reject((int) $id, $reason, Auth::user()['id']);
        } catch (PDOException $exception) {
            error_log('Erro ao rejeitar solicitacao de organizador: ' . $exception->getMessage());
            flash('error', 'Nao foi possivel rejeitar a solicitacao. Tente novamente.');
            $this->redirect('/admin/solicitacoes-organizador/' . $id);
        }

        if ($rejected) {
            if ($request) {
                $message = 'Sua solicitacao para se tornar organizador foi rejeitada.';
                if ($reason !== '') {
                    $message .= ' Motivo: ' . $reason;
                }
                $this->notifyApplicant(
                    (int) $request['user_id'],
                    $message,
                    'Solicitacao rejeitada',
                    '/atleta',
                    'organizer_request_rejected'
                );
            }
            flash('success', 'Solicitacao rejeitada.');
        } else {
            flash('error', 'Solicitacao pendente nao encontrada.');
        }

        $this->redirect('/admin/solicitacoes-organizador');
    }

    private function validatedData(array $user): array
    {
        $values = [
            'responsible_name' => trim((string) ($_POST['responsible_name'] ?? $user['name'] ?? '')),
            'document' => trim((string) ($_POST['document'] ?? '')),
            'organization_name' => trim((string) ($_POST['organization_name'] ?? '')),
            'organization_type' => trim((string) ($_POST['organization_type'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? $user['phone'] ?? '')),
            'whatsapp' => trim((string) ($_POST['whatsapp'] ?? $user['phone'] ?? '')),
            'contact_email' => trim((string) ($_POST['contact_email'] ?? $user['email'] ?? '')),
            'city' => trim((string) ($_POST['city'] ?? $user['city'] ?? '')),
            'state' => strtoupper(trim((string) ($_POST['state'] ?? ''))),
            'experience' => trim((string) ($_POST['experience'] ?? '')),
            'request_reason' => trim((string) ($_POST['request_reason'] ?? '')),
        ];

        $errors = [];
        foreach (['responsible_name', 'document', 'organization_name', 'organization_type', 'phone', 'whatsapp', 'contact_email', 'city', 'state', 'experience', 'request_reason'] as $field) {
            if ($values[$field] === '') {
                $errors[$field] = 'Campo obrigatorio.';
            }
        }

        if ($values['contact_email'] !== '' && !filter_var($values['contact_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['contact_email'] = 'Informe um e-mail valido.';
        }

        if ($values['state'] !== '' && !preg_match('/^[A-Z]{2}$/', $values['state'])) {
            $errors['state'] = 'Informe a UF com 2 letras.';
        }

        $documentDigits = preg_replace('/\D/', '', $values['document']);
        if ($values['document'] !== '' && !in_array(strlen($documentDigits), [11, 14], true)) {
            $errors['document'] = 'Informe um CPF ou CNPJ valido.';
        }

        if ($values['phone'] !== '' && strlen(preg_replace('/\D/', '', $values['phone'])) < 10) {
            $errors['phone'] = 'Informe um telefone valido.';
        }

        if ($values['whatsapp'] !== '' && strlen(preg_replace('/\D/', '', $values['whatsapp'])) < 10) {
            $errors['whatsapp'] = 'Informe um WhatsApp valido.';
        }

        return ['values' => $values, 'errors' => $errors];
    }

    private function notifyAdminsAboutNewRequest(array $user, int $requestId): void
    {
        try {
            (new Notification())->createForAdmins(
                ($user['name'] ?? 'Um usuario') . ' solicitou permissao para se tornar organizador.',
                'Nova solicitacao de organizador',
                '/admin/solicitacoes-organizador/' . $requestId,
                'organizer_request'
            );
        } catch (Throwable $exception) {
            error_log('Erro ao notificar admins sobre solicitacao de organizador: ' . $exception->getMessage());
        }
    }

    private function notifyApplicant(int $userId, string $message, string $title, string $link, string $type): void
    {
        try {
            (new Notification())->create($userId, $message, $title, $link, $type);
        } catch (Throwable $exception) {
            error_log('Erro ao notificar usuario sobre solicitacao de organizador: ' . $exception->getMessage());
        }
    }
}

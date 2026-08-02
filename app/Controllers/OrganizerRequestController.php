<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Security;
use App\Models\Notification;
use App\Models\OrganizerRequest;
use App\Models\User;

class OrganizerRequestController extends Controller
{
    public function form(): void
    {
        $this->requireAuth('athlete');
        $this->view('organizer_requests/form', [
            'title' => 'Solicitar perfil de organizador',
            'latestRequest' => (new OrganizerRequest())->latestForUser(Auth::user()['id']),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth('athlete');
        verify_csrf();
        Security::rateLimit('organizer_request', 3, 3600);
        $user = (new User())->find(Auth::user()['id']);
        if (!$user || $user['role'] !== 'athlete') {
            flash('error', 'Somente usuarios comuns podem solicitar perfil de organizador.');
            $this->redirect($user && $user['role'] === 'organizer' ? '/organizador' : '/admin');
        }

        $latest = (new OrganizerRequest())->latestForUser(Auth::user()['id']);
        if ($latest && in_array($latest['status'], ['pending', 'approved'], true)) {
            flash('error', 'Ja existe uma solicitacao ativa para sua conta.');
            $this->redirect('/solicitar-organizador');
        }

        $proof = $this->uploadProof('proof_file');
        $requestId = (new OrganizerRequest())->create($_POST, Auth::user()['id'], $proof);
        if ($requestId === 0) {
            flash('error', 'Voce ja possui uma solicitacao pendente.');
            $this->redirect('/solicitar-organizador');
        }

        (new Notification())->create(Auth::user()['id'], 'Solicitacao enviada', 'Sua solicitacao de organizador foi enviada para analise.', '/solicitar-organizador', 'organizer_request');
        foreach ((new User())->all('admin') as $admin) {
            (new Notification())->create((int) $admin['id'], 'Novo organizador para analisar', 'Ha uma nova solicitacao de perfil de organizador.', '/admin/solicitacoes-organizadores', 'organizer_request');
        }
        flash('success', 'Solicitacao enviada para analise do administrador.');
        $this->redirect('/solicitar-organizador');
    }

    private function uploadProof(string $field): ?string
    {
        if (empty($_FILES[$field]['name'])) {
            return null;
        }
        if ((int) $_FILES[$field]['size'] > 5 * 1024 * 1024) {
            error_log('upload_rejected reason=size field=' . $field . ' user=' . (Auth::user()['id'] ?? 'guest'));
            flash('error', 'O comprovante deve ter no maximo 5 MB.');
            return null;
        }
        $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowed, true)) {
            error_log('upload_rejected reason=extension field=' . $field . ' user=' . (Auth::user()['id'] ?? 'guest'));
            flash('error', 'Envie PDF, JPG ou PNG.');
            return null;
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($_FILES[$field]['tmp_name']);
        $valid = match ($ext) {
            'pdf' => $mime === 'application/pdf',
            'jpg', 'jpeg' => in_array($mime, ['image/jpeg', 'image/pjpeg'], true),
            'png' => $mime === 'image/png',
            default => false,
        };
        if (!$valid) {
            error_log('upload_rejected reason=mime field=' . $field . ' user=' . (Auth::user()['id'] ?? 'guest'));
            flash('error', 'Arquivo de comprovante invalido.');
            return null;
        }
        $dir = BASE_PATH . '/storage/organizer_requests';
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            flash('error', 'Nao foi possivel preparar o armazenamento.');
            return null;
        }
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dir . '/' . $filename)) {
            flash('error', 'Nao foi possivel salvar o comprovante.');
            return null;
        }
        return 'storage/organizer_requests/' . $filename;
    }
}

<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Championship;
use App\Models\Notification;
use App\Models\Registration;
use App\Models\Sport;
use App\Models\User;

class OrganizerController extends Controller
{
    public function dashboard(): void
    {
        $this->requireAuth('organizer');
        $this->ensureOrganizerActive();
        $this->view('organizer/dashboard', [
            'title' => 'Painel do organizador',
            'stats' => (new Registration())->statsForOrganizer(Auth::user()['id']),
            'championships' => (new Championship())->byOrganizer(Auth::user()['id']),
        ]);
    }

    public function create(): void
    {
        $this->requireAuth('organizer');
        $this->ensureOrganizerActive();
        $this->view('organizer/form', ['title' => 'Novo campeonato', 'sports' => (new Sport())->all(), 'championship' => null]);
    }

    public function store(): void
    {
        $this->requireAuth('organizer');
        $this->ensureOrganizerActive();
        verify_csrf();
        $whatsapp = $this->sanitizeWhatsapp($_POST['whatsapp_contato'] ?? '');
        if (!$this->isValidWhatsapp($whatsapp)) {
            flash('error', 'Informe um WhatsApp brasileiro valido com DDD.');
            $this->view('organizer/form', [
                'title' => 'Novo campeonato',
                'sports' => (new Sport())->all(),
                'championship' => $_POST,
            ]);
            return;
        }
        $_POST['whatsapp_contato'] = $whatsapp;
        try {
            $_POST['imagem'] = $this->uploadChampionshipImage('imagem');
        } catch (\RuntimeException $exception) {
            flash('error', $exception->getMessage());
            $this->view('organizer/form', [
                'title' => 'Novo campeonato',
                'sports' => (new Sport())->all(),
                'championship' => $_POST,
            ]);
            return;
        }
        $_POST['image'] = $_POST['imagem'] ?? 'assets/images/campeonato-placeholder.jpg';
        $_POST['rules_file'] = $this->upload('rules_file', ['pdf']);
        $id = (new Championship())->create($_POST, Auth::user()['id']);
        (new Notification())->createForFavoriteSport((int) $_POST['sport_id'], 'Novo campeonato disponivel: ' . $_POST['name']);
        flash('success', 'Campeonato cadastrado e atletas interessados notificados.');
        $this->redirect('/campeonatos/' . $id);
    }

    public function edit(string $id): void
    {
        $this->requireAuth('organizer');
        $this->ensureOrganizerActive();
        $model = new Championship();
        $championship = $model->find((int) $id);
        if (!$championship || !$model->canManage((int) $id, Auth::user()['id'], $this->isAdmin())) {
            http_response_code(403);
            $this->view('errors/403', ['title' => 'Acesso negado']);
            return;
        }

        $this->view('organizer/form', [
            'title' => 'Editar campeonato',
            'sports' => (new Sport())->all(),
            'championship' => $championship,
        ]);
    }

    public function update(string $id): void
    {
        $this->requireAuth('organizer');
        $this->ensureOrganizerActive();
        verify_csrf();
        $model = new Championship();
        $championship = $model->find((int) $id);
        if (!$championship || !$model->canManage((int) $id, Auth::user()['id'], $this->isAdmin())) {
            http_response_code(403);
            $this->view('errors/403', ['title' => 'Acesso negado']);
            return;
        }

        $whatsapp = $this->sanitizeWhatsapp($_POST['whatsapp_contato'] ?? '');
        if (!$this->isValidWhatsapp($whatsapp)) {
            flash('error', 'Informe um WhatsApp brasileiro valido com DDD.');
            $_POST['id'] = (int) $id;
            $_POST['image'] = $_POST['current_image'] ?? ($championship['image'] ?? 'assets/img/default-event.svg');
            $_POST['imagem'] = $_POST['current_imagem'] ?? ($championship['imagem'] ?? null);
            $_POST['rules_file'] = $_POST['current_rules_file'] ?? ($championship['rules_file'] ?? null);
            $this->view('organizer/form', [
                'title' => 'Editar campeonato',
                'sports' => (new Sport())->all(),
                'championship' => array_merge($championship, $_POST),
            ]);
            return;
        }

        $_POST['whatsapp_contato'] = $whatsapp;
        $_POST['is_admin'] = $this->isAdmin();
        try {
            $newImage = $this->uploadChampionshipImage('imagem');
        } catch (\RuntimeException $exception) {
            flash('error', $exception->getMessage());
            $_POST['id'] = (int) $id;
            $_POST['image'] = $_POST['current_image'] ?? ($championship['image'] ?? 'assets/img/default-event.svg');
            $_POST['imagem'] = $_POST['current_imagem'] ?? ($championship['imagem'] ?? null);
            $_POST['rules_file'] = $_POST['current_rules_file'] ?? ($championship['rules_file'] ?? null);
            $this->view('organizer/form', [
                'title' => 'Editar campeonato',
                'sports' => (new Sport())->all(),
                'championship' => array_merge($championship, $_POST),
            ]);
            return;
        }

        $_POST['imagem'] = $newImage ?? ($_POST['current_imagem'] ?? ($championship['imagem'] ?? null));
        $_POST['image'] = $_POST['imagem'] ?? ($_POST['current_image'] ?? 'assets/images/campeonato-placeholder.jpg');
        $_POST['rules_file'] = $this->upload('rules_file', ['pdf']) ?? ($_POST['current_rules_file'] ?? null);
        $model->update((int) $id, $_POST, Auth::user()['id']);
        if ($newImage) {
            $this->deleteChampionshipImage($championship['imagem'] ?? null);
        }
        flash('success', 'Campeonato atualizado.');
        $this->redirect('/organizador');
    }

    public function sendToReview(string $id): void
    {
        $this->requireAuth('organizer');
        $this->ensureOrganizerActive();
        verify_csrf();
        $sent = (new Championship())->sendToReview((int) $id, Auth::user()['id']);
        if ($sent) {
            foreach ((new User())->all('admin') as $admin) {
                (new Notification())->create((int) $admin['id'], 'Campeonato para aprovacao', 'Um campeonato foi enviado para revisao administrativa.', '/admin/campeonatos-pendentes', 'championship_review');
            }
        }
        flash($sent ? 'success' : 'error', $sent ? 'Campeonato enviado para aprovacao administrativa.' : 'Nao foi possivel enviar. Verifique campos obrigatorios ou status atual.');
        $this->redirect('/organizador');
    }

    public function registrations(): void
    {
        $this->requireAuth('organizer');
        $this->view('organizer/registrations', [
            'title' => 'Inscricoes recebidas',
            'registrations' => (new Registration())->byOrganizer(Auth::user()['id'], $this->isAdmin()),
        ]);
    }

    public function registrationStatus(string $id): void
    {
        $this->requireAuth('organizer');
        verify_csrf();
        (new Registration())->setStatus((int) $id, $_POST['status'], Auth::user()['id'], $this->isAdmin());
        flash('success', 'Status atualizado.');
        $this->redirect('/organizador/inscricoes');
    }

    public function report(string $type): void
    {
        $this->requireAuth('organizer');
        $rows = (new Registration())->byOrganizer(Auth::user()['id'], $this->isAdmin());
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="relatorio-' . preg_replace('/[^a-z0-9_-]/i', '', $type) . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Campeonato', 'Nome', 'Email', 'Cidade', 'Status', 'Valor']);
        foreach ($rows as $row) {
            fputcsv($out, [$row['championship_name'], $row['name'], $row['email'], $row['city'], $row['status'], $row['registration_fee']]);
        }
        fclose($out);
    }

    private function upload(string $field, array $allowed): ?string
    {
        if (empty($_FILES[$field]['name'])) {
            return null;
        }
        $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            flash('error', 'Arquivo invalido em ' . $field . '.');
            return null;
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES[$field]['tmp_name']);
        $validMime = match ($ext) {
            'pdf' => $mime === 'application/pdf',
            'jpg', 'jpeg' => in_array($mime, ['image/jpeg', 'image/pjpeg'], true),
            'png' => $mime === 'image/png',
            default => true,
        };
        if (!$validMime) {
            flash('error', 'O conteudo do arquivo nao corresponde ao formato informado.');
            return null;
        }
        $name = uniqid($field . '_', true) . '.' . $ext;
        move_uploaded_file($_FILES[$field]['tmp_name'], BASE_PATH . '/uploads/' . $name);
        return 'uploads/' . $name;
    }

    private function uploadChampionshipImage(string $field): ?string
    {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Nao foi possivel enviar a imagem. Tente novamente.');
        }

        if ((int) $_FILES[$field]['size'] > 5 * 1024 * 1024) {
            throw new \RuntimeException('A imagem deve ter no maximo 5 MB.');
        }

        $tmpName = $_FILES[$field]['tmp_name'];
        if (!is_uploaded_file($tmpName)) {
            throw new \RuntimeException('Upload de imagem invalido.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpName);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($allowed[$mime])) {
            throw new \RuntimeException('Envie uma imagem JPG, PNG ou WEBP valida.');
        }

        $originalExtension = strtolower(pathinfo($_FILES[$field]['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($originalExtension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw new \RuntimeException('A extensao da imagem deve ser JPG, JPEG, PNG ou WEBP.');
        }

        $directory = BASE_PATH . '/public/uploads/campeonatos';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException('Nao foi possivel preparar a pasta de uploads.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
        $target = $directory . '/' . $filename;

        if (!move_uploaded_file($tmpName, $target)) {
            throw new \RuntimeException('Nao foi possivel salvar a imagem enviada.');
        }

        return 'uploads/campeonatos/' . $filename;
    }

    private function deleteChampionshipImage(?string $path): void
    {
        if (!$path || strpos($path, 'uploads/campeonatos/') !== 0) {
            return;
        }

        $directory = realpath(BASE_PATH . '/public/uploads/campeonatos');
        $file = realpath(BASE_PATH . '/public/' . $path);
        if ($directory && $file && strpos($file, $directory) === 0 && is_file($file)) {
            unlink($file);
        }
    }

    private function sanitizeWhatsapp(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if (strlen($digits) > 11 && substr($digits, 0, 2) === '55') {
            $digits = substr($digits, 2);
        }

        return $digits;
    }

    private function isValidWhatsapp(string $value): bool
    {
        return (bool) preg_match('/^[1-9]{2}9?\d{8}$/', $value);
    }

    private function isAdmin(): bool
    {
        return (Auth::user()['role'] ?? '') === 'admin';
    }

    private function ensureOrganizerActive(): void
    {
        if (!$this->isAdmin() && (new User())->isSuspendedOrganizer(Auth::user()['id'])) {
            http_response_code(403);
            $this->view('errors/403', ['title' => 'Organizador suspenso']);
            exit;
        }
    }
}

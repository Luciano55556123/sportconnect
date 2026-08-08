<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Championship;
use App\Models\Notification;
use App\Models\Registration;
use App\Models\RegistrationPayment;
use App\Models\Sport;
use App\Services\RegistrationEmailService;

class OrganizerController extends Controller
{
    public function dashboard(): void
    {
        $this->requireAuth('organizer');
        $this->view('organizer/dashboard', [
            'title' => 'Painel do organizador',
            'stats' => (new Registration())->statsForOrganizer(Auth::user()['id']),
            'championships' => (new Championship())->byOrganizer(Auth::user()['id']),
        ]);
    }

    public function create(): void
    {
        $this->requireAuth('organizer');
        $this->view('organizer/form', [
            'title' => 'Novo campeonato',
            'sports' => (new Sport())->all(),
            'championship' => [
                'email_contato' => Auth::user()['email'] ?? '',
                'whatsapp_contato' => '',
            ],
        ]);
    }

    public function store(): void
    {
        $this->requireAuth('organizer');
        verify_csrf();
        $this->normalizePaymentData($_POST);
        if (!$this->validateChampionshipPayment($_POST)) {
            $this->redirect('/organizador/campeonatos/novo');
        }
        $_POST['image'] = $this->upload('image', ['jpg', 'jpeg', 'png', 'webp']) ?? 'assets/img/default-event.svg';
        $_POST['rules_file'] = $this->upload('rules_file', ['pdf']);
        $id = (new Championship())->create($_POST, Auth::user()['id']);
        (new Notification())->createForFavoriteSport((int) $_POST['sport_id'], 'Novo campeonato disponivel: ' . $_POST['name']);
        flash('success', 'Campeonato cadastrado e atletas interessados notificados.');
        $this->redirect('/campeonatos/' . $id);
    }

    public function edit(string $id): void
    {
        $this->requireAuth('organizer');
        $this->view('organizer/form', [
            'title' => 'Editar campeonato',
            'sports' => (new Sport())->all(),
            'championship' => (new Championship())->find((int) $id),
        ]);
    }

    public function update(string $id): void
    {
        $this->requireAuth('organizer');
        verify_csrf();
        $this->normalizePaymentData($_POST);
        if (!$this->validateChampionshipPayment($_POST)) {
            $this->redirect('/organizador/campeonatos/' . $id . '/editar');
        }
        $_POST['image'] = $this->upload('image', ['jpg', 'jpeg', 'png', 'webp']) ?? ($_POST['current_image'] ?? 'assets/img/default-event.svg');
        $_POST['rules_file'] = $this->upload('rules_file', ['pdf']) ?? ($_POST['current_rules_file'] ?? null);
        (new Championship())->update((int) $id, $_POST, Auth::user()['id']);
        flash('success', 'Campeonato atualizado.');
        $this->redirect('/organizador');
    }

    public function registrations(): void
    {
        $this->requireAuth('organizer');
        $this->view('organizer/registrations', [
            'title' => 'Inscricoes recebidas',
            'registrations' => (new Registration())->byOrganizer(Auth::user()['id']),
        ]);
    }

    public function registrationStatus(string $id): void
    {
        $this->requireAuth('organizer');
        verify_csrf();
        (new Registration())->setStatus((int) $id, $_POST['status'], Auth::user()['id']);
        flash('success', 'Status atualizado.');
        $this->redirect('/organizador/inscricoes');
    }

    public function paymentStatus(string $id): void
    {
        $this->requireAuth('organizer');
        verify_csrf();
        $action = $_POST['action'] ?? '';
        $notes = trim((string) ($_POST['review_notes'] ?? ''));
        $paymentModel = new RegistrationPayment();

        if ($action === 'approve') {
            $ok = $paymentModel->review((int) $id, Auth::user()['id'], 'paid', 'confirmada', $notes);
        } elseif ($action === 'reject') {
            $ok = $paymentModel->review((int) $id, Auth::user()['id'], 'rejected', 'pagamento_rejeitado', $notes);
        } else {
            $ok = false;
        }

        if ($ok) {
            $registration = (new Registration())->findDetails((int) $id);
            if ($registration) {
                (new RegistrationEmailService())->paymentReviewedToAthlete($registration, $action === 'approve');
            }
            flash('success', 'Pagamento atualizado.');
        } else {
            flash('error', 'Nao foi possivel atualizar este pagamento.');
        }

        $this->redirect('/organizador/inscricoes');
    }

    public function receipt(string $id): void
    {
        $this->requireAuth('organizer');
        $payment = (new RegistrationPayment())->findForOrganizer((int) $id, Auth::user()['id']);
        if (!$payment || empty($payment['receipt_path'])) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Comprovante nao encontrado']);
            return;
        }
        $this->downloadUpload($payment['receipt_path']);
    }

    public function report(string $type): void
    {
        $this->requireAuth('organizer');
        $rows = (new Registration())->byOrganizer(Auth::user()['id']);
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
        $name = uniqid($field . '_', true) . '.' . $ext;
        move_uploaded_file($_FILES[$field]['tmp_name'], BASE_PATH . '/uploads/' . $name);
        return 'uploads/' . $name;
    }

    private function validateChampionshipPayment(array $data): bool
    {
        $email = trim((string) ($data['email_contato'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Informe um e-mail valido para receber as inscricoes.');
            return false;
        }

        if (strlen(preg_replace('/\D/', '', (string) ($data['whatsapp_contato'] ?? ''))) < 10) {
            flash('error', 'Informe um WhatsApp de contato valido.');
            return false;
        }

        $requiresPayment = !empty($data['requires_payment']);
        $fee = (float) ($data['registration_fee'] ?? 0);
        if (!$requiresPayment && $fee <= 0) {
            return true;
        }

        foreach (['pix_key', 'pix_key_type', 'pix_holder_name'] as $field) {
            if (trim((string) ($data[$field] ?? '')) === '') {
                flash('error', 'Preencha os dados PIX para campeonatos pagos.');
                return false;
            }
        }

        if ($fee <= 0) {
            flash('error', 'Informe um valor de inscricao maior que zero para campeonatos pagos.');
            return false;
        }

        return true;
    }

    private function normalizePaymentData(array &$data): void
    {
        if (!empty($data['requires_payment'])) {
            return;
        }

        $data['registration_fee'] = 0;
        $data['pix_key'] = '';
        $data['pix_key_type'] = '';
        $data['pix_holder_name'] = '';
        $data['pix_instructions'] = '';
    }

    private function downloadUpload(string $path): void
    {
        $base = realpath(BASE_PATH . '/uploads');
        $file = realpath(BASE_PATH . '/' . $path);
        if (!$base || !$file || !str_starts_with($file, $base) || !is_file($file)) {
            http_response_code(404);
            exit('Arquivo nao encontrado.');
        }

        header('Content-Type: ' . (mime_content_type($file) ?: 'application/octet-stream'));
        header('Content-Disposition: inline; filename="' . basename($file) . '"');
        readfile($file);
        exit;
    }
}

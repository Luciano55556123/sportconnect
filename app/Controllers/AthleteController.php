<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Favorite;
use App\Models\Notification;
use App\Models\OrganizerRequest;
use App\Models\Recommendation;
use App\Models\Registration;
use App\Models\RegistrationPayment;
use App\Models\Sport;
use App\Models\User;
use App\Services\PixService;
use App\Services\QrCodeService;
use App\Services\RegistrationEmailService;

class AthleteController extends Controller
{
    public function dashboard(): void
    {
        $this->requireAuth('athlete');
        $user = (new User())->find(Auth::user()['id']);
        $this->view('athlete/dashboard', [
            'title' => 'Painel do atleta',
            'user' => $user,
            'sports' => (new Sport())->all(),
            'favoriteSports' => (new User())->favoriteSportIds(Auth::user()['id']),
            'notifications' => (new Notification())->forUser(Auth::user()['id']),
            'recommendations' => (new Recommendation())->forUser($user),
            'organizerRequest' => (new OrganizerRequest())->latestForUser(Auth::user()['id']),
        ]);
    }

    public function updateProfile(): void
    {
        $this->requireAuth('athlete');
        verify_csrf();
        $model = new User();
        $model->updateProfile(Auth::user()['id'], $_POST);
        $model->syncFavoriteSports(Auth::user()['id'], $_POST['sports'] ?? []);
        flash('success', 'Perfil atualizado.');
        $this->redirect('/atleta');
    }

    public function favorites(): void
    {
        $this->requireAuth('athlete');
        $this->view('athlete/favorites', [
            'title' => 'Meus favoritos',
            'favorites' => (new Favorite())->byUser(Auth::user()['id']),
        ]);
    }

    public function history(): void
    {
        $this->requireAuth('athlete');
        $registrations = (new Registration())->byUser(Auth::user()['id']);
        $this->attachPixCodes($registrations);
        $this->view('athlete/history', [
            'title' => 'Historico',
            'registrations' => $registrations,
        ]);
    }

    public function uploadReceipt(string $id): void
    {
        $this->requireAuth('athlete');
        verify_csrf();
        $paymentModel = new RegistrationPayment();
        $payment = $paymentModel->findForAthlete((int) $id, Auth::user()['id']);
        if (!$payment) {
            flash('error', 'Pagamento nao encontrado para esta inscricao.');
            $this->redirect('/atleta/historico');
        }

        $path = $this->uploadReceiptFile('receipt_file');
        if ($path === null) {
            $this->redirect('/atleta/historico');
        }

        if ($paymentModel->submitReceipt((int) $id, Auth::user()['id'], $path)) {
            $registration = (new Registration())->findDetails((int) $id);
            if ($registration) {
                (new RegistrationEmailService())->receiptToOrganizer($registration);
                (new Notification())->create((int) $registration['organizer_id'], 'Comprovante recebido em ' . $registration['championship_name'] . '.');
            }
            flash('success', 'Comprovante recebido. Pagamento em analise.');
        } else {
            flash('error', 'Nao foi possivel enviar o comprovante.');
        }

        $this->redirect('/atleta/historico');
    }

    public function receipt(string $id): void
    {
        $this->requireAuth('athlete');
        $payment = (new RegistrationPayment())->findForAthlete((int) $id, Auth::user()['id']);
        if (!$payment || empty($payment['receipt_path'])) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Comprovante nao encontrado']);
            return;
        }
        $this->downloadUpload($payment['receipt_path']);
    }

    public function recommendations(): void
    {
        $this->requireAuth('athlete');
        $user = (new User())->find(Auth::user()['id']);
        $this->view('athlete/recommendations', [
            'title' => 'Recomendacoes inteligentes',
            'recommendations' => (new Recommendation())->forUser($user),
        ]);
    }

    private function uploadReceiptFile(string $field): ?string
    {
        if (empty($_FILES[$field]['name'])) {
            flash('error', 'Selecione um comprovante para enviar.');
            return null;
        }

        if (($_FILES[$field]['size'] ?? 0) > 5 * 1024 * 1024) {
            flash('error', 'O comprovante deve ter no maximo 5 MB.');
            return null;
        }

        $allowed = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ];
        $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        $mime = mime_content_type($_FILES[$field]['tmp_name']);

        if (!isset($allowed[$ext]) || $allowed[$ext] !== $mime) {
            flash('error', 'Formato de comprovante invalido.');
            return null;
        }

        $name = uniqid('receipt_', true) . '.' . $ext;
        if (!move_uploaded_file($_FILES[$field]['tmp_name'], BASE_PATH . '/uploads/' . $name)) {
            flash('error', 'Nao foi possivel salvar o comprovante.');
            return null;
        }

        return 'uploads/' . $name;
    }

    private function attachPixCodes(array &$registrations): void
    {
        $pix = new PixService();
        $qr = new QrCodeService();

        foreach ($registrations as &$registration) {
            $paid = !empty($registration['requires_payment']) && (float) ($registration['registration_fee'] ?? 0) > 0;
            if (!$paid || empty($registration['pix_key'])) {
                continue;
            }

            try {
                $payload = $pix->payload([
                    'pix_key' => $registration['pix_key'],
                    'pix_key_type' => $registration['pix_key_type'] ?? '',
                    'pix_holder_name' => $registration['pix_holder_name'] ?? '',
                    'pix_receiver_city' => $registration['pix_receiver_city'] ?? $registration['city'] ?? '',
                    'amount' => (float) ($registration['payment_amount'] ?? $registration['registration_fee']),
                    'txid' => 'REG' . (int) $registration['id'],
                ]);

                if (!$this->isValidPixPayload($payload)) {
                    error_log('Payload PIX invalido para inscricao ' . ($registration['id'] ?? '') . ': ' . $payload);
                    continue;
                }

                $displayPayload = $payload;
                $qrPayload = $payload;

                if (!hash_equals($displayPayload, $qrPayload)) {
                    error_log('Payload PIX divergente entre exibicao e QR Code na inscricao ' . ($registration['id'] ?? ''));
                    continue;
                }

                $registration['pix_payload'] = $displayPayload;
                $registration['pix_qr'] = $qr->dataUri($qrPayload);
            } catch (\Throwable $exception) {
                error_log('Erro ao gerar QR Code PIX da inscricao ' . ($registration['id'] ?? '') . ': ' . $exception->getMessage());
            }
        }
        unset($registration);
    }

    private function isValidPixPayload(string $payload): bool
    {
        return $payload !== '' && preg_match('/^000201.*6304[0-9A-F]{4}$/', $payload) === 1;
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

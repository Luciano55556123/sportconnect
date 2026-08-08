<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Championship;
use App\Models\Favorite;
use App\Models\Notification;
use App\Models\Registration;
use App\Models\RegistrationPayment;
use App\Models\Sport;
use App\Services\RegistrationEmailService;

class ChampionshipController extends Controller
{
    public function index(): void
    {
        $this->view('championships/index', [
            'title' => 'Pesquisar campeonatos',
            'sports' => (new Sport())->all(),
            'championships' => (new Championship())->search($_GET),
            'filters' => $_GET,
        ]);
    }

    public function show(string $id): void
    {
        $model = new Championship();
        $championship = $model->find((int) $id);
        if (!$championship) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Campeonato nao encontrado']);
            return;
        }
        $model->incrementViews((int) $id);
        $this->view('championships/show', [
            'title' => $championship['name'],
            'championship' => $championship,
            'reviews' => $model->reviews((int) $id),
        ]);
    }

    public function register(string $id): void
    {
        $this->requireAuth('athlete');
        verify_csrf();
        $championshipModel = new Championship();
        $championship = $championshipModel->find((int) $id);
        if (!$championship) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Campeonato nao encontrado']);
            return;
        }

        if ((new Registration())->existsForUser((int) $id, Auth::user()['id'])) {
            flash('error', 'Voce ja esta inscrito neste campeonato.');
            $this->redirect('/campeonatos/' . $id);
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Informe um e-mail valido.');
            $this->redirect('/campeonatos/' . $id);
        }

        $proof = $this->upload('proof_file', ['pdf', 'jpg', 'jpeg', 'png']);
        $isPaid = !empty($championship['requires_payment']) || (float) ($championship['registration_fee'] ?? 0) > 0;
        $db = Database::connection();
        $registrationModel = new Registration();
        $paymentModel = new RegistrationPayment();

        $db->beginTransaction();
        try {
            $registrationId = $registrationModel->create([
            'championship_id' => (int) $id,
            'user_id' => Auth::user()['id'],
            'name' => trim((string) ($_POST['name'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'email' => $email,
            'team' => trim((string) ($_POST['team'] ?? '')),
            'category' => trim((string) ($_POST['category'] ?? '')),
            'city' => trim((string) ($_POST['city'] ?? '')),
            'cpf' => trim((string) ($_POST['cpf'] ?? '')),
            'notes' => trim((string) ($_POST['notes'] ?? '')),
            'proof_file' => $proof,
            'status' => $isPaid ? 'aguardando_pagamento' : 'pendente',
        ]);
            if ($isPaid) {
                $paymentModel->createPending($registrationId, (float) $championship['registration_fee']);
            }
            $db->commit();
        } catch (\Throwable $exception) {
            $db->rollBack();
            throw $exception;
        }

        (new Notification())->create((int) $championship['organizer_id'], 'Nova inscricao recebida em ' . $championship['name'] . '.');
        $registration = $registrationModel->findDetails($registrationId);
        if ($registration) {
            $emails = new RegistrationEmailService();
            $emails->newRegistrationToOrganizer($registration);
            $emails->confirmationToAthlete($registration);
        }

        flash('success', $isPaid ? 'Inscricao realizada. Aguardando pagamento.' : 'Inscricao enviada. Status: pendente.');
        $this->redirect($isPaid ? '/atleta/historico' : '/campeonatos/' . $id);
    }

    public function favorite(string $id): void
    {
        $this->requireAuth('athlete');
        verify_csrf();
        $active = (new Favorite())->toggle(Auth::user()['id'], (int) $id);
        flash('success', $active ? 'Campeonato salvo nos favoritos.' : 'Campeonato removido dos favoritos.');
        $this->redirect('/campeonatos/' . $id);
    }

    public function review(string $id): void
    {
        $this->requireAuth('athlete');
        verify_csrf();
        $stmt = \App\Core\Database::connection()->prepare(
            'INSERT INTO reviews (championship_id, user_id, rating, comment) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([(int) $id, Auth::user()['id'], (int) $_POST['rating'], $_POST['comment'] ?? '']);
        flash('success', 'Avaliacao publicada.');
        $this->redirect('/campeonatos/' . $id);
    }

    public function calendar(): void
    {
        $this->view('championships/calendar', [
            'title' => 'Calendario esportivo',
            'events' => (new Championship())->calendar(),
        ]);
    }

    private function upload(string $field, array $allowed): ?string
    {
        if (empty($_FILES[$field]['name'])) {
            return null;
        }
        $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            flash('error', 'Arquivo invalido.');
            return null;
        }
        $name = uniqid($field . '_', true) . '.' . $ext;
        $target = BASE_PATH . '/uploads/' . $name;
        move_uploaded_file($_FILES[$field]['tmp_name'], $target);
        return 'uploads/' . $name;
    }
}

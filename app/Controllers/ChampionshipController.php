<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Championship;
use App\Models\CompetitionManagement;
use App\Models\Favorite;
use App\Models\Notification;
use App\Models\Registration;
use App\Models\RegistrationPayment;
use App\Models\Sport;
use App\Services\RegistrationEmailService;
use App\Services\PixService;
use Throwable;

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
        $competition = new CompetitionManagement();
        $competitionData = $competition->overview((int) $id);
        $this->view('championships/show', [
            'title' => $championship['name'],
            'championship' => $championship,
            'reviews' => $model->reviews((int) $id),
            'competitionData' => $competitionData,
            'competitionCounts' => $competitionData['summary']['counts'] ?? [],
        ]);
    }

    public function register(string $id): void
    {
        $this->requireAuth('athlete');
        verify_csrf();
        $userId = (int) Auth::user()['id'];
        $championshipId = (int) $id;
        $step = 'load championship';
        $championshipModel = new Championship();
        $championship = $championshipModel->find($championshipId);
        if (!$championship) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Campeonato nao encontrado']);
            return;
        }

        if ((new Registration())->existsForUser($championshipId, $userId)) {
            flash('error', 'Voce ja esta inscrito neste campeonato.');
            $this->redirect('/campeonatos/' . $id);
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Informe um e-mail valido.');
            $this->redirect('/campeonatos/' . $id);
        }

        $isPaid = $this->championshipRequiresPayment($championship);
        if ($isPaid && !$this->championshipHasPixConfiguration($championship)) {
            flash('error', 'Este campeonato ainda nao possui os dados PIX configurados. Entre em contato com o organizador.');
            $this->redirect('/campeonatos/' . $id);
        }

        $db = Database::connection();
        $registrationModel = new Registration();
        $paymentModel = new RegistrationPayment();

        $db->beginTransaction();
        try {
            $step = 'create registration';
            $registrationId = $registrationModel->create([
            'championship_id' => $championshipId,
            'user_id' => $userId,
            'name' => trim((string) ($_POST['name'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'email' => $email,
            'team' => trim((string) ($_POST['team'] ?? '')),
            'category' => trim((string) ($_POST['category'] ?? '')),
            'city' => trim((string) ($_POST['city'] ?? '')),
            'cpf' => trim((string) ($_POST['cpf'] ?? '')),
            'notes' => trim((string) ($_POST['notes'] ?? '')),
            'proof_file' => null,
            'status' => 'pendente',
        ]);
            if ($isPaid) {
                $step = 'create registration payment';
                $paymentModel->createPending($registrationId, (float) $championship['registration_fee']);
            }
            $db->commit();
        } catch (Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->logRegistrationError($exception, $championshipId, $userId, $step);
            flash('error', 'Nao foi possivel realizar sua inscricao. Tente novamente.');
            $this->redirect('/campeonatos/' . $id);
        }

        try {
            (new Notification())->create(
                (int) $championship['organizer_id'],
                trim((string) ($_POST['name'] ?? 'Atleta')) . ' solicitou inscricao no campeonato ' . $championship['name'] . '.',
                'Nova inscricao recebida',
                '/organizador/inscricoes?status=pending',
                'registration'
            );
        } catch (Throwable $exception) {
            $this->logRegistrationError($exception, $championshipId, $userId, 'notify organizer');
        }

        try {
            $registration = $registrationModel->findDetails($registrationId);
            if ($registration) {
                $emails = new RegistrationEmailService();
                $emails->newRegistrationToOrganizer($registration);
                $emails->confirmationToAthlete($registration);
            }
        } catch (Throwable $exception) {
            $this->logRegistrationError($exception, $championshipId, $userId, 'send registration emails');
        }

        flash('success', $isPaid ? 'Inscricao realizada. Aguardando pagamento.' : 'Inscricao enviada. Status: pendente.');
        $this->redirect('/atleta/historico');
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

    private function championshipRequiresPayment(array $championship): bool
    {
        return !empty($championship['requires_payment']) && (float) ($championship['registration_fee'] ?? 0) > 0;
    }

    private function championshipHasPixConfiguration(array $championship): bool
    {
        $data = [
            'pix_key_type' => $championship['pix_key_type'] ?? '',
            'pix_key' => $championship['pix_key'] ?? '',
            'pix_holder_name' => $championship['pix_holder_name'] ?? '',
            'pix_receiver_city' => $championship['pix_receiver_city'] ?? $championship['city'] ?? '',
            'registration_fee' => $championship['registration_fee'] ?? 0,
        ];

        return (new PixService())->validatePixData($data) === [];
    }

    private function logRegistrationError(Throwable $exception, int $championshipId, int $userId, string $step): void
    {
        $dir = BASE_PATH . '/storage/logs';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $message = '[' . date('Y-m-d H:i:s') . '] registration failed'
            . ' championship_id=' . $championshipId
            . ' user_id=' . $userId
            . ' step=' . $step
            . ' exception=' . get_class($exception)
            . ' code=' . $exception->getCode()
            . ' message=' . $exception->getMessage()
            . PHP_EOL;

        error_log($message, 3, $dir . '/app.log');
    }
}

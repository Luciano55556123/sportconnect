<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Security;
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
        $filters = [];
        foreach (['q', 'city', 'sport_id', 'date', 'max_price', 'category', 'modality', 'status'] as $key) {
            $filters[$key] = is_string($_GET[$key] ?? null) ? trim($_GET[$key]) : '';
        }
        $this->view('championships/index', [
            'title' => 'Pesquisar campeonatos',
            'sports' => (new Sport())->all(),
            'championships' => (new Championship())->search($filters),
            'filters' => $filters,
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
            'occupiedSpots' => $model->occupiedSpots($championship),
            'reviews' => $model->reviews((int) $id),
            'competitionData' => $competitionData,
            'competitionCounts' => $competitionData['summary']['counts'] ?? [],
        ]);
    }

    public function register(string $id): void
    {
        $this->requireAuth('athlete');
        verify_csrf();
        Security::rateLimit('registration', 8, 900);
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

        $registrationsOpen = in_array($championship['registrations_open'] ?? false, [true, 1, '1', 't', 'true', 'yes', 'on'], true);
        if (in_array(($championship['status'] ?? ''), ['encerrado', 'cancelado'], true)
            || !$registrationsOpen || ($championship['event_date'] ?? '') < date('Y-m-d')) {
            flash('error', 'As inscrições para este campeonato estão encerradas.');
            $this->redirect('/campeonatos/' . $id);
        }

        if ((new Registration())->existsForUser($championshipId, $userId)) {
            flash('error', 'Voce ja esta inscrito neste campeonato.');
            $this->redirect('/campeonatos/' . $id);
        }

        foreach (['name' => 160, 'phone' => 30, 'city' => 100, 'team' => 160, 'category' => 100] as $field => $limit) {
            $value = $_POST[$field] ?? '';
            if (!is_string($value) || strlen(trim($value)) > $limit
                || (in_array($field, ['name', 'phone', 'city'], true) && trim($value) === '')) {
                flash('error', 'Confira os campos nome, telefone, cidade, equipe e categoria.');
                $this->redirect('/campeonatos/' . $id);
            }
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
            // Bloqueia o campeonato durante a verificacao de vagas para evitar
            // duas inscricoes simultaneas ocuparem a ultima vaga.
            $step = 'check available spots';
            $lockedStmt = $db->prepare(
                'SELECT id, status, registrations_open, max_participants, registration_type
                 FROM championships WHERE id = ? FOR UPDATE'
            );
            $lockedStmt->execute([$championshipId]);
            $lockedChampionship = $lockedStmt->fetch();
            $stillOpen = $lockedChampionship && in_array(
                $lockedChampionship['registrations_open'] ?? false,
                [true, 1, '1', 't', 'true', 'yes', 'on'],
                true
            );
            if (!$stillOpen || in_array(($lockedChampionship['status'] ?? ''), ['encerrado', 'cancelado'], true)) {
                throw new \DomainException('As inscricoes para este campeonato estao encerradas.');
            }
            if ($registrationModel->existsForUser($championshipId, $userId)) {
                throw new \DomainException('Voce ja esta inscrito neste campeonato.');
            }
            $maxParticipants = (int) ($lockedChampionship['max_participants'] ?? 0);
            if ($maxParticipants > 0
                && $championshipModel->occupiedSpots($lockedChampionship) >= $maxParticipants) {
                throw new \DomainException('Nao ha mais vagas disponiveis para este campeonato.');
            }

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
        } catch (\DomainException $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            flash('error', $exception->getMessage());
            $this->redirect('/campeonatos/' . $id);
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
        Security::rateLimit('review', 10, 3600);

        $rating = filter_var($_POST['rating'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 5],
        ]);
        $comment = trim((string) ($_POST['comment'] ?? ''));
        if ($rating === false || strlen($comment) > 1500
            || !(new Championship())->find((int) $id)) {
            flash('error', 'Informe uma avaliacao de 1 a 5 estrelas e um comentario valido.');
            $this->redirect('/campeonatos/' . $id);
        }

        try {
            $stmt = Database::connection()->prepare(
                'INSERT INTO reviews (championship_id, user_id, rating, comment) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([(int) $id, (int) Auth::user()['id'], $rating, $comment]);
            flash('success', 'Avaliacao publicada.');
        } catch (\Throwable $exception) {
            error_log('Nao foi possivel publicar avaliacao: ' . $exception->getMessage());
            flash('error', 'Nao foi possivel publicar a avaliacao.');
        }
        $this->redirect('/campeonatos/' . $id);
    }

    public function rules(string $id): void
    {
        $championship = (new Championship())->find((int) $id);
        $rule = (string) ($championship['rules_file'] ?? '');
        if ($rule === '') {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Regulamento nao encontrado']);
            return;
        }

        if (filter_var($rule, FILTER_VALIDATE_URL) && str_starts_with(strtolower($rule), 'https://')) {
            header('Location: ' . $rule, true, 302);
            return;
        }

        // Mantem compatibilidade com PDFs salvos localmente em versoes anteriores.
        if (preg_match('#^uploads/[a-zA-Z0-9_.-]+\\.pdf$#', $rule) !== 1) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Regulamento indisponivel']);
            return;
        }
        $file = BASE_PATH . '/' . $rule;
        if (!is_file($file)) {
            $file = BASE_PATH . '/public/' . $rule;
        }
        if (!is_file($file)) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Regulamento indisponivel']);
            return;
        }

        header('Content-Type: application/pdf');
        header('X-Content-Type-Options: nosniff');
        header('Content-Disposition: inline; filename="regulamento.pdf"');
        readfile($file);
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

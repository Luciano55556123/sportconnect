<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Security;
use App\Models\Championship;
use App\Models\Athlete;
use App\Models\AthleteStatistic;
use App\Models\CompetitionMatch;
use App\Models\Favorite;
use App\Models\MatchEvent;
use App\Models\MatchSet;
use App\Models\Registration;
use App\Models\Report;
use App\Models\Sport;
use App\Models\Standing;
use App\Models\Team;

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
        $publicStatuses = ['published', 'registration_open', 'registration_closed', 'in_progress', 'finished'];
        $canPreview = Auth::check() && (Auth::user()['role'] === 'admin' || (int) $championship['organizer_id'] === (int) Auth::user()['id']);
        if (!in_array($championship['status'] ?? '', $publicStatuses, true) && !$canPreview) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Campeonato nao encontrado']);
            return;
        }
        $model->incrementViews((int) $id);
        $this->view('championships/show', [
            'title' => $championship['name'],
            'championship' => $championship,
            'reviews' => $model->reviews((int) $id),
            'teams' => (new Team())->byChampionship((int) $id),
            'athletes' => (new Athlete())->byChampionship((int) $id),
            'matches' => (new CompetitionMatch())->byChampionship((int) $id),
            'eventsByMatch' => (new MatchEvent())->byChampionship((int) $id),
            'setsByMatch' => (new MatchSet())->byChampionship((int) $id),
            'standings' => (new Standing())->byChampionship((int) $id),
            'statistics' => (new AthleteStatistic())->byChampionship((int) $id),
        ]);
    }

    public function register(string $id): void
    {
        $this->requireAuth('athlete');
        verify_csrf();
        Security::rateLimit('championship_registration', 8, 600);
        $championship = (new Championship())->find((int) $id);
        if (!$championship || ($championship['status'] ?? '') !== 'registration_open') {
            flash('error', 'As inscricoes nao estao abertas para este campeonato.');
            $this->redirect('/campeonatos/' . $id);
        }
        if (!empty($championship['registration_deadline']) && strtotime($championship['registration_deadline']) < strtotime(date('Y-m-d'))) {
            flash('error', 'O prazo de inscricao ja encerrou.');
            $this->redirect('/campeonatos/' . $id);
        }
        if (empty($_POST['accepted_terms'])) {
            flash('error', 'E necessario aceitar o regulamento para se inscrever.');
            $this->redirect('/campeonatos/' . $id);
        }
        $registrationModel = new Registration();
        $limit = (int) ($championship['maximum_registrations'] ?? $championship['max_participants'] ?? 0);
        if ($limit > 0 && $registrationModel->countActiveForChampionship((int) $id) >= $limit) {
            flash('error', 'O limite de vagas deste campeonato foi atingido.');
            $this->redirect('/campeonatos/' . $id);
        }
        $proof = $this->upload('proof_file', ['pdf', 'jpg', 'jpeg', 'png']);
        if ((!empty($championship['requires_documents']) || !empty($championship['requires_payment'])) && !$proof) {
            flash('error', 'Envie o documento ou comprovante exigido para concluir a inscricao.');
            $this->redirect('/campeonatos/' . $id);
        }
        $registrationModel->create([
            'championship_id' => (int) $id,
            'user_id' => Auth::user()['id'],
            'name' => $_POST['name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'team' => $_POST['team'] ?? '',
            'category' => $_POST['category'] ?? '',
            'city' => $_POST['city'] ?? '',
            'cpf' => $_POST['cpf'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'proof_file' => $proof,
            'accepted_terms' => $_POST['accepted_terms'] ?? null,
        ]);
        (new \App\Models\Notification())->create((int) $championship['organizer_id'], 'Inscricao recebida', 'Uma nova inscricao chegou para ' . $championship['name'] . '.', '/organizador/inscricoes', 'registration');
        flash('success', 'Inscricao enviada. Status: pendente.');
        $this->redirect('/campeonatos/' . $id);
    }

    public function favorite(string $id): void
    {
        $this->requireAuth('athlete');
        verify_csrf();
        Security::rateLimit('review', 10, 600);
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

    public function report(string $id): void
    {
        $this->requireAuth();
        verify_csrf();
        Security::rateLimit('report', 5, 600);
        (new Report())->create($_POST, Auth::user()['id'], (int) $id);
        flash('success', 'Denuncia enviada para analise administrativa.');
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
        if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK || (int) $_FILES[$field]['size'] > 5 * 1024 * 1024 || !is_uploaded_file($_FILES[$field]['tmp_name'])) {
            flash('error', 'Nao foi possivel validar o arquivo enviado.');
            return null;
        }
        $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            flash('error', 'Arquivo invalido.');
            return null;
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES[$field]['tmp_name']);
        $validMime = match ($ext) {
            'pdf' => $mime === 'application/pdf',
            'jpg', 'jpeg' => in_array($mime, ['image/jpeg', 'image/pjpeg'], true),
            'png' => $mime === 'image/png',
            default => false,
        };
        if (!$validMime) {
            flash('error', 'O conteudo do arquivo nao corresponde ao formato informado.');
            return null;
        }
        $name = bin2hex(random_bytes(16)) . '.' . $ext;
        $target = BASE_PATH . '/uploads/' . $name;
        move_uploaded_file($_FILES[$field]['tmp_name'], $target);
        return 'uploads/' . $name;
    }
}

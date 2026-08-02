<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Favorite;
use App\Models\Notification;
use App\Models\Recommendation;
use App\Models\Registration;
use App\Models\Sport;
use App\Models\User;

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
        $this->view('athlete/history', [
            'title' => 'Historico',
            'registrations' => (new Registration())->byUser(Auth::user()['id']),
        ]);
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
}

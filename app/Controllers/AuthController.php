<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Sport;
use App\Models\User;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        $this->view('auth/login', ['title' => 'Entrar']);
    }

    public function login(): void
    {
        verify_csrf();
        $user = (new User())->findByEmail($_POST['email'] ?? '');
        if (!$user || !password_verify($_POST['password'] ?? '', $user['password'])) {
            flash('error', 'Email ou senha invalidos.');
            $this->redirect('/login');
        }
        Auth::login($user);
        $this->redirect($user['role'] === 'organizer' ? '/organizador' : ($user['role'] === 'admin' ? '/admin' : '/atleta'));
    }

    public function registerForm(): void
    {
        $this->view('auth/register', ['title' => 'Criar conta', 'sports' => (new Sport())->all()]);
    }

    public function register(): void
    {
        verify_csrf();
        $userModel = new User();
        if ($userModel->findByEmail($_POST['email'] ?? '')) {
            flash('error', 'Este email ja esta cadastrado.');
            $this->redirect('/cadastro');
        }
        $id = $userModel->create($_POST);
        $userModel->syncFavoriteSports($id, $_POST['sports'] ?? []);
        Auth::login($userModel->find($id));
        flash('success', 'Conta criada com sucesso.');
        $this->redirect('/atleta');
    }

    public function logout(): void
    {
        Auth::logout();
        session_start();
        flash('success', 'Voce saiu da sua conta.');
        $this->redirect('/');
    }
}

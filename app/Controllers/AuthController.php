<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Security;
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
        Security::rateLimit('login', 5, 300);
        $user = (new User())->findByEmail($_POST['email'] ?? '');
        if (!$user || !password_verify($_POST['password'] ?? '', $user['password'])) {
            error_log('login_failed email_hash=' . hash('sha256', strtolower(trim($_POST['email'] ?? ''))) . ' ip=' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            flash('error', 'Email ou senha invalidos.');
            $this->redirect('/login');
        }
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            (new User())->updatePasswordHash((int) $user['id'], password_hash($_POST['password'], PASSWORD_DEFAULT));
        }
        error_log('login_success user=' . (int) $user['id']);
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
        Security::rateLimit('register', 3, 600);
        if (strlen((string) ($_POST['password'] ?? '')) < 8 || !filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) || trim($_POST['name'] ?? '') === '') {
            flash('error', 'Informe nome, email valido e senha com pelo menos 8 caracteres.');
            $this->redirect('/cadastro');
        }
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

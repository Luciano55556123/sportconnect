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
        Security::rateLimit('login', 12, 900);
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $user = (new User())->findByEmail($email);
        if (!$user || !password_verify($_POST['password'] ?? '', $user['password'])) {
            flash('error', 'Email ou senha invalidos.');
            $this->redirect('/login');
        }
        Auth::login($user);
        $this->redirect(in_array($user['role'], ['organizer', 'organizador'], true) ? '/organizador' : ($user['role'] === 'admin' ? '/admin' : '/atleta'));
    }

    public function registerForm(): void
    {
        $this->view('auth/register', ['title' => 'Criar conta', 'sports' => (new Sport())->all()]);
    }

    public function register(): void
    {
        verify_csrf();
        Security::rateLimit('register', 5, 3600);
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        if (strlen($name) < 2 || strlen($name) > 320 || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || strlen($password) < 8 || strlen($password) > 72) {
            flash('error', 'Informe um nome, e-mail valido e senha de 8 a 72 caracteres.');
            $this->redirect('/cadastro');
        }

        $userModel = new User();
        if ($userModel->findByEmail($email)) {
            flash('error', 'Este email ja esta cadastrado.');
            $this->redirect('/cadastro');
        }

        $data = $_POST;
        $data['name'] = $name;
        $data['email'] = $email;
        $sports = array_values(array_unique(array_filter(
            array_map('intval', is_array($_POST['sports'] ?? null) ? $_POST['sports'] : []),
            static fn (int $id): bool => $id > 0
        )));
        $allowedSports = array_map('intval', array_column((new Sport())->all(), 'id'));
        $sports = array_values(array_intersect($sports, $allowedSports));

        $db = \App\Core\Database::connection();
        try {
            $db->beginTransaction();
            $id = $userModel->create($data);
            $userModel->syncFavoriteSports($id, $sports);
            $db->commit();
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('Falha ao cadastrar usuario: ' . $exception->getMessage());
            flash('error', 'Nao foi possivel criar a conta. Confira os dados ou tente outro e-mail.');
            $this->redirect('/cadastro');
        }

        Auth::login($userModel->find($id));
        flash('success', 'Conta criada com sucesso.');
        $this->redirect('/atleta');
    }

    public function logout(): void
    {
        verify_csrf();
        Auth::logout();
        session_start();
        flash('success', 'Voce saiu da sua conta.');
        $this->redirect('/');
    }
}

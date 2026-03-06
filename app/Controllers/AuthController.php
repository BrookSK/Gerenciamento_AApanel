<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Controller;
use App\Core\Response;

final class AuthController extends Controller
{
    public function showLogin(): Response
    {
        return $this->view('auth/login', [
            'error' => null,
        ]);
    }

    public function login(): Response
    {
        $email = (string)($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        $auth = Container::get('auth');
        if ($auth->attempt($email, $password)) {
            return $this->redirect('/');
        }

        return $this->view('auth/login', [
            'error' => 'Login inválido',
        ]);
    }

    public function logout(): Response
    {
        $auth = Container::get('auth');
        $auth->logout();
        return $this->redirect('/login');
    }
}

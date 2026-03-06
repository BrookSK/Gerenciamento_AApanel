<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Client;

final class PortalAuthController extends Controller
{
    public function showLogin(): Response
    {
        return $this->view('portal/login', [
            'error' => null,
        ]);
    }

    public function login(): Response
    {
        $email = (string)($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        $client = Client::findForPortalLogin($email);
        if ($client === null) {
            return $this->view('portal/login', ['error' => 'Login inválido']);
        }

        if ((int)($client['portal_enabled'] ?? 0) !== 1) {
            return $this->view('portal/login', ['error' => 'Acesso desativado']);
        }

        $hash = (string)($client['portal_password_hash'] ?? '');
        if ($hash === '' || !password_verify($password, $hash)) {
            return $this->view('portal/login', ['error' => 'Login inválido']);
        }

        $_SESSION['portal_client_id'] = (int)$client['id'];
        Client::touchPortalLastLogin((int)$client['id']);

        return Response::redirect('/portal');
    }

    public function logout(): Response
    {
        unset($_SESSION['portal_client_id']);
        return Response::redirect('/portal/login');
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Controller;
use App\Core\Response;
use App\Services\AapanelAdminMailService;

final class AapanelEmailsController extends Controller
{
    private function requireAuth(): ?Response
    {
        $auth = Container::get('auth');
        if (!$auth->check()) {
            return Response::redirect('/login');
        }
        return null;
    }

    public function index(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $svc = new AapanelAdminMailService();
        $result = $svc->listMailboxes();

        return $this->view('aapanel_emails/index', [
            'emails' => (array)($result['items'] ?? []),
            'raw' => $result['raw'] ?? null,
            'error' => $result['error'] ?? null,
        ]);
    }

    public function create(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        return $this->view('aapanel_emails/create', [
            'error' => null,
        ]);
    }

    public function store(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            return $this->view('aapanel_emails/create', [
                'error' => 'Informe e-mail e senha',
            ]);
        }

        $svc = new AapanelAdminMailService();
        $svc->createMailbox($email, $password);

        return $this->redirect('/aapanel-emails');
    }

    public function delete(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $email = trim((string)($_POST['email'] ?? ''));
        if ($email !== '') {
            $svc = new AapanelAdminMailService();
            $svc->deleteMailbox($email);
        }

        return $this->redirect('/aapanel-emails');
    }

    public function changePassword(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            return new Response(400, [], 'Bad Request');
        }

        $svc = new AapanelAdminMailService();
        $svc->changePassword($email, $password);

        return $this->redirect('/aapanel-emails');
    }
}

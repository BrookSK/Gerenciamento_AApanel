<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Controller;
use App\Core\Response;
use App\Models\AapanelServer;

final class AapanelServersController extends Controller
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

        return $this->view('aapanel_servers/index', [
            'servers' => AapanelServer::all(),
        ]);
    }

    public function create(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        return $this->view('aapanel_servers/form', [
            'server' => null,
        ]);
    }

    public function store(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        AapanelServer::create($_POST);
        return $this->redirect('/aapanel-servers');
    }

    public function edit(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $id = (int)($_GET['id'] ?? 0);
        $server = $id > 0 ? AapanelServer::find($id) : null;
        if ($server === null) {
            return new Response(404, [], 'Not Found');
        }

        return $this->view('aapanel_servers/form', [
            'server' => $server,
        ]);
    }

    public function update(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            return new Response(400, [], 'Bad Request');
        }

        AapanelServer::update($id, $_POST);
        return $this->redirect('/aapanel-servers');
    }

    public function delete(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            AapanelServer::delete($id);
        }

        return $this->redirect('/aapanel-servers');
    }
}

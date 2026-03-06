<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Controller;
use App\Core\Response;
use App\Models\Client;

final class ClientsController extends Controller
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

        return $this->view('clients/index', [
            'clients' => Client::all(),
        ]);
    }

    public function create(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        return $this->view('clients/form', [
            'client' => null,
        ]);
    }

    public function store(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        Client::create($_POST);
        return $this->redirect('/clients');
    }

    public function edit(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $id = (int)($_GET['id'] ?? 0);
        $client = $id > 0 ? Client::find($id) : null;
        if ($client === null) {
            return new Response(404, [], 'Not Found');
        }

        return $this->view('clients/form', [
            'client' => $client,
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

        Client::update($id, $_POST);
        return $this->redirect('/clients');
    }

    public function delete(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            Client::delete($id);
        }

        return $this->redirect('/clients');
    }
}

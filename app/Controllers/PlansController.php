<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Controller;
use App\Core\Response;
use App\Models\Plan;

final class PlansController extends Controller
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

        return $this->view('plans/index', [
            'plans' => Plan::all(),
        ]);
    }

    public function create(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        return $this->view('plans/form', [
            'plan' => null,
        ]);
    }

    public function store(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        Plan::create($_POST);
        return $this->redirect('/plans');
    }

    public function edit(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $id = (int)($_GET['id'] ?? 0);
        $plan = $id > 0 ? Plan::find($id) : null;
        if ($plan === null) {
            return new Response(404, [], 'Not Found');
        }

        return $this->view('plans/form', [
            'plan' => $plan,
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

        Plan::update($id, $_POST);
        return $this->redirect('/plans');
    }

    public function delete(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            Plan::delete($id);
        }

        return $this->redirect('/plans');
    }
}

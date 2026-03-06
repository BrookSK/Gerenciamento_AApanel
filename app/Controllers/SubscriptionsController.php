<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Controller;
use App\Core\Response;
use App\Models\Client;
use App\Models\Plan;
use App\Models\Subscription;

final class SubscriptionsController extends Controller
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

        return $this->view('subscriptions/index', [
            'subscriptions' => Subscription::all(),
        ]);
    }

    public function create(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        return $this->view('subscriptions/form', [
            'subscription' => null,
            'clients' => Client::all(),
            'plans' => Plan::allActive(),
        ]);
    }

    public function store(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        Subscription::create($_POST);
        return $this->redirect('/subscriptions');
    }

    public function edit(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $id = (int)($_GET['id'] ?? 0);
        $subscription = $id > 0 ? Subscription::find($id) : null;
        if ($subscription === null) {
            return new Response(404, [], 'Not Found');
        }

        return $this->view('subscriptions/form', [
            'subscription' => $subscription,
            'clients' => Client::all(),
            'plans' => Plan::allActive(),
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

        Subscription::update($id, $_POST);
        return $this->redirect('/subscriptions');
    }

    public function delete(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            Subscription::delete($id);
        }

        return $this->redirect('/subscriptions');
    }
}

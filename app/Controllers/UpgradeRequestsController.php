<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Controller;
use App\Core\Response;
use App\Models\Subscription;
use App\Models\UpgradeRequest;
use App\Services\AsaasUpgradeService;

final class UpgradeRequestsController extends Controller
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

        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 50);

        $data = UpgradeRequest::paginate($page, $perPage);

        return $this->view('upgrade_requests/index', [
            'data' => $data,
        ]);
    }

    public function apply(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $id = (int)($_POST['id'] ?? 0);
        $req = $id > 0 ? UpgradeRequest::find($id) : null;
        if ($req === null) {
            return new Response(404, [], 'Not Found');
        }

        Subscription::setPlanId((int)$req['subscription_id'], (int)$req['target_plan_id']);
        UpgradeRequest::apply($id);

        return Response::redirect('/upgrade-requests');
    }

    public function cancel(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            UpgradeRequest::cancel($id);
        }

        return Response::redirect('/upgrade-requests');
    }

    public function regeneratePayment(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $id = (int)($_POST['id'] ?? 0);
        $req = $id > 0 ? UpgradeRequest::find($id) : null;
        if ($req === null) {
            return new Response(404, [], 'Not Found');
        }

        if ((string)$req['status'] === 'pending') {
            $asaas = new AsaasUpgradeService();
            $asaas->createPaymentForUpgradeRequest($id);
        }

        return Response::redirect('/upgrade-requests');
    }
}

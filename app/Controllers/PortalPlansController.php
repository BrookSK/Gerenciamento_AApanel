<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\UpgradeRequest;
use App\Services\AsaasUpgradeService;

final class PortalPlansController extends Controller
{
    private function requirePortalAuth(): ?Response
    {
        $id = (int)($_SESSION['portal_client_id'] ?? 0);
        if ($id <= 0) {
            return Response::redirect('/portal/login');
        }
        return null;
    }

    public function index(): Response
    {
        if ($r = $this->requirePortalAuth()) {
            return $r;
        }

        $clientId = (int)$_SESSION['portal_client_id'];
        $subs = Subscription::allByClientId($clientId);

        $pendingBySub = [];
        foreach ($subs as $s) {
            $pendingBySub[(int)$s['id']] = UpgradeRequest::pendingBySubscriptionId((int)$s['id']);
        }

        return $this->view('portal/plans/index', [
            'subscriptions' => $subs,
            'plans' => Plan::allActive(),
            'pendingBySub' => $pendingBySub,
        ]);
    }

    public function requestUpgrade(): Response
    {
        if ($r = $this->requirePortalAuth()) {
            return $r;
        }

        $clientId = (int)$_SESSION['portal_client_id'];
        $subscriptionId = (int)($_POST['subscription_id'] ?? 0);
        $targetPlanId = (int)($_POST['target_plan_id'] ?? 0);

        if ($subscriptionId <= 0 || $targetPlanId <= 0) {
            return new Response(400, [], 'Bad Request');
        }

        $sub = Subscription::find($subscriptionId);
        if ($sub === null || (int)$sub['client_id'] !== $clientId) {
            return new Response(403, [], 'Forbidden');
        }

        $pending = UpgradeRequest::pendingBySubscriptionId($subscriptionId);
        if (count($pending) > 0) {
            return Response::redirect('/portal/plans?error=pending_exists');
        }

        $currentPlanId = isset($sub['plan_id']) && $sub['plan_id'] !== null ? (int)$sub['plan_id'] : null;

        $upgradeRequestId = UpgradeRequest::create($clientId, $subscriptionId, $currentPlanId, $targetPlanId);

        $asaas = new AsaasUpgradeService();
        $asaas->createPaymentForUpgradeRequest($upgradeRequestId);

        return Response::redirect('/portal/plans');
    }

    public function regeneratePayment(): Response
    {
        if ($r = $this->requirePortalAuth()) {
            return $r;
        }

        $clientId = (int)$_SESSION['portal_client_id'];
        $upgradeRequestId = (int)($_POST['upgrade_request_id'] ?? 0);
        if ($upgradeRequestId <= 0) {
            return new Response(400, [], 'Bad Request');
        }

        $req = UpgradeRequest::find($upgradeRequestId);
        if ($req === null || (int)$req['client_id'] !== $clientId) {
            return new Response(403, [], 'Forbidden');
        }

        if ((string)$req['status'] !== 'pending') {
            return Response::redirect('/portal/plans');
        }

        $asaas = new AsaasUpgradeService();
        $asaas->createPaymentForUpgradeRequest($upgradeRequestId);

        return Response::redirect('/portal/plans');
    }
}

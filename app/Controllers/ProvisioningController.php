<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Controller;
use App\Core\Response;
use App\Models\Subscription;
use App\Services\ProvisioningService;

final class ProvisioningController extends Controller
{
    private function requireAuth(): ?Response
    {
        $auth = Container::get('auth');
        if (!$auth->check()) {
            return Response::redirect('/login');
        }
        return null;
    }

    public function wordpressForm(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $subscriptionId = (int)($_GET['subscription_id'] ?? 0);
        $subscription = $subscriptionId > 0 ? Subscription::find($subscriptionId) : null;
        if ($subscription === null) {
            return new Response(404, [], 'Not Found');
        }

        return $this->view('provisioning/wordpress', [
            'subscription' => $subscription,
            'error' => null,
        ]);
    }

    public function wordpressProvision(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $subscriptionId = (int)($_POST['subscription_id'] ?? 0);
        $domain = (string)($_POST['domain'] ?? '');

        if ($subscriptionId <= 0 || trim($domain) === '') {
            return new Response(400, [], 'Bad Request');
        }

        $svc = new ProvisioningService();
        $svc->provisionWordpress($subscriptionId, $domain);

        return Response::redirect('/subscriptions/edit?id=' . $subscriptionId);
    }

    public function wordpressInstallLinked(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $subscriptionId = (int)($_POST['subscription_id'] ?? 0);
        $domain = (string)($_POST['domain'] ?? '');

        if ($subscriptionId <= 0 || trim($domain) === '') {
            return new Response(400, [], 'Bad Request');
        }

        $svc = new ProvisioningService();
        $svc->installWordpressOnLinkedSite($subscriptionId, $domain);

        return Response::redirect('/subscriptions/edit?id=' . $subscriptionId);
    }
}

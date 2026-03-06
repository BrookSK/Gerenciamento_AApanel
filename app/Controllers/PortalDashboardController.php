<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Client;
use App\Models\Subscription;

final class PortalDashboardController extends Controller
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
        $client = Client::find($clientId);
        if ($client === null) {
            return new Response(404, [], 'Not Found');
        }

        $subscriptions = Subscription::allByClientId($clientId);

        return $this->view('portal/dashboard', [
            'client' => $client,
            'subscriptions' => $subscriptions,
        ]);
    }
}

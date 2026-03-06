<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Services\AsaasWebhookService;

final class AsaasWebhookController extends Controller
{
    public function handle(): Response
    {
        $raw = file_get_contents('php://input');
        if (!is_string($raw)) {
            $raw = '';
        }

        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            $payload = [];
        }

        $svc = new AsaasWebhookService();
        $svc->handle($payload);

        return new Response(200, ['Content-Type' => 'application/json; charset=utf-8'], json_encode(['ok' => true]));
    }
}

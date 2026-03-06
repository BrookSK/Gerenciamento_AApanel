<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Container;
use App\Core\Response;
use App\Services\AsaasWebhookService;
use App\Services\SettingsService;

final class AsaasWebhookController extends Controller
{
    public function handle(): Response
    {
        $settings = new SettingsService();
        $expected = (string)($settings->safeGet('asaas_webhook_access_token') ?? '');
        $expected = trim($expected);

        if ($expected !== '') {
            $provided = '';
            if (isset($_SERVER['HTTP_ASAAS_ACCESS_TOKEN'])) {
                $provided = (string)$_SERVER['HTTP_ASAAS_ACCESS_TOKEN'];
            }
            if ($provided === '' && isset($_SERVER['HTTP_ASAAS_ACCESS_TOKEN'.''])) {
                $provided = (string)$_SERVER['HTTP_ASAAS_ACCESS_TOKEN'];
            }
            if ($provided === '' && function_exists('getallheaders')) {
                $headers = getallheaders();
                if (is_array($headers)) {
                    foreach ($headers as $k => $v) {
                        if (is_string($k) && strcasecmp($k, 'asaas-access-token') === 0) {
                            $provided = is_string($v) ? $v : '';
                            break;
                        }
                    }
                }
            }

            if (trim($provided) !== $expected) {
                return new Response(401, ['Content-Type' => 'application/json; charset=utf-8'], json_encode(['ok' => false, 'error' => 'unauthorized']));
            }
        }

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

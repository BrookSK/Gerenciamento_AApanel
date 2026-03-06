<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Controller;
use App\Core\Response;
use App\Models\AapanelServer;
use App\Services\SettingsService;

final class SettingsController extends Controller
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

        $svc = new SettingsService();
        $settings = $svc->safeLoadAll();

        return $this->view('settings/index', [
            'settings' => $settings,
            'servers' => AapanelServer::all(),
            'baseUrl' => (string)((Container::get('config')['app']['base_url'] ?? '')),
        ]);
    }

    public function save(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $svc = new SettingsService();

        $keys = [
            'aapanel_default_server_id',
            'aapanel_wwwroot_base',
            'aapanel_insecure_ssl',
            'wp_base_path',
            'asaas_environment',
            'asaas_token_sandbox',
            'asaas_token_prod',
            'asaas_billing_type',
            'asaas_payment_description',
            'asaas_webhook_url',
            'asaas_webhook_access_token',
            'ticket_notify_emails',
        ];

        foreach ($keys as $k) {
            $v = $_POST[$k] ?? null;
            if (is_string($v)) {
                $v = trim($v);
            }
            $svc->safeSet($k, $v === '' ? null : (is_string($v) ? $v : null));
        }

        return $this->redirect('/settings');
    }
}

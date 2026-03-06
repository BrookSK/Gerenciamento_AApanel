<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AapanelServer;
use App\Models\IntegrationLog;

final class AapanelMailService
{
    private function getDefaultAapanelServer(): ?array
    {
        $settings = new SettingsService();
        $id = (int)($settings->safeGet('aapanel_default_server_id') ?? 0);
        if ($id <= 0) {
            return null;
        }
        return AapanelServer::find($id);
    }

    public function createMailbox(string $email, string $password): array
    {
        $server = $this->getDefaultAapanelServer();
        if ($server === null) {
            $resp = ['error' => 'No default server configured'];
            IntegrationLog::add('aapanel', 'mail.create', 'mailbox', $email, 'error', $resp['error'], ['email' => $email], $resp);
            return $resp;
        }

        $client = new AapanelApiClient((string)$server['base_url'], (string)$server['api_key']);

        // Best-effort: endpoints podem variar conforme plugin Mail Server.
        $params = [
            'username' => $email,
            'password' => $password,
        ];

        $resp = $client->request('/mail?action=AddMailUser', $params);
        IntegrationLog::add('aapanel', 'mail.create', 'mailbox', $email, 'ok', null, $params, $resp);
        return $resp;
    }

    public function deleteMailbox(string $email): array
    {
        $server = $this->getDefaultAapanelServer();
        if ($server === null) {
            $resp = ['error' => 'No default server configured'];
            IntegrationLog::add('aapanel', 'mail.delete', 'mailbox', $email, 'error', $resp['error'], ['email' => $email], $resp);
            return $resp;
        }

        $client = new AapanelApiClient((string)$server['base_url'], (string)$server['api_key']);

        $params = [
            'username' => $email,
        ];

        $resp = $client->request('/mail?action=DeleteMailUser', $params);
        IntegrationLog::add('aapanel', 'mail.delete', 'mailbox', $email, 'ok', null, $params, $resp);
        return $resp;
    }

    public function changePassword(string $email, string $newPassword): array
    {
        $server = $this->getDefaultAapanelServer();
        if ($server === null) {
            $resp = ['error' => 'No default server configured'];
            IntegrationLog::add('aapanel', 'mail.password', 'mailbox', $email, 'error', $resp['error'], ['email' => $email], $resp);
            return $resp;
        }

        $client = new AapanelApiClient((string)$server['base_url'], (string)$server['api_key']);

        $params = [
            'username' => $email,
            'password' => $newPassword,
        ];

        $resp = $client->request('/mail?action=SetMailUserPassword', $params);
        IntegrationLog::add('aapanel', 'mail.password', 'mailbox', $email, 'ok', null, $params, $resp);
        return $resp;
    }
}

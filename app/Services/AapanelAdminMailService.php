<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AapanelServer;
use App\Models\IntegrationLog;

final class AapanelAdminMailService
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

    private function client(): ?AapanelApiClient
    {
        $server = $this->getDefaultAapanelServer();
        if ($server === null) {
            return null;
        }

        $settings = new SettingsService();
        $insecure = (string)($settings->safeGet('aapanel_insecure_ssl') ?? '');
        $verifySsl = $insecure !== '1';

        return new AapanelApiClient((string)$server['base_url'], (string)$server['api_key'], $verifySsl);
    }

    public function listMailboxes(): array
    {
        $client = $this->client();
        if ($client === null) {
            return ['error' => 'No default server configured'];
        }

        $attempts = [
            ['/mail?action=GetMailUserList', ['p' => 1, 'limit' => 1000, 'search' => '']],
            ['/mail?action=GetMailList', ['p' => 1, 'limit' => 1000, 'search' => '']],
            ['/mail?action=GetMailUser', []],
        ];

        foreach ($attempts as [$path, $params]) {
            $resp = $client->request((string)$path, (array)$params);
            IntegrationLog::add('aapanel', 'mail.list', 'endpoint', (string)$path, 'ok', null, ['params' => $params], $resp);

            if (isset($resp['data']) && is_array($resp['data'])) {
                return ['items' => $resp['data'], 'raw' => $resp];
            }
            if (isset($resp['list']) && is_array($resp['list'])) {
                return ['items' => $resp['list'], 'raw' => $resp];
            }
            if (isset($resp['items']) && is_array($resp['items'])) {
                return ['items' => $resp['items'], 'raw' => $resp];
            }
        }

        return ['items' => [], 'raw' => $resp ?? []];
    }

    public function createMailbox(string $email, string $password): array
    {
        $svc = new AapanelMailService();
        return $svc->createMailbox($email, $password);
    }

    public function deleteMailbox(string $email): array
    {
        $svc = new AapanelMailService();
        return $svc->deleteMailbox($email);
    }

    public function changePassword(string $email, string $newPassword): array
    {
        $svc = new AapanelMailService();
        return $svc->changePassword($email, $newPassword);
    }
}

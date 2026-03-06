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

        $settings = new SettingsService();
        $insecure = (string)($settings->safeGet('aapanel_insecure_ssl') ?? '');
        $verifySsl = $insecure !== '1';
        $client = new AapanelApiClient((string)$server['base_url'], (string)$server['api_key'], $verifySsl);

        // Best-effort: endpoints podem variar conforme plugin Mail Server.
        $attempts = [
            ['/mail?action=AddMailUser', ['username' => $email, 'password' => $password]],
            ['/mail?action=AddUser', ['username' => $email, 'password' => $password]],
            ['/mail?action=CreateMail', ['username' => $email, 'password' => $password]],
            ['/mail?action=AddMailbox', ['email' => $email, 'password' => $password]],
        ];

        $last = ['error' => 'No response'];
        foreach ($attempts as [$path, $params]) {
            $last = $client->request((string)$path, (array)$params);
            IntegrationLog::add('aapanel', 'mail.create', 'endpoint', (string)$path, 'ok', null, $params, $last);

            $status = $last['status'] ?? null;
            $http = (int)($last['http_status'] ?? 200);
            if (($status === true || $status === 1 || $status === '1') && $http < 400) {
                return $last;
            }
        }

        IntegrationLog::add('aapanel', 'mail.create', 'mailbox', $email, 'error', 'All endpoints failed', ['email' => $email], $last);
        return $last;
    }

    public function deleteMailbox(string $email): array
    {
        $server = $this->getDefaultAapanelServer();
        if ($server === null) {
            $resp = ['error' => 'No default server configured'];
            IntegrationLog::add('aapanel', 'mail.delete', 'mailbox', $email, 'error', $resp['error'], ['email' => $email], $resp);
            return $resp;
        }

        $settings = new SettingsService();
        $insecure = (string)($settings->safeGet('aapanel_insecure_ssl') ?? '');
        $verifySsl = $insecure !== '1';
        $client = new AapanelApiClient((string)$server['base_url'], (string)$server['api_key'], $verifySsl);

        $attempts = [
            ['/mail?action=DeleteMailUser', ['username' => $email]],
            ['/mail?action=DeleteUser', ['username' => $email]],
            ['/mail?action=DeleteMail', ['username' => $email]],
            ['/mail?action=DeleteMailbox', ['email' => $email]],
        ];

        $last = ['error' => 'No response'];
        foreach ($attempts as [$path, $params]) {
            $last = $client->request((string)$path, (array)$params);
            IntegrationLog::add('aapanel', 'mail.delete', 'endpoint', (string)$path, 'ok', null, $params, $last);

            $status = $last['status'] ?? null;
            $http = (int)($last['http_status'] ?? 200);
            if (($status === true || $status === 1 || $status === '1') && $http < 400) {
                return $last;
            }
        }

        IntegrationLog::add('aapanel', 'mail.delete', 'mailbox', $email, 'error', 'All endpoints failed', ['email' => $email], $last);
        return $last;
    }

    public function changePassword(string $email, string $newPassword): array
    {
        $server = $this->getDefaultAapanelServer();
        if ($server === null) {
            $resp = ['error' => 'No default server configured'];
            IntegrationLog::add('aapanel', 'mail.password', 'mailbox', $email, 'error', $resp['error'], ['email' => $email], $resp);
            return $resp;
        }

        $settings = new SettingsService();
        $insecure = (string)($settings->safeGet('aapanel_insecure_ssl') ?? '');
        $verifySsl = $insecure !== '1';
        $client = new AapanelApiClient((string)$server['base_url'], (string)$server['api_key'], $verifySsl);

        $attempts = [
            ['/mail?action=SetMailUserPassword', ['username' => $email, 'password' => $newPassword]],
            ['/mail?action=SetUserPassword', ['username' => $email, 'password' => $newPassword]],
            ['/mail?action=ChangePassword', ['username' => $email, 'password' => $newPassword]],
            ['/mail?action=SetMailboxPassword', ['email' => $email, 'password' => $newPassword]],
        ];

        $last = ['error' => 'No response'];
        foreach ($attempts as [$path, $params]) {
            $last = $client->request((string)$path, (array)$params);
            IntegrationLog::add('aapanel', 'mail.password', 'endpoint', (string)$path, 'ok', null, $params, $last);

            $status = $last['status'] ?? null;
            $http = (int)($last['http_status'] ?? 200);
            if (($status === true || $status === 1 || $status === '1') && $http < 400) {
                return $last;
            }
        }

        IntegrationLog::add('aapanel', 'mail.password', 'mailbox', $email, 'error', 'All endpoints failed', ['email' => $email], $last);
        return $last;
    }
}

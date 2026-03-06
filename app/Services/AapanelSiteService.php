<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AapanelServer;
use App\Models\IntegrationLog;

final class AapanelSiteService
{
    private function extractList(array $resp): ?array
    {
        if (isset($resp['data']) && is_array($resp['data'])) {
            if (isset($resp['data']['data']) && is_array($resp['data']['data'])) {
                return $resp['data']['data'];
            }
            if (isset($resp['data']['list']) && is_array($resp['data']['list'])) {
                return $resp['data']['list'];
            }
            if (isset($resp['data'][0])) {
                return $resp['data'];
            }
        }
        if (isset($resp['sites']) && is_array($resp['sites'])) {
            return $resp['sites'];
        }
        if (isset($resp['list']) && is_array($resp['list'])) {
            return $resp['list'];
        }
        if (isset($resp['items']) && is_array($resp['items'])) {
            return $resp['items'];
        }
        return null;
    }

    private function responseError(array $resp): ?string
    {
        if (isset($resp['error']) && is_string($resp['error']) && trim($resp['error']) !== '') {
            return trim($resp['error']);
        }
        if (isset($resp['msg']) && is_string($resp['msg']) && trim($resp['msg']) !== '') {
            $status = $resp['status'] ?? null;
            if ($status === false || $status === 0 || $status === '0') {
                return trim($resp['msg']);
            }
        }
        if (isset($resp['status']) && ($resp['status'] === false || $resp['status'] === 0 || $resp['status'] === '0')) {
            return 'Request failed';
        }
        if (isset($resp['http_status']) && (int)$resp['http_status'] >= 400) {
            return 'HTTP ' . (int)$resp['http_status'];
        }
        return null;
    }

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

    public function listSites(): array
    {
        $client = $this->client();
        if ($client === null) {
            return ['error' => 'No default server configured'];
        }

        $attempts = [
            ['/site?action=GetSiteList', ['p' => 1, 'limit' => 1000, 'type' => -1, 'search' => '']],
            ['/site?action=Websites', []],
        ];

        foreach ($attempts as [$path, $params]) {
            $resp = $client->request((string)$path, (array)$params);
            IntegrationLog::add('aapanel', 'site.list', 'endpoint', (string)$path, 'ok', null, ['params' => $params], $resp);

            $list = $this->extractList($resp);
            if (is_array($list)) {
                return ['items' => $list, 'raw' => $resp];
            }
        }

        $err = is_array($resp ?? null) ? $this->responseError((array)$resp) : null;
        return ['items' => [], 'raw' => $resp ?? [], 'error' => $err];
    }

    public function createSite(string $domain, ?string $path, ?string $phpVersion): array
    {
        $client = $this->client();
        if ($client === null) {
            $resp = ['error' => 'No default server configured'];
            IntegrationLog::add('aapanel', 'site.create', 'site', $domain, 'error', $resp['error'], null, $resp);
            return $resp;
        }

        $settings = new SettingsService();
        $wwwrootBase = (string)($settings->safeGet('aapanel_wwwroot_base') ?? '/www/wwwroot');

        $domain = trim($domain);
        $siteRootPath = $path !== null && trim($path) !== '' ? trim($path) : (rtrim($wwwrootBase, '/') . '/' . $domain);
        $phpVersion = $phpVersion !== null && trim($phpVersion) !== '' ? trim($phpVersion) : '74';

        $params = [
            'webname' => json_encode([
                'domain' => $domain,
                'domainlist' => [],
                'count' => 0,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'path' => $siteRootPath,
            'type_id' => 0,
            'type' => 'PHP',
            'php_version' => $phpVersion,
            'version' => '00',
            'port' => 80,
            'ftp' => true,
            'ftp_username' => '',
            'ftp_password' => '',
            'sql' => false,
            'codeing' => 'utf8',
        ];

        $resp = $client->request('/site?action=AddSite', $params);
        IntegrationLog::add('aapanel', 'site.create', 'site', $domain, 'ok', null, $params, $resp);
        return $resp;
    }

    public function startSite(?string $id, ?string $siteName): array
    {
        $client = $this->client();
        if ($client === null) {
            return ['error' => 'No default server configured'];
        }

        $params = [];
        if (is_string($id) && trim($id) !== '') {
            $params['id'] = trim($id);
        }
        if (is_string($siteName) && trim($siteName) !== '') {
            $params['siteName'] = trim($siteName);
        }

        $resp = $client->request('/site?action=StartSite', $params);
        IntegrationLog::add('aapanel', 'site.start', 'site', (string)($siteName ?? $id ?? ''), 'ok', null, $params, $resp);
        return $resp;
    }

    public function stopSite(?string $id, ?string $siteName): array
    {
        $client = $this->client();
        if ($client === null) {
            return ['error' => 'No default server configured'];
        }

        $params = [];
        if (is_string($id) && trim($id) !== '') {
            $params['id'] = trim($id);
        }
        if (is_string($siteName) && trim($siteName) !== '') {
            $params['siteName'] = trim($siteName);
        }

        $resp = $client->request('/site?action=StopSite', $params);
        IntegrationLog::add('aapanel', 'site.stop', 'site', (string)($siteName ?? $id ?? ''), 'ok', null, $params, $resp);
        return $resp;
    }

    public function deleteSite(?string $id, ?string $siteName, bool $deletePath = false, bool $deleteDb = false, bool $deleteFtp = false): array
    {
        $client = $this->client();
        if ($client === null) {
            return ['error' => 'No default server configured'];
        }

        $params = [
            'webname' => is_string($siteName) ? trim($siteName) : '',
            'id' => is_string($id) ? trim($id) : '',
            'path' => $deletePath ? 1 : 0,
            'database' => $deleteDb ? 1 : 0,
            'ftp' => $deleteFtp ? 1 : 0,
        ];

        $resp = $client->request('/site?action=DeleteSite', $params);
        IntegrationLog::add('aapanel', 'site.delete', 'site', (string)($siteName ?? $id ?? ''), 'ok', null, $params, $resp);
        return $resp;
    }
}

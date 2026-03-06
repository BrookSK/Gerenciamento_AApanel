<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AapanelServer;
use App\Models\IntegrationLog;

final class AapanelSiteService
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

            if (isset($resp['data']) && is_array($resp['data'])) {
                return ['items' => $resp['data'], 'raw' => $resp];
            }
            if (isset($resp['sites']) && is_array($resp['sites'])) {
                return ['items' => $resp['sites'], 'raw' => $resp];
            }
            if (isset($resp['list']) && is_array($resp['list'])) {
                return ['items' => $resp['list'], 'raw' => $resp];
            }
        }

        return ['items' => [], 'raw' => $resp ?? []];
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

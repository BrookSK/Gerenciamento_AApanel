<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AapanelServer;
use App\Models\IntegrationLog;
use App\Models\Subscription;
use App\Models\SubscriptionItem;

final class ProvisioningService
{
    public function provisionWordpress(int $subscriptionId, string $domain): void
    {
        $subscription = Subscription::find($subscriptionId);
        if ($subscription === null) {
            return;
        }

        $domain = trim($domain);
        if ($domain === '') {
            return;
        }

        $settings = new SettingsService();
        $wwwrootBase = (string)($settings->safeGet('aapanel_wwwroot_base') ?? '/www/wwwroot');
        $wpBasePath = (string)($settings->safeGet('wp_base_path') ?? '/desenvolvimento');

        $siteRootPath = rtrim($wwwrootBase, '/') . '/' . $domain;
        $wpInstallPath = rtrim($siteRootPath, '/') . '/' . ltrim($wpBasePath, '/');

        $itemId = SubscriptionItem::create(
            $subscriptionId,
            'site',
            $domain,
            null,
            [
                'requested_domain' => $domain,
                'site_root_path' => $siteRootPath,
                'wp_install_path' => $wpInstallPath,
                'provisioning' => 'wordpress',
            ],
            'active'
        );

        $server = $this->getDefaultAapanelServer();
        if ($server === null) {
            IntegrationLog::add('aapanel', 'wordpress.provision', 'subscription', (string)$subscriptionId, 'error', 'No default server configured', ['domain' => $domain], null);
            return;
        }

        $insecure = (string)($settings->safeGet('aapanel_insecure_ssl') ?? '');
        $verifySsl = $insecure !== '1';
        $client = new AapanelApiClient((string)$server['base_url'], (string)$server['api_key'], $verifySsl);

        $addSiteParams = [
            // NOTE: aaPanel geralmente usa webname em JSON no formato do BT.
            'webname' => json_encode([
                'domain' => $domain,
                'domainlist' => [],
                'count' => 0,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'path' => $siteRootPath,
            'type_id' => 0,
            'type' => 'PHP',
            'php_version' => '74',
            'version' => '00',
            'port' => 80,
            'ftp' => true,
            'ftp_username' => '',
            'ftp_password' => '',
            'sql' => false,
            'codeing' => 'utf8',
        ];

        $addSiteResp = $client->request('/site?action=AddSite', $addSiteParams);
        IntegrationLog::add('aapanel', 'site.create', 'subscription', (string)$subscriptionId, 'ok', null, $addSiteParams, $addSiteResp);

        $aapanelSiteId = null;
        if (isset($addSiteResp['siteId'])) {
            $aapanelSiteId = (string)$addSiteResp['siteId'];
        }
        if ($aapanelSiteId === null && isset($addSiteResp['id'])) {
            $aapanelSiteId = (string)$addSiteResp['id'];
        }

        $metadata = [
            'requested_domain' => $domain,
            'site_root_path' => $siteRootPath,
            'wp_install_path' => $wpInstallPath,
            'aapanel_add_site_response' => $addSiteResp,
        ];

        SubscriptionItem::updateAapanelResource($itemId, $aapanelSiteId, $metadata);

        // Tentativa best-effort de instalar WordPress. Caso o endpoint não exista no seu aaPanel,
        // o retorno ficará registrado em integration_logs.
        $wpParams = [
            'siteName' => $domain,
            'path' => $wpInstallPath,
        ];
        $wpResp = $client->request('/site?action=InstallWordPress', $wpParams);
        IntegrationLog::add('aapanel', 'wordpress.install', 'subscription', (string)$subscriptionId, 'ok', null, $wpParams, $wpResp);

        $metadata['aapanel_wp_install_response'] = $wpResp;
        SubscriptionItem::updateAapanelResource($itemId, $aapanelSiteId, $metadata);
    }

    public function suspendSubscription(int $subscriptionId): void
    {
        $subscription = Subscription::find($subscriptionId);
        if ($subscription === null) {
            return;
        }

        foreach (SubscriptionItem::allBySubscriptionId($subscriptionId) as $item) {
            if ((string)$item['resource_type'] === 'site') {
                $this->suspendSite((string)$item['aapanel_resource_id'], (string)($item['resource_name'] ?? ''), $subscriptionId);
            }
        }
    }

    public function activateSubscription(int $subscriptionId): void
    {
        $subscription = Subscription::find($subscriptionId);
        if ($subscription === null) {
            return;
        }

        foreach (SubscriptionItem::allBySubscriptionId($subscriptionId) as $item) {
            if ((string)$item['resource_type'] === 'site') {
                $this->activateSite((string)$item['aapanel_resource_id'], (string)($item['resource_name'] ?? ''), $subscriptionId);
            }
        }
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

    private function suspendSite(string $aapanelResourceId, string $domain, int $subscriptionId): void
    {
        $server = $this->getDefaultAapanelServer();
        if ($server === null) {
            IntegrationLog::add('aapanel', 'site.suspend', 'subscription', (string)$subscriptionId, 'error', 'No default server configured', ['domain' => $domain], null);
            return;
        }

        $settings = new SettingsService();
        $insecure = (string)($settings->safeGet('aapanel_insecure_ssl') ?? '');
        $verifySsl = $insecure !== '1';
        $client = new AapanelApiClient((string)$server['base_url'], (string)$server['api_key'], $verifySsl);

        $params = [];
        if ($aapanelResourceId !== '') {
            $params['id'] = $aapanelResourceId;
        }
        if ($domain !== '') {
            $params['siteName'] = $domain;
        }

        $resp = $client->request('/site?action=StopSite', $params);
        IntegrationLog::add('aapanel', 'site.suspend', 'subscription', (string)$subscriptionId, 'ok', null, $params, $resp);
    }

    private function activateSite(string $aapanelResourceId, string $domain, int $subscriptionId): void
    {
        $server = $this->getDefaultAapanelServer();
        if ($server === null) {
            IntegrationLog::add('aapanel', 'site.activate', 'subscription', (string)$subscriptionId, 'error', 'No default server configured', ['domain' => $domain], null);
            return;
        }

        $settings = new SettingsService();
        $insecure = (string)($settings->safeGet('aapanel_insecure_ssl') ?? '');
        $verifySsl = $insecure !== '1';
        $client = new AapanelApiClient((string)$server['base_url'], (string)$server['api_key'], $verifySsl);

        $params = [];
        if ($aapanelResourceId !== '') {
            $params['id'] = $aapanelResourceId;
        }
        if ($domain !== '') {
            $params['siteName'] = $domain;
        }

        $resp = $client->request('/site?action=StartSite', $params);
        IntegrationLog::add('aapanel', 'site.activate', 'subscription', (string)$subscriptionId, 'ok', null, $params, $resp);
    }
}

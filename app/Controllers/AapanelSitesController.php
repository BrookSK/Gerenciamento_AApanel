<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Controller;
use App\Core\Response;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use App\Services\AapanelSiteService;
use App\Services\ProvisioningService;

final class AapanelSitesController extends Controller
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

        $svc = new AapanelSiteService();
        $result = $svc->listSites();

        return $this->view('aapanel_sites/index', [
            'sites' => (array)($result['items'] ?? []),
            'raw' => $result['raw'] ?? null,
            'error' => $result['error'] ?? null,
            'subscriptions' => Subscription::allForSelect(),
            'linksByResourceName' => SubscriptionItem::linkedResourcesByType('site'),
        ]);
    }

    public function create(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        return $this->view('aapanel_sites/create', [
            'error' => null,
            'subscriptions' => Subscription::allForSelect(),
        ]);
    }

    public function store(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $domain = trim((string)($_POST['domain'] ?? ''));
        $path = trim((string)($_POST['path'] ?? ''));
        $phpVersion = trim((string)($_POST['php_version'] ?? ''));
        $subscriptionId = (int)($_POST['subscription_id'] ?? 0);
        $installWp = ((string)($_POST['install_wp'] ?? '') === '1');

        if ($domain === '') {
            return $this->view('aapanel_sites/create', [
                'error' => 'Informe o domínio',
                'subscriptions' => Subscription::allForSelect(),
            ]);
        }

        if ($installWp) {
            if ($subscriptionId <= 0) {
                return $this->view('aapanel_sites/create', [
                    'error' => 'Selecione a assinatura para provisionar WordPress',
                    'subscriptions' => Subscription::allForSelect(),
                ]);
            }

            $prov = new ProvisioningService();
            $prov->provisionWordpress($subscriptionId, $domain);

            return $this->redirect('/subscriptions/edit?id=' . $subscriptionId);
        }

        $svc = new AapanelSiteService();
        $resp = $svc->createSite($domain, $path !== '' ? $path : null, $phpVersion !== '' ? $phpVersion : null);

        if ($subscriptionId > 0) {
            $aapanelSiteId = null;
            if (isset($resp['siteId'])) {
                $aapanelSiteId = (string)$resp['siteId'];
            }
            if ($aapanelSiteId === null && isset($resp['id'])) {
                $aapanelSiteId = (string)$resp['id'];
            }

            SubscriptionItem::upsertLink($subscriptionId, 'site', $domain, $aapanelSiteId, [
                'linked_from' => 'aapanel_sites_create',
                'aapanel_response' => $resp,
            ]);
        }

        return $this->redirect('/aapanel-sites');
    }

    public function start(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $id = (string)($_POST['id'] ?? '');
        $siteName = (string)($_POST['site_name'] ?? '');

        $svc = new AapanelSiteService();
        $svc->startSite($id !== '' ? $id : null, $siteName !== '' ? $siteName : null);

        return $this->redirect('/aapanel-sites');
    }

    public function stop(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $id = (string)($_POST['id'] ?? '');
        $siteName = (string)($_POST['site_name'] ?? '');

        $svc = new AapanelSiteService();
        $svc->stopSite($id !== '' ? $id : null, $siteName !== '' ? $siteName : null);

        return $this->redirect('/aapanel-sites');
    }

    public function delete(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $id = (string)($_POST['id'] ?? '');
        $siteName = (string)($_POST['site_name'] ?? '');

        $deletePath = ((string)($_POST['delete_path'] ?? '') === '1');
        $deleteDb = ((string)($_POST['delete_db'] ?? '') === '1');
        $deleteFtp = ((string)($_POST['delete_ftp'] ?? '') === '1');

        $svc = new AapanelSiteService();
        $svc->deleteSite($id !== '' ? $id : null, $siteName !== '' ? $siteName : null, $deletePath, $deleteDb, $deleteFtp);

        return $this->redirect('/aapanel-sites');
    }

    public function link(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $subscriptionId = (int)($_POST['subscription_id'] ?? 0);
        $siteName = trim((string)($_POST['site_name'] ?? ''));
        $aapanelId = trim((string)($_POST['aapanel_id'] ?? ''));

        if ($subscriptionId <= 0 || $siteName === '') {
            return new Response(400, [], 'Bad Request');
        }

        SubscriptionItem::upsertLink($subscriptionId, 'site', $siteName, $aapanelId !== '' ? $aapanelId : null, [
            'linked_from' => 'aapanel_sites',
        ]);

        return $this->redirect('/aapanel-sites');
    }

    public function unlink(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $siteName = trim((string)($_POST['site_name'] ?? ''));
        if ($siteName === '') {
            return new Response(400, [], 'Bad Request');
        }

        SubscriptionItem::unlinkByResource('site', $siteName);
        return $this->redirect('/aapanel-sites');
    }
}

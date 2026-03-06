<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Controller;
use App\Core\Response;
use App\Services\AapanelSiteService;

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
        ]);
    }

    public function create(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        return $this->view('aapanel_sites/create', [
            'error' => null,
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

        if ($domain === '') {
            return $this->view('aapanel_sites/create', [
                'error' => 'Informe o domínio',
            ]);
        }

        $svc = new AapanelSiteService();
        $svc->createSite($domain, $path !== '' ? $path : null, $phpVersion !== '' ? $phpVersion : null);

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
}

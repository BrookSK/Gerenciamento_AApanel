<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Controller;
use App\Core\Response;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use App\Services\AapanelAdminMailService;

final class AapanelEmailsController extends Controller
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

        $svc = new AapanelAdminMailService();
        $result = $svc->listMailboxes();

        return $this->view('aapanel_emails/index', [
            'emails' => (array)($result['items'] ?? []),
            'raw' => $result['raw'] ?? null,
            'error' => $result['error'] ?? null,
            'subscriptions' => Subscription::allForSelect(),
            'linksByResourceName' => SubscriptionItem::linkedResourcesByType('email'),
        ]);
    }

    public function create(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        return $this->view('aapanel_emails/create', [
            'error' => null,
        ]);
    }

    public function store(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            return $this->view('aapanel_emails/create', [
                'error' => 'Informe e-mail e senha',
            ]);
        }

        $svc = new AapanelAdminMailService();
        $resp = $svc->createMailbox($email, $password);

        $ok = true;
        if (isset($resp['http_status']) && (int)$resp['http_status'] >= 400) {
            $ok = false;
        }
        if (isset($resp['status']) && ($resp['status'] === false || $resp['status'] === 0 || $resp['status'] === '0')) {
            $ok = false;
        }
        if (isset($resp['error']) && is_string($resp['error']) && trim($resp['error']) !== '') {
            $ok = false;
        }

        if (!$ok) {
            $msg = null;
            if (isset($resp['msg']) && is_string($resp['msg']) && trim($resp['msg']) !== '') {
                $msg = trim($resp['msg']);
            }
            if ($msg === null && isset($resp['error']) && is_string($resp['error']) && trim($resp['error']) !== '') {
                $msg = trim($resp['error']);
            }
            if ($msg === null) {
                $msg = 'Falha ao criar e-mail no aaPanel';
            }

            return $this->view('aapanel_emails/create', [
                'error' => $msg,
            ]);
        }

        return $this->redirect('/aapanel-emails');
    }

    public function delete(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $email = trim((string)($_POST['email'] ?? ''));
        if ($email !== '') {
            $svc = new AapanelAdminMailService();
            $svc->deleteMailbox($email);
        }

        return $this->redirect('/aapanel-emails');
    }

    public function changePassword(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            return new Response(400, [], 'Bad Request');
        }

        $svc = new AapanelAdminMailService();
        $svc->changePassword($email, $password);

        return $this->redirect('/aapanel-emails');
    }

    public function link(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $subscriptionId = (int)($_POST['subscription_id'] ?? 0);
        $email = trim((string)($_POST['email'] ?? ''));

        if ($subscriptionId <= 0 || $email === '') {
            return new Response(400, [], 'Bad Request');
        }

        SubscriptionItem::upsertLink($subscriptionId, 'email', $email, null, [
            'linked_from' => 'aapanel_emails',
        ]);

        return $this->redirect('/aapanel-emails');
    }

    public function unlink(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $email = trim((string)($_POST['email'] ?? ''));
        if ($email === '') {
            return new Response(400, [], 'Bad Request');
        }

        SubscriptionItem::unlinkByResource('email', $email);
        return $this->redirect('/aapanel-emails');
    }
}

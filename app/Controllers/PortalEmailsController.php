<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use App\Services\AapanelMailService;

final class PortalEmailsController extends Controller
{
    private function requirePortalAuth(): ?Response
    {
        $id = (int)($_SESSION['portal_client_id'] ?? 0);
        if ($id <= 0) {
            return Response::redirect('/portal/login');
        }
        return null;
    }

    public function index(): Response
    {
        if ($r = $this->requirePortalAuth()) {
            return $r;
        }

        $clientId = (int)$_SESSION['portal_client_id'];
        $subs = Subscription::allByClientId($clientId);

        $emails = [];
        foreach ($subs as $s) {
            foreach (SubscriptionItem::allBySubscriptionId((int)$s['id']) as $item) {
                if ((string)$item['resource_type'] === 'email') {
                    $emails[] = $item;
                }
            }
        }

        return $this->view('portal/emails/index', [
            'emails' => $emails,
        ]);
    }

    public function create(): Response
    {
        if ($r = $this->requirePortalAuth()) {
            return $r;
        }

        return $this->view('portal/emails/create', [
            'error' => null,
        ]);
    }

    public function store(): Response
    {
        if ($r = $this->requirePortalAuth()) {
            return $r;
        }

        $clientId = (int)$_SESSION['portal_client_id'];
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            return new Response(400, [], 'Bad Request');
        }

        $subs = Subscription::allByClientId($clientId);
        if (count($subs) === 0) {
            return new Response(400, [], 'No subscription');
        }

        $subscriptionId = (int)$subs[0]['id'];

        $planId = (int)($subs[0]['plan_id'] ?? 0);
        if ($planId > 0) {
            $plan = Plan::find($planId);
            if ($plan !== null) {
                $emailsMax = (int)($plan['emails_max'] ?? 0);
                if ($emailsMax > 0) {
                    $current = SubscriptionItem::countByClientIdAndType($clientId, 'email');
                    if ($current >= $emailsMax) {
                        return $this->view('portal/emails/create', [
                            'error' => 'Limite de e-mails atingido (plano permite ' . $emailsMax . ')',
                        ]);
                    }
                }
            }
        }

        $mail = new AapanelMailService();
        $resp = $mail->createMailbox($email, $password);

        SubscriptionItem::create($subscriptionId, 'email', $email, null, [
            'aapanel_response' => $resp,
        ], 'active');

        return Response::redirect('/portal/emails');
    }

    public function delete(): Response
    {
        if ($r = $this->requirePortalAuth()) {
            return $r;
        }

        $email = trim((string)($_POST['email'] ?? ''));
        if ($email === '') {
            return new Response(400, [], 'Bad Request');
        }

        $mail = new AapanelMailService();
        $mail->deleteMailbox($email);

        return Response::redirect('/portal/emails');
    }

    public function changePassword(): Response
    {
        if ($r = $this->requirePortalAuth()) {
            return $r;
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            return new Response(400, [], 'Bad Request');
        }

        $mail = new AapanelMailService();
        $mail->changePassword($email, $password);

        return Response::redirect('/portal/emails');
    }
}

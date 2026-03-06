<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Services\TicketNotificationService;
use App\Services\TicketStorageService;

final class PortalTicketsController extends Controller
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
        $tickets = Ticket::allByClientId($clientId);

        return $this->view('portal/tickets/index', [
            'tickets' => $tickets,
        ]);
    }

    public function create(): Response
    {
        if ($r = $this->requirePortalAuth()) {
            return $r;
        }

        return $this->view('portal/tickets/create', [
            'error' => null,
        ]);
    }

    public function store(): Response
    {
        if ($r = $this->requirePortalAuth()) {
            return $r;
        }

        $clientId = (int)$_SESSION['portal_client_id'];
        $subject = trim((string)($_POST['subject'] ?? ''));
        $type = trim((string)($_POST['type'] ?? ''));
        $message = trim((string)($_POST['message'] ?? ''));

        if ($subject === '' || $type === '' || $message === '') {
            return $this->view('portal/tickets/create', [
                'error' => 'Preencha todos os campos.',
            ]);
        }

        $ticketId = Ticket::create($clientId, $subject, $type);
        $messageId = TicketMessage::create($ticketId, 'client', $clientId, $message);

        $storage = new TicketStorageService();
        if (isset($_FILES['files']) && is_array($_FILES['files'])) {
            $infos = $storage->storeUploadedFiles($_FILES['files']);
            foreach ($infos as $info) {
                TicketAttachment::create($messageId, (string)$info['original_name'], (string)$info['stored_path'], $info['mime_type'], $info['size_bytes']);
            }
        }

        $notify = new TicketNotificationService();
        $notify->notifyNewTicket($ticketId);

        return Response::redirect('/portal/tickets/view?id=' . $ticketId);
    }

    public function show(): Response
    {
        if ($r = $this->requirePortalAuth()) {
            return $r;
        }

        $clientId = (int)$_SESSION['portal_client_id'];
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            return new Response(400, [], 'Bad Request');
        }

        $ticket = Ticket::find($id);
        if ($ticket === null || (int)$ticket['client_id'] !== $clientId) {
            return new Response(403, [], 'Forbidden');
        }

        $messages = TicketMessage::allByTicketId($id);
        $attachments = TicketAttachment::allByTicketId($id);

        $attachmentsByMessageId = [];
        foreach ($attachments as $a) {
            $mid = (int)($a['ticket_message_id'] ?? 0);
            if (!isset($attachmentsByMessageId[$mid])) {
                $attachmentsByMessageId[$mid] = [];
            }
            $attachmentsByMessageId[$mid][] = $a;
        }

        return $this->view('portal/tickets/view', [
            'ticket' => $ticket,
            'messages' => $messages,
            'attachments' => $attachments,
            'attachmentsByMessageId' => $attachmentsByMessageId,
            'error' => null,
        ]);
    }

    public function reply(): Response
    {
        if ($r = $this->requirePortalAuth()) {
            return $r;
        }

        $clientId = (int)$_SESSION['portal_client_id'];
        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $message = trim((string)($_POST['message'] ?? ''));
        if ($ticketId <= 0 || $message === '') {
            return new Response(400, [], 'Bad Request');
        }

        $ticket = Ticket::find($ticketId);
        if ($ticket === null || (int)$ticket['client_id'] !== $clientId) {
            return new Response(403, [], 'Forbidden');
        }

        TicketMessage::create($ticketId, 'client', $clientId, $message);
        return Response::redirect('/portal/tickets/view?id=' . $ticketId);
    }

    public function upload(): Response
    {
        if ($r = $this->requirePortalAuth()) {
            return $r;
        }

        $clientId = (int)$_SESSION['portal_client_id'];
        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $messageText = trim((string)($_POST['message'] ?? ''));
        if ($ticketId <= 0 || $messageText === '' || !isset($_FILES['files']) || !is_array($_FILES['files'])) {
            return new Response(400, [], 'Bad Request');
        }

        $ticket = Ticket::find($ticketId);
        if ($ticket === null || (int)$ticket['client_id'] !== $clientId) {
            return new Response(403, [], 'Forbidden');
        }

        $messageId = TicketMessage::create($ticketId, 'client', $clientId, $messageText);

        $storage = new TicketStorageService();
        $infos = $storage->storeUploadedFiles($_FILES['files']);
        foreach ($infos as $info) {
            TicketAttachment::create($messageId, (string)$info['original_name'], (string)$info['stored_path'], $info['mime_type'], $info['size_bytes']);
        }

        return Response::redirect('/portal/tickets/view?id=' . $ticketId);
    }

    public function downloadAttachment(): Response
    {
        if ($r = $this->requirePortalAuth()) {
            return $r;
        }

        $clientId = (int)$_SESSION['portal_client_id'];
        $attachmentId = (int)($_GET['id'] ?? 0);
        if ($attachmentId <= 0) {
            return new Response(400, [], 'Bad Request');
        }

        $att = TicketAttachment::find($attachmentId);
        if ($att === null) {
            return new Response(404, [], 'Not Found');
        }

        $msg = TicketMessage::find((int)$att['ticket_message_id']);
        if ($msg === null) {
            return new Response(404, [], 'Not Found');
        }

        $ticket = Ticket::find((int)$msg['ticket_id']);
        if ($ticket === null || (int)$ticket['client_id'] !== $clientId) {
            return new Response(403, [], 'Forbidden');
        }

        $storage = new TicketStorageService();
        $path = $storage->resolvePath((string)$att['stored_path']);
        if (!is_file($path)) {
            return new Response(404, [], 'Not Found');
        }

        $mime = (string)($att['mime_type'] ?? 'application/octet-stream');
        $name = (string)($att['original_name'] ?? 'file');

        $body = (string)file_get_contents($path);
        return new Response(200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'attachment; filename="' . addslashes($name) . '"',
        ], $body);
    }
}

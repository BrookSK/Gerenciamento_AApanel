<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Controller;
use App\Core\Response;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Services\TicketStorageService;

final class TicketsController extends Controller
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

        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 50);
        try {
            $data = Ticket::paginate($page, $perPage);
        } catch (\Throwable $e) {
            $data = [];
        }

        if (!is_array($data)) {
            $data = [];
        }

        if (!isset($data['items']) || !is_array($data['items'])) {
            $data['items'] = [];
        }

        return $this->view('tickets/index', [
            'data' => $data,
        ]);
    }

    public function show(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            return new Response(400, [], 'Bad Request');
        }

        $ticket = Ticket::find($id);
        if ($ticket === null) {
            return new Response(404, [], 'Not Found');
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

        return $this->view('tickets/view', [
            'ticket' => $ticket,
            'messages' => $messages,
            'attachments' => $attachments,
            'attachmentsByMessageId' => $attachmentsByMessageId,
        ]);
    }

    public function assignToMe(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $auth = Container::get('auth');
        $userId = (int)($auth->userId() ?? 0);
        if ($ticketId <= 0 || $userId <= 0) {
            return new Response(400, [], 'Bad Request');
        }

        Ticket::assignToUser($ticketId, $userId);
        return Response::redirect('/tickets/view?id=' . $ticketId);
    }

    public function reply(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $message = trim((string)($_POST['message'] ?? ''));
        $auth = Container::get('auth');
        $userId = (int)($auth->userId() ?? 0);

        if ($ticketId <= 0 || $message === '' || $userId <= 0) {
            return new Response(400, [], 'Bad Request');
        }

        $ticket = Ticket::find($ticketId);
        if ($ticket === null) {
            return new Response(404, [], 'Not Found');
        }

        TicketMessage::create($ticketId, 'user', $userId, $message);
        return Response::redirect('/tickets/view?id=' . $ticketId);
    }

    public function close(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        if ($ticketId <= 0) {
            return new Response(400, [], 'Bad Request');
        }

        Ticket::setStatus($ticketId, 'closed');
        return Response::redirect('/tickets/view?id=' . $ticketId);
    }

    public function upload(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $messageText = trim((string)($_POST['message'] ?? ''));
        $auth = Container::get('auth');
        $userId = (int)($auth->userId() ?? 0);

        if ($ticketId <= 0 || $messageText === '' || $userId <= 0 || !isset($_FILES['files']) || !is_array($_FILES['files'])) {
            return new Response(400, [], 'Bad Request');
        }

        $ticket = Ticket::find($ticketId);
        if ($ticket === null) {
            return new Response(404, [], 'Not Found');
        }

        $messageId = TicketMessage::create($ticketId, 'user', $userId, $messageText);

        $storage = new TicketStorageService();
        $infos = $storage->storeUploadedFiles($_FILES['files']);
        foreach ($infos as $info) {
            TicketAttachment::create($messageId, (string)$info['original_name'], (string)$info['stored_path'], $info['mime_type'], $info['size_bytes']);
        }

        return Response::redirect('/tickets/view?id=' . $ticketId);
    }

    public function downloadAttachment(): Response
    {
        if ($r = $this->requireAuth()) {
            return $r;
        }

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
        if ($ticket === null) {
            return new Response(404, [], 'Not Found');
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

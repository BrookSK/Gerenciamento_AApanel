<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Client;
use App\Models\Ticket;

final class TicketNotificationService
{
    private SettingsService $settings;
    private MailService $mail;

    public function __construct(?SettingsService $settings = null, ?MailService $mail = null)
    {
        $this->settings = $settings ?? new SettingsService();
        $this->mail = $mail ?? new MailService();
    }

    public function notifyNewTicket(int $ticketId): void
    {
        $emailsRaw = (string)($this->settings->safeGet('ticket_notify_emails') ?? '');
        $emails = array_filter(array_map('trim', explode(',', $emailsRaw)), fn($x) => is_string($x) && $x !== '');
        if (count($emails) === 0) {
            return;
        }

        $ticket = Ticket::find($ticketId);
        if ($ticket === null) {
            return;
        }

        $client = Client::find((int)$ticket['client_id']);

        $subject = 'Novo chamado #' . (int)$ticket['id'] . ' - ' . (string)$ticket['subject'];
        $body = "Novo chamado aberto\n\n";
        $body .= "Ticket: #" . (int)$ticket['id'] . "\n";
        $body .= "Assunto: " . (string)$ticket['subject'] . "\n";
        $body .= "Tipo: " . (string)$ticket['type'] . "\n";
        $body .= "Status: " . (string)$ticket['status'] . "\n";
        if ($client !== null) {
            $body .= "Cliente: " . (string)$client['name'] . "\n";
            $body .= "Email: " . (string)($client['email'] ?? '') . "\n";
        }
        $body .= "\nAcesse o painel para responder.\n";

        $this->mail->sendMany($emails, $subject, $body, null);
    }
}

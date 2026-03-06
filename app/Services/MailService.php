<?php

declare(strict_types=1);

namespace App\Services;

final class MailService
{
    public function send(string $to, string $subject, string $body, ?string $from = null): bool
    {
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/plain; charset=utf-8';
        if (is_string($from) && trim($from) !== '') {
            $headers[] = 'From: ' . trim($from);
        }

        return mail($to, $subject, $body, implode("\r\n", $headers));
    }

    public function sendMany(array $emails, string $subject, string $body, ?string $from = null): void
    {
        foreach ($emails as $e) {
            if (!is_string($e)) {
                continue;
            }
            $e = trim($e);
            if ($e === '') {
                continue;
            }
            $this->send($e, $subject, $body, $from);
        }
    }
}

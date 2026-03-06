<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public int $statusCode;
    public array $headers;
    public string $body;

    public function __construct(int $statusCode = 200, array $headers = [], string $body = '')
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->body = $body;
    }

    public static function redirect(string $to): self
    {
        return new self(302, ['Location' => $to], '');
    }
}

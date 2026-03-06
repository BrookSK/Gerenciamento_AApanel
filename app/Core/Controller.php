<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $template, array $data = []): Response
    {
        $html = View::render($template, $data);
        return new Response(200, ['Content-Type' => 'text/html; charset=utf-8'], $html);
    }

    protected function redirect(string $to): Response
    {
        return Response::redirect($to);
    }
}

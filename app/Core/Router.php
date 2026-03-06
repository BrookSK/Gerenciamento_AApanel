<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    public function dispatch(string $method, string $path): Response
    {
        $method = strtoupper($method);
        $path = $this->normalize($path);

        $handler = $this->routes[$method][$path] ?? null;
        if ($handler === null) {
            return new Response(404, [], 'Not Found');
        }

        [$class, $action] = $handler;
        if (!class_exists($class)) {
            return new Response(500, [], 'Controller not found');
        }

        $controller = new $class();
        if (!method_exists($controller, $action)) {
            return new Response(500, [], 'Action not found');
        }

        $result = $controller->{$action}();

        if ($result instanceof Response) {
            return $result;
        }

        return new Response(200, [], (string)$result);
    }

    private function normalize(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        return $path;
    }
}

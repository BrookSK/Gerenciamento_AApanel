<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $template, array $data = []): string
    {
        $basePath = dirname(__DIR__, 2);
        $viewPath = $basePath . '/app/Views/' . ltrim($template, '/') . '.php';

        if (!is_file($viewPath)) {
            throw new \RuntimeException('View not found: ' . $viewPath);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        return (string)ob_get_clean();
    }
}

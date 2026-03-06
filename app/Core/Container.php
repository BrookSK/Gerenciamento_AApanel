<?php

declare(strict_types=1);

namespace App\Core;

final class Container
{
    private static array $instances = [];

    public static function set(string $key, mixed $instance): void
    {
        self::$instances[$key] = $instance;
    }

    public static function get(string $key): mixed
    {
        if (!array_key_exists($key, self::$instances)) {
            throw new \RuntimeException('Container key not found: ' . $key);
        }

        return self::$instances[$key];
    }
}

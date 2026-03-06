<?php

declare(strict_types=1);

namespace App\Core;

final class MigrationCreator
{
    private string $migrationsPath;

    public function __construct(string $migrationsPath)
    {
        $this->migrationsPath = rtrim($migrationsPath, "\\/ ");

        if (!is_dir($this->migrationsPath)) {
            if (!mkdir($this->migrationsPath, 0777, true) && !is_dir($this->migrationsPath)) {
                throw new \RuntimeException('Unable to create migrations dir');
            }
        }
    }

    public function create(string $name): string
    {
        $name = $this->slug($name);
        $next = $this->nextSequence();
        $file = sprintf('%s/%04d_%s.sql', $this->migrationsPath, $next, $name);

        if (file_exists($file)) {
            throw new \RuntimeException('Migration already exists');
        }

        $content = '';
        if (file_put_contents($file, $content) === false) {
            throw new \RuntimeException('Unable to write migration');
        }

        return $file;
    }

    private function nextSequence(): int
    {
        $max = 0;
        $files = scandir($this->migrationsPath);
        if (!is_array($files)) {
            return 1;
        }

        foreach ($files as $file) {
            if (!is_string($file)) {
                continue;
            }

            if (preg_match('/^(\d{4})_.*\.sql$/', $file, $m)) {
                $n = (int)$m[1];
                if ($n > $max) {
                    $max = $n;
                }
            }

            if (preg_match('/^(\d{4})\.sql$/', $file, $m2)) {
                $n = (int)$m2[1];
                if ($n > $max) {
                    $max = $n;
                }
            }

            if (preg_match('/^(\d{4})_.*$/', $file, $m3)) {
                $n = (int)$m3[1];
                if ($n > $max) {
                    $max = $n;
                }
            }
        }

        return $max + 1;
    }

    private function slug(string $value): string
    {
        $value = trim($value);
        $value = mb_strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/i', '_', $value);
        $value = trim((string)$value, '_');

        if ($value === '') {
            return 'migration';
        }

        return $value;
    }
}

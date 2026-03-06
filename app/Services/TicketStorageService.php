<?php

declare(strict_types=1);

namespace App\Services;

final class TicketStorageService
{
    private int $maxBytes = 10485760;

    public function baseDir(): string
    {
        $root = dirname(__DIR__, 2);
        return $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'tickets';
    }

    public function ensureDirs(): void
    {
        $base = $this->baseDir();
        if (!is_dir($base)) {
            mkdir($base, 0775, true);
        }
    }

    public function storeUploadedFile(array $file): ?array
    {
        if (!isset($file['tmp_name'], $file['name'])) {
            return null;
        }

        $tmp = (string)$file['tmp_name'];
        $orig = (string)$file['name'];
        if ($tmp === '' || $orig === '' || !is_uploaded_file($tmp)) {
            return null;
        }

        if (isset($file['size']) && is_numeric($file['size']) && (int)$file['size'] > $this->maxBytes) {
            return null;
        }

        $this->ensureDirs();

        $safeName = preg_replace('/[^a-zA-Z0-9._-]+/', '_', $orig);
        if (!is_string($safeName) || $safeName === '') {
            $safeName = 'file';
        }

        $ext = '';
        $pos = strrpos($safeName, '.');
        if ($pos !== false) {
            $ext = substr($safeName, $pos);
            if (!is_string($ext)) {
                $ext = '';
            }
        }

        $rand = bin2hex(random_bytes(16));
        $stored = date('Ymd') . '_' . $rand . $ext;

        $dest = $this->baseDir() . DIRECTORY_SEPARATOR . $stored;
        if (!move_uploaded_file($tmp, $dest)) {
            return null;
        }

        $mime = null;
        if (function_exists('mime_content_type')) {
            $m = @mime_content_type($dest);
            if (is_string($m) && $m !== '') {
                $mime = $m;
            }
        }

        $size = filesize($dest);
        $sizeBytes = is_int($size) ? $size : null;

        return [
            'original_name' => $orig,
            'stored_path' => $stored,
            'mime_type' => $mime,
            'size_bytes' => $sizeBytes,
        ];
    }

    public function storeUploadedFiles(array $files): array
    {
        $out = [];

        if (!isset($files['name'], $files['tmp_name']) || !is_array($files['name']) || !is_array($files['tmp_name'])) {
            $one = $this->storeUploadedFile($files);
            if ($one !== null) {
                $out[] = $one;
            }
            return $out;
        }

        $names = $files['name'];
        $tmps = $files['tmp_name'];
        $sizes = isset($files['size']) && is_array($files['size']) ? $files['size'] : [];
        $types = isset($files['type']) && is_array($files['type']) ? $files['type'] : [];

        foreach ($names as $i => $n) {
            $file = [
                'name' => $n,
                'tmp_name' => $tmps[$i] ?? null,
                'size' => $sizes[$i] ?? null,
                'type' => $types[$i] ?? null,
            ];
            $stored = $this->storeUploadedFile($file);
            if ($stored !== null) {
                $out[] = $stored;
            }
        }

        return $out;
    }

    public function resolvePath(string $storedPath): string
    {
        return $this->baseDir() . DIRECTORY_SEPARATOR . $storedPath;
    }
}

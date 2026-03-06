<?php

declare(strict_types=1);

namespace App\Services;

final class HttpClient
{
    public function postForm(string $url, array $data, array $headers = [], int $timeoutSeconds = 30): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new \RuntimeException('Unable to init curl');
        }

        $baseHeaders = [
            'Content-Type: application/x-www-form-urlencoded',
        ];
        foreach ($headers as $h) {
            $baseHeaders[] = $h;
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => $baseHeaders,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeoutSeconds,
        ]);

        $raw = curl_exec($ch);
        if ($raw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('HTTP request failed: ' . $err);
        }

        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [$status, (string)$raw];
    }

    public function postJson(string $url, array $data, array $headers = [], int $timeoutSeconds = 30): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new \RuntimeException('Unable to init curl');
        }

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json)) {
            $json = '{}';
        }

        $baseHeaders = [
            'Content-Type: application/json',
        ];
        foreach ($headers as $h) {
            $baseHeaders[] = $h;
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => $baseHeaders,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeoutSeconds,
        ]);

        $raw = curl_exec($ch);
        if ($raw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('HTTP request failed: ' . $err);
        }

        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [$status, (string)$raw];
    }
}

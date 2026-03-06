<?php

declare(strict_types=1);

namespace App\Services;

final class AsaasApiClient
{
    private string $baseUrl;
    private string $accessToken;
    private HttpClient $http;

    public function __construct(string $accessToken, string $baseUrl = 'https://www.asaas.com/api/v3', ?HttpClient $http = null)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->accessToken = $accessToken;
        $this->http = $http ?? new HttpClient();
    }

    public function post(string $path, array $params = []): array
    {
        $url = $this->baseUrl . '/' . ltrim($path, '/');

        $headers = [
            'access_token: ' . $this->accessToken,
        ];

        [$status, $raw] = $this->http->postForm($url, $params, $headers);

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [
                'http_status' => $status,
                'raw' => $raw,
            ];
        }

        $decoded['http_status'] = $status;
        return $decoded;
    }

    public function postJson(string $path, array $payload = []): array
    {
        $url = $this->baseUrl . '/' . ltrim($path, '/');

        $headers = [
            'access_token: ' . $this->accessToken,
        ];

        [$status, $raw] = $this->http->postJson($url, $payload, $headers);

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [
                'http_status' => $status,
                'raw' => $raw,
            ];
        }

        $decoded['http_status'] = $status;
        return $decoded;
    }
}

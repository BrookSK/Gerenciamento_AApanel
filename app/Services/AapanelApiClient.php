<?php

declare(strict_types=1);

namespace App\Services;

final class AapanelApiClient
{
    private string $baseUrl;
    private string $apiKey;
    private HttpClient $http;
    private bool $verifySsl;

    public function __construct(string $baseUrl, string $apiKey, bool $verifySsl = true, ?HttpClient $http = null)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->verifySsl = $verifySsl;
        $this->http = $http ?? new HttpClient();
    }

    public function request(string $path, array $params = []): array
    {
        $url = $this->baseUrl . '/' . ltrim($path, '/');

        $payload = $params;
        $payload['request_time'] = time();
        $payload['request_token'] = md5($this->apiKey . $payload['request_time']);

        $curlOptions = [];
        if (!$this->verifySsl && str_starts_with(strtolower($url), 'https://')) {
            $curlOptions = [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ];
        }

        [$status, $raw] = $this->http->postForm($url, $payload, [], 30, $curlOptions);

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

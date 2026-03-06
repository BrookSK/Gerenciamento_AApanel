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
        $urls = [$url];

        if (!str_contains($url, '/api/') && !str_ends_with($this->baseUrl, '/api')) {
            $urls[] = rtrim($this->baseUrl, '/') . '/api/' . ltrim($path, '/');
        }

        $curlOptions = [];
        $firstUrl = $urls[0];
        if (!$this->verifySsl && str_starts_with(strtolower($firstUrl), 'https://')) {
            $curlOptions = [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ];
        }

        $requestTime = (string)time();

        $tokenCandidates = [
            md5($this->apiKey . $requestTime),
            md5($requestTime . md5($this->apiKey)),
            md5(md5($this->apiKey) . $requestTime),
        ];

        $lastStatus = 0;
        $lastRaw = '';

        foreach ($urls as $u) {
            foreach ($tokenCandidates as $token) {
                $payload = $params;
                $payload['request_time'] = $requestTime;
                $payload['request_token'] = $token;

                [$status, $raw] = $this->http->postForm($u, $payload, [], 30, $curlOptions);
                $lastStatus = (int)$status;
                $lastRaw = (string)$raw;

                $decoded = json_decode($raw, true);
                if (!is_array($decoded)) {
                    if ($status === 404) {
                        continue;
                    }
                    return [
                        'http_status' => $status,
                        'raw' => $raw,
                    ];
                }

                $decoded['http_status'] = $status;

                $msg = isset($decoded['msg']) && is_string($decoded['msg']) ? strtolower(trim($decoded['msg'])) : '';
                if ($status === 404) {
                    continue;
                }
                if ($msg !== '' && str_contains($msg, 'secret key verification failed')) {
                    continue;
                }

                return $decoded;
            }
        }

        $decoded = json_decode($lastRaw, true);
        if (is_array($decoded)) {
            $decoded['http_status'] = $lastStatus;
            return $decoded;
        }

        return [
            'http_status' => $lastStatus,
            'raw' => $lastRaw,
        ];
    }
}

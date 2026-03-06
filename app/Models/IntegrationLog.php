<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Container;

final class IntegrationLog
{
    public static function add(
        string $provider,
        string $action,
        ?string $referenceType,
        ?string $referenceId,
        string $status,
        ?string $message,
        ?array $request,
        ?array $response
    ): void {
        try {
            $db = Container::get('db')->pdo();
            $req = $request === null ? null : json_encode($request, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $res = $response === null ? null : json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $stmt = $db->prepare('INSERT INTO integration_logs (provider, action, reference_type, reference_id, status, message, request_json, response_json) VALUES (:provider, :action, :reference_type, :reference_id, :status, :message, :request_json, :response_json)');
            $stmt->execute([
                'provider' => $provider,
                'action' => $action,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'status' => $status,
                'message' => $message,
                'request_json' => is_string($req) ? $req : null,
                'response_json' => is_string($res) ? $res : null,
            ]);
        } catch (\Throwable) {
        }
    }

    public static function paginate(int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(200, $perPage));
        $offset = ($page - 1) * $perPage;

        $db = Container::get('db')->pdo();

        $totalStmt = $db->query('SELECT COUNT(*) AS cnt FROM integration_logs');
        $totalRow = $totalStmt->fetch();
        $total = (int)($totalRow['cnt'] ?? 0);

        $stmt = $db->prepare('SELECT id, provider, action, reference_type, reference_id, status, message, request_json, response_json, created_at FROM integration_logs ORDER BY id DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue('limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => (int)ceil($total / $perPage),
        ];
    }
}

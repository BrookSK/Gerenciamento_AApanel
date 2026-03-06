<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Container;

final class SubscriptionItem
{
    public static function allBySubscriptionId(int $subscriptionId): array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT id, subscription_id, resource_type, resource_name, aapanel_resource_id, metadata_json, status FROM subscription_items WHERE subscription_id = :sid ORDER BY id ASC');
        $stmt->execute(['sid' => $subscriptionId]);
        return $stmt->fetchAll();
    }

    public static function findBySubscriptionIdAndResource(int $subscriptionId, string $resourceType, string $resourceName): ?array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT id, subscription_id, resource_type, resource_name, aapanel_resource_id, metadata_json, status FROM subscription_items WHERE subscription_id = :sid AND resource_type = :resource_type AND resource_name = :resource_name LIMIT 1');
        $stmt->execute([
            'sid' => $subscriptionId,
            'resource_type' => $resourceType,
            'resource_name' => $resourceName,
        ]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function create(int $subscriptionId, string $resourceType, ?string $resourceName, ?string $aapanelResourceId, ?array $metadata, string $status = 'active'): int
    {
        $db = Container::get('db')->pdo();
        $json = $metadata === null ? null : json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $stmt = $db->prepare('INSERT INTO subscription_items (subscription_id, resource_type, resource_name, aapanel_resource_id, metadata_json, status) VALUES (:subscription_id, :resource_type, :resource_name, :aapanel_resource_id, :metadata_json, :status)');
        $stmt->execute([
            'subscription_id' => $subscriptionId,
            'resource_type' => $resourceType,
            'resource_name' => $resourceName,
            'aapanel_resource_id' => $aapanelResourceId,
            'metadata_json' => is_string($json) ? $json : null,
            'status' => $status,
        ]);

        return (int)$db->lastInsertId();
    }

    public static function updateAapanelResource(int $id, ?string $aapanelResourceId, ?array $metadata): void
    {
        $db = Container::get('db')->pdo();
        $json = $metadata === null ? null : json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $stmt = $db->prepare('UPDATE subscription_items SET aapanel_resource_id = :aapanel_resource_id, metadata_json = :metadata_json, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'aapanel_resource_id' => $aapanelResourceId,
            'metadata_json' => is_string($json) ? $json : null,
        ]);
    }

    public static function countByClientIdAndType(int $clientId, string $resourceType): int
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT COUNT(*) AS cnt FROM subscription_items si INNER JOIN subscriptions s ON s.id = si.subscription_id WHERE s.client_id = :client_id AND si.resource_type = :resource_type');
        $stmt->execute([
            'client_id' => $clientId,
            'resource_type' => $resourceType,
        ]);
        $row = $stmt->fetch();
        return (int)($row['cnt'] ?? 0);
    }

    public static function linkedResourcesByType(string $resourceType): array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT si.id AS subscription_item_id, si.subscription_id, si.resource_type, si.resource_name, si.aapanel_resource_id, si.status, s.title AS subscription_title, s.client_id, c.name AS client_name FROM subscription_items si INNER JOIN subscriptions s ON s.id = si.subscription_id INNER JOIN clients c ON c.id = s.client_id WHERE si.resource_type = :resource_type ORDER BY si.id DESC');
        $stmt->execute(['resource_type' => $resourceType]);
        $rows = $stmt->fetchAll();

        $out = [];
        foreach ($rows as $r) {
            $key = (string)($r['resource_name'] ?? '');
            if ($key === '') {
                continue;
            }
            $out[$key] = $r;
        }
        return $out;
    }

    public static function upsertLink(int $subscriptionId, string $resourceType, string $resourceName, ?string $aapanelResourceId, ?array $metadata = null): int
    {
        $db = Container::get('db')->pdo();
        $resourceName = trim($resourceName);
        if ($resourceName === '') {
            throw new \RuntimeException('resource_name is required');
        }

        $del = $db->prepare('DELETE FROM subscription_items WHERE resource_type = :resource_type AND resource_name = :resource_name');
        $del->execute([
            'resource_type' => $resourceType,
            'resource_name' => $resourceName,
        ]);

        return self::create($subscriptionId, $resourceType, $resourceName, $aapanelResourceId, $metadata, 'active');
    }

    public static function unlinkByResource(string $resourceType, string $resourceName): void
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('DELETE FROM subscription_items WHERE resource_type = :resource_type AND resource_name = :resource_name');
        $stmt->execute([
            'resource_type' => $resourceType,
            'resource_name' => trim($resourceName),
        ]);
    }
}

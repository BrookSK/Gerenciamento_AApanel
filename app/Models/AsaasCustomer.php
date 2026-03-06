<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Container;

final class AsaasCustomer
{
    public static function findByClientId(int $clientId): ?array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT id, client_id, asaas_customer_id FROM asaas_customers WHERE client_id = :cid LIMIT 1');
        $stmt->execute(['cid' => $clientId]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function upsert(int $clientId, string $asaasCustomerId): void
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('INSERT INTO asaas_customers (client_id, asaas_customer_id) VALUES (:client_id, :asaas_customer_id) ON DUPLICATE KEY UPDATE asaas_customer_id = VALUES(asaas_customer_id), updated_at = CURRENT_TIMESTAMP');
        $stmt->execute([
            'client_id' => $clientId,
            'asaas_customer_id' => $asaasCustomerId,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Container;

final class AsaasEvent
{
    public static function store(string $eventId, ?string $type, array $payload): bool
    {
        $db = Container::get('db')->pdo();
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json)) {
            $json = '{}';
        }

        try {
            $stmt = $db->prepare('INSERT INTO asaas_events (event_id, event_type, payload_json) VALUES (:event_id, :event_type, :payload_json)');
            $stmt->execute([
                'event_id' => $eventId,
                'event_type' => $type,
                'payload_json' => $json,
            ]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}

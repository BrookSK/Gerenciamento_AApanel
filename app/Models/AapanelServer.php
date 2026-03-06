<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Container;

final class AapanelServer
{
    public static function all(): array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->query('SELECT id, name, base_url, api_key, status FROM aapanel_servers ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT id, name, base_url, api_key, status FROM aapanel_servers WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function create(array $data): int
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('INSERT INTO aapanel_servers (name, base_url, api_key, status) VALUES (:name, :base_url, :api_key, :status)');
        $stmt->execute([
            'name' => (string)($data['name'] ?? ''),
            'base_url' => (string)($data['base_url'] ?? ''),
            'api_key' => (string)($data['api_key'] ?? ''),
            'status' => (string)($data['status'] ?? 'active'),
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('UPDATE aapanel_servers SET name = :name, base_url = :base_url, api_key = :api_key, status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'name' => (string)($data['name'] ?? ''),
            'base_url' => (string)($data['base_url'] ?? ''),
            'api_key' => (string)($data['api_key'] ?? ''),
            'status' => (string)($data['status'] ?? 'active'),
        ]);
    }

    public static function delete(int $id): void
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('DELETE FROM aapanel_servers WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Container;

final class Plan
{
    public static function all(): array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->query('SELECT id, name, description, price, billing_cycle, emails_max, wordpress_sites_max, status FROM plans ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public static function allActive(): array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->query("SELECT id, name, price, billing_cycle, emails_max, wordpress_sites_max, status FROM plans WHERE status = 'active' ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT id, name, description, price, billing_cycle, emails_max, wordpress_sites_max, status FROM plans WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function create(array $data): int
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('INSERT INTO plans (name, description, price, billing_cycle, emails_max, wordpress_sites_max, status) VALUES (:name, :description, :price, :billing_cycle, :emails_max, :wordpress_sites_max, :status)');
        $stmt->execute([
            'name' => (string)($data['name'] ?? ''),
            'description' => ($data['description'] ?? null) !== '' ? (string)$data['description'] : null,
            'price' => (string)($data['price'] ?? '0.00'),
            'billing_cycle' => (string)($data['billing_cycle'] ?? 'monthly'),
            'emails_max' => (int)($data['emails_max'] ?? 0),
            'wordpress_sites_max' => (int)($data['wordpress_sites_max'] ?? 0),
            'status' => (string)($data['status'] ?? 'active'),
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('UPDATE plans SET name = :name, description = :description, price = :price, billing_cycle = :billing_cycle, emails_max = :emails_max, wordpress_sites_max = :wordpress_sites_max, status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'name' => (string)($data['name'] ?? ''),
            'description' => ($data['description'] ?? null) !== '' ? (string)$data['description'] : null,
            'price' => (string)($data['price'] ?? '0.00'),
            'billing_cycle' => (string)($data['billing_cycle'] ?? 'monthly'),
            'emails_max' => (int)($data['emails_max'] ?? 0),
            'wordpress_sites_max' => (int)($data['wordpress_sites_max'] ?? 0),
            'status' => (string)($data['status'] ?? 'active'),
        ]);
    }

    public static function delete(int $id): void
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('DELETE FROM plans WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}

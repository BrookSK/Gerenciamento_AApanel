<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Container;

final class Subscription
{
    public static function all(): array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->query('SELECT s.id, s.client_id, s.plan_id, c.name AS client_name, s.title, s.plan_type, s.price, s.billing_cycle, s.status, s.next_due_date FROM subscriptions s INNER JOIN clients c ON c.id = s.client_id ORDER BY s.id DESC');
        return $stmt->fetchAll();
    }

    public static function allByClientId(int $clientId): array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT id, client_id, plan_id, title, plan_type, price, billing_cycle, status, next_due_date FROM subscriptions WHERE client_id = :client_id ORDER BY id DESC');
        $stmt->execute(['client_id' => $clientId]);
        return $stmt->fetchAll();
    }

    public static function findByAsaasSubscriptionId(string $asaasSubscriptionId): ?array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT id, client_id, plan_id, title, plan_type, price, billing_cycle, status, start_date, next_due_date, asaas_subscription_id FROM subscriptions WHERE asaas_subscription_id = :sid LIMIT 1');
        $stmt->execute(['sid' => $asaasSubscriptionId]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function setStatus(int $id, string $status): void
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('UPDATE subscriptions SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'status' => $status,
        ]);
    }

    public static function setPlanId(int $id, int $planId): void
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('UPDATE subscriptions SET plan_id = :plan_id, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'plan_id' => $planId,
        ]);
    }

    public static function find(int $id): ?array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT id, client_id, plan_id, title, plan_type, price, billing_cycle, status, start_date, next_due_date, asaas_subscription_id FROM subscriptions WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function create(array $data): int
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('INSERT INTO subscriptions (client_id, plan_id, title, plan_type, price, billing_cycle, status, start_date, next_due_date) VALUES (:client_id, :plan_id, :title, :plan_type, :price, :billing_cycle, :status, :start_date, :next_due_date)');
        $stmt->execute([
            'client_id' => (int)($data['client_id'] ?? 0),
            'plan_id' => ($data['plan_id'] ?? null) !== '' ? (int)$data['plan_id'] : null,
            'title' => (string)($data['title'] ?? ''),
            'plan_type' => (string)($data['plan_type'] ?? ''),
            'price' => (string)($data['price'] ?? '0.00'),
            'billing_cycle' => (string)($data['billing_cycle'] ?? 'monthly'),
            'status' => (string)($data['status'] ?? 'active'),
            'start_date' => ($data['start_date'] ?? null) !== '' ? (string)$data['start_date'] : null,
            'next_due_date' => ($data['next_due_date'] ?? null) !== '' ? (string)$data['next_due_date'] : null,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('UPDATE subscriptions SET client_id = :client_id, plan_id = :plan_id, title = :title, plan_type = :plan_type, price = :price, billing_cycle = :billing_cycle, status = :status, start_date = :start_date, next_due_date = :next_due_date, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'client_id' => (int)($data['client_id'] ?? 0),
            'plan_id' => ($data['plan_id'] ?? null) !== '' ? (int)$data['plan_id'] : null,
            'title' => (string)($data['title'] ?? ''),
            'plan_type' => (string)($data['plan_type'] ?? ''),
            'price' => (string)($data['price'] ?? '0.00'),
            'billing_cycle' => (string)($data['billing_cycle'] ?? 'monthly'),
            'status' => (string)($data['status'] ?? 'active'),
            'start_date' => ($data['start_date'] ?? null) !== '' ? (string)$data['start_date'] : null,
            'next_due_date' => ($data['next_due_date'] ?? null) !== '' ? (string)$data['next_due_date'] : null,
        ]);
    }

    public static function delete(int $id): void
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('DELETE FROM subscriptions WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}

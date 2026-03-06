<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Container;

final class Ticket
{
    public static function create(int $clientId, string $subject, string $type): int
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('INSERT INTO tickets (client_id, subject, type, status) VALUES (:client_id, :subject, :type, "open")');
        $stmt->execute([
            'client_id' => $clientId,
            'subject' => $subject,
            'type' => $type,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT id, client_id, assigned_user_id, subject, type, status, created_at, updated_at, closed_at FROM tickets WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function allByClientId(int $clientId): array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT id, client_id, assigned_user_id, subject, type, status, created_at, updated_at FROM tickets WHERE client_id = :client_id ORDER BY id DESC');
        $stmt->execute(['client_id' => $clientId]);
        return $stmt->fetchAll();
    }

    public static function paginate(int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(200, $perPage));
        $offset = ($page - 1) * $perPage;

        $db = Container::get('db')->pdo();
        $totalStmt = $db->query('SELECT COUNT(*) AS cnt FROM tickets');
        $totalRow = $totalStmt->fetch();
        $total = (int)($totalRow['cnt'] ?? 0);

        $stmt = $db->prepare('SELECT t.id, t.client_id, c.name AS client_name, t.assigned_user_id, t.subject, t.type, t.status, t.created_at FROM tickets t INNER JOIN clients c ON c.id = t.client_id ORDER BY t.id DESC LIMIT :limit OFFSET :offset');
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

    public static function assignToUser(int $ticketId, int $userId): void
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('UPDATE tickets SET assigned_user_id = :uid, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([
            'id' => $ticketId,
            'uid' => $userId,
        ]);
    }

    public static function setStatus(int $ticketId, string $status): void
    {
        $db = Container::get('db')->pdo();

        $closedAtSql = '';
        if ($status === 'closed') {
            $closedAtSql = ', closed_at = CURRENT_TIMESTAMP';
        }

        $stmt = $db->prepare('UPDATE tickets SET status = :status, updated_at = CURRENT_TIMESTAMP' . $closedAtSql . ' WHERE id = :id');
        $stmt->execute([
            'id' => $ticketId,
            'status' => $status,
        ]);
    }
}

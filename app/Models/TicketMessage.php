<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Container;

final class TicketMessage
{
    public static function create(int $ticketId, string $senderType, ?int $senderId, string $messageText): int
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('INSERT INTO ticket_messages (ticket_id, sender_type, sender_id, message_text) VALUES (:ticket_id, :sender_type, :sender_id, :message_text)');
        $stmt->execute([
            'ticket_id' => $ticketId,
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'message_text' => $messageText,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function allByTicketId(int $ticketId): array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT id, ticket_id, sender_type, sender_id, message_text, created_at FROM ticket_messages WHERE ticket_id = :ticket_id ORDER BY id ASC');
        $stmt->execute(['ticket_id' => $ticketId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT id, ticket_id, sender_type, sender_id, message_text, created_at FROM ticket_messages WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
}

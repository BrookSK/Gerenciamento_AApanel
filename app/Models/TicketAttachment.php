<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Container;

final class TicketAttachment
{
    public static function create(int $ticketMessageId, string $originalName, string $storedPath, ?string $mimeType, ?int $sizeBytes): int
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('INSERT INTO ticket_attachments (ticket_message_id, original_name, stored_path, mime_type, size_bytes) VALUES (:ticket_message_id, :original_name, :stored_path, :mime_type, :size_bytes)');
        $stmt->execute([
            'ticket_message_id' => $ticketMessageId,
            'original_name' => $originalName,
            'stored_path' => $storedPath,
            'mime_type' => $mimeType,
            'size_bytes' => $sizeBytes,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function allByTicketId(int $ticketId): array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT a.id, a.ticket_message_id, a.original_name, a.stored_path, a.mime_type, a.size_bytes, a.created_at FROM ticket_attachments a INNER JOIN ticket_messages m ON m.id = a.ticket_message_id WHERE m.ticket_id = :ticket_id ORDER BY a.id ASC');
        $stmt->execute(['ticket_id' => $ticketId]);
        return $stmt->fetchAll();
    }

    public static function allByMessageId(int $ticketMessageId): array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT id, ticket_message_id, original_name, stored_path, mime_type, size_bytes, created_at FROM ticket_attachments WHERE ticket_message_id = :mid ORDER BY id ASC');
        $stmt->execute(['mid' => $ticketMessageId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT id, ticket_message_id, original_name, stored_path, mime_type, size_bytes, created_at FROM ticket_attachments WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
}

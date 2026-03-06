<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Container;

final class Client
{
    public static function all(): array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->query('SELECT id, name, email, portal_email, portal_enabled, document, phone, status FROM clients ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT id, name, email, portal_email, portal_enabled, document, phone, status FROM clients WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function findForPortalLogin(string $email): ?array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT id, portal_email, portal_password_hash, portal_enabled FROM clients WHERE portal_email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function touchPortalLastLogin(int $id): void
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('UPDATE clients SET portal_last_login_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function create(array $data): int
    {
        $db = Container::get('db')->pdo();

        $portalPassword = (string)($data['portal_password'] ?? '');
        $portalPasswordHash = $portalPassword !== '' ? password_hash($portalPassword, PASSWORD_DEFAULT) : null;

        $stmt = $db->prepare('INSERT INTO clients (name, email, portal_email, portal_password_hash, portal_enabled, document, phone, status) VALUES (:name, :email, :portal_email, :portal_password_hash, :portal_enabled, :document, :phone, :status)');
        $stmt->execute([
            'name' => (string)($data['name'] ?? ''),
            'email' => ($data['email'] ?? null) !== '' ? (string)$data['email'] : null,
            'portal_email' => ($data['portal_email'] ?? null) !== '' ? (string)$data['portal_email'] : null,
            'portal_password_hash' => $portalPasswordHash,
            'portal_enabled' => isset($data['portal_enabled']) ? (int)$data['portal_enabled'] : 1,
            'document' => ($data['document'] ?? null) !== '' ? (string)$data['document'] : null,
            'phone' => ($data['phone'] ?? null) !== '' ? (string)$data['phone'] : null,
            'status' => (string)($data['status'] ?? 'active'),
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $db = Container::get('db')->pdo();

        $portalPassword = (string)($data['portal_password'] ?? '');
        $portalPasswordHash = $portalPassword !== '' ? password_hash($portalPassword, PASSWORD_DEFAULT) : null;

        $sql = 'UPDATE clients SET name = :name, email = :email, portal_email = :portal_email, portal_enabled = :portal_enabled, document = :document, phone = :phone, status = :status, updated_at = CURRENT_TIMESTAMP';
        $params = [
            'id' => $id,
            'name' => (string)($data['name'] ?? ''),
            'email' => ($data['email'] ?? null) !== '' ? (string)$data['email'] : null,
            'portal_email' => ($data['portal_email'] ?? null) !== '' ? (string)$data['portal_email'] : null,
            'portal_enabled' => isset($data['portal_enabled']) ? (int)$data['portal_enabled'] : 1,
            'document' => ($data['document'] ?? null) !== '' ? (string)$data['document'] : null,
            'phone' => ($data['phone'] ?? null) !== '' ? (string)$data['phone'] : null,
            'status' => (string)($data['status'] ?? 'active'),
        ];

        if ($portalPasswordHash !== null) {
            $sql .= ', portal_password_hash = :portal_password_hash';
            $params['portal_password_hash'] = $portalPasswordHash;
        }

        $sql .= ' WHERE id = :id';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('DELETE FROM clients WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}

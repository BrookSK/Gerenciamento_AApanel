<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Container;

final class User
{
    public static function findByEmail(string $email): ?array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT id, email, password_hash FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return $row;
    }
}

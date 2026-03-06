<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    public function check(): bool
    {
        return isset($_SESSION['user_id']) && is_int($_SESSION['user_id']);
    }

    public function userId(): ?int
    {
        if (!$this->check()) {
            return null;
        }

        return (int)$_SESSION['user_id'];
    }

    public function attempt(string $email, string $password): bool
    {
        $user = User::findByEmail($email);
        if ($user === null) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        $_SESSION['user_id'] = (int)$user['id'];
        return true;
    }

    public function logout(): void
    {
        unset($_SESSION['user_id']);
    }
}

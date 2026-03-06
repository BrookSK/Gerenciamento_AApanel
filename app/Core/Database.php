<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class Database
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        $host = (string)($config['host'] ?? '127.0.0.1');
        $port = (int)($config['port'] ?? 3306);
        $db = (string)($config['database'] ?? '');
        $user = (string)($config['username'] ?? '');
        $pass = (string)($config['password'] ?? '');
        $charset = (string)($config['charset'] ?? 'utf8mb4');

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
        $this->pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}

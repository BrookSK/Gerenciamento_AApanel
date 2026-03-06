<?php

declare(strict_types=1);

return [
    'app' => [
        'base_path' => dirname(__DIR__),
        'base_url' => getenv('APP_BASE_URL') ?: (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
            . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
        ),
        'display_errors' => true,
        'session_name' => 'gaap_session',
    ],
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'gerenciamento_aapanel',
        'username' => 'gerenciamento_aapanel',
        'password' => '@DLVzg!sakumt076',
        'charset' => 'utf8mb4',
    ],
];

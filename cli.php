<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Core\MigrationCreator;

$argv = $_SERVER['argv'] ?? [];
$command = $argv[1] ?? null;

if ($command === null) {
    fwrite(STDERR, "Missing command\n");
    exit(1);
}

switch ($command) {
    case 'make:migration':
        $name = (string)($argv[2] ?? 'migration');
        $creator = new MigrationCreator(__DIR__ . '/migrations');
        $file = $creator->create($name);
        fwrite(STDOUT, $file . "\n");
        exit(0);

    default:
        fwrite(STDERR, "Unknown command\n");
        exit(1);
}

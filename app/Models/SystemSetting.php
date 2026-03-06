<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Container;

final class SystemSetting
{
    public static function get(string $key): ?string
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('SELECT setting_value FROM system_settings WHERE setting_key = :k LIMIT 1');
        $stmt->execute(['k' => $key]);
        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }
        return $row['setting_value'];
    }

    public static function set(string $key, ?string $value): void
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->prepare('INSERT INTO system_settings (setting_key, setting_value) VALUES (:k, :v) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP');
        $stmt->execute([
            'k' => $key,
            'v' => $value,
        ]);
    }

    public static function all(): array
    {
        $db = Container::get('db')->pdo();
        $stmt = $db->query('SELECT setting_key, setting_value FROM system_settings ORDER BY setting_key ASC');
        $rows = $stmt->fetchAll();

        $out = [];
        foreach ($rows as $r) {
            $out[(string)$r['setting_key']] = $r['setting_value'];
        }

        return $out;
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SystemSetting;

final class SettingsService
{
    public function safeLoadAll(): array
    {
        try {
            return SystemSetting::all();
        } catch (\Throwable) {
            return [];
        }
    }

    public function safeGet(string $key): ?string
    {
        try {
            return SystemSetting::get($key);
        } catch (\Throwable) {
            return null;
        }
    }

    public function safeSet(string $key, ?string $value): void
    {
        try {
            SystemSetting::set($key, $value);
        } catch (\Throwable) {
        }
    }
}

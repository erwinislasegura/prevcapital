<?php

declare(strict_types=1);

namespace App\Core;

final class Config
{
    private static array $items = [];

    public static function load(string $name, string $path): void
    {
        self::$items[$name] = require $path;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        [$group, $item] = array_pad(explode('.', $key, 2), 2, null);
        if ($item === null) {
            return self::$items[$group] ?? $default;
        }

        return self::$items[$group][$item] ?? $default;
    }
}

<?php

declare(strict_types=1);

namespace App\Utilities;

final class ConfigHelper
{
    public static function int(string $key, int $default): int
    {
        $value = config($key, $default);

        return is_numeric($value) ? (int) $value : $default;
    }
}

<?php

declare(strict_types=1);

namespace App\Utilities;

final class LanguageUtil
{
    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        $config = config('languages');

        if (! is_array($config)) {
            return []; // @codeCoverageIgnore
        }

        /** @var array<string, string> */
        return $config;
    }

    /**
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function get(string $code): ?string
    {
        return self::all()[$code] ?? null;
    }

    public static function has(string $code): bool
    {
        return isset(self::all()[$code]);
    }

    public static function default(): string
    {
        return 'en';
    }
}

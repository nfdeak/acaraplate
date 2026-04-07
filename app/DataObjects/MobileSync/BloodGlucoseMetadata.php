<?php

declare(strict_types=1);

namespace App\DataObjects\MobileSync;

use Spatie\LaravelData\Data;

final class BloodGlucoseMetadata extends Data
{
    public function __construct(
        public string $glucoseReadingType = 'random',
    ) {}

    /**
     * @param  array<string, mixed>|null  $raw
     * @return array<string, mixed>
     */
    public static function normalize(?array $raw): array
    {
        /** @var array<string, mixed> $source */
        $source = $raw ?? [];

        $instance = self::from($source);

        /** @var array<string, mixed> $normalized */
        $normalized = $instance->toArray();

        $unknown = array_diff_key($source, array_flip(['glucoseReadingType', 'glucose_reading_type']));

        return [...$unknown, ...$normalized];
    }
}

<?php

declare(strict_types=1);

namespace App\DataObjects\MobileSync;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\CamelCaseMapper;

#[MapInputName(CamelCaseMapper::class)]
final class MedicationDoseEventMetadata extends Data
{
    public function __construct(
        public ?string $medicationName = null,
        public ?string $logStatus = null,
    ) {}

    /**
     * @param  array<string, mixed>|null  $raw
     * @return array<string, mixed>|null
     */
    public static function normalize(?array $raw): ?array
    {
        if ($raw === null || $raw === []) {
            return null;
        }

        $instance = self::from($raw);

        /** @var array<string, mixed> $normalized */
        $normalized = array_filter($instance->toArray(), fn (mixed $v): bool => $v !== null);

        $unknown = array_diff_key($raw, array_flip(['medicationName', 'logStatus']));

        $merged = [...$unknown, ...$normalized];

        return $merged !== [] ? $merged : null;
    }
}

<?php

declare(strict_types=1);

use App\Enums\WeightUnit;

covers(WeightUnit::class);

it('returns same value for kg', function (): void {
    expect(WeightUnit::Kg->toKg(75.0))->toBe(75.0);
});

it('converts lb to kg', function (): void {
    $result = WeightUnit::Lb->toKg(100.0);

    expect($result)->toBe(45.359237);
});

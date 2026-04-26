<?php

declare(strict_types=1);

use App\Actions\CalculateCaffeineSafeDose;
use App\Data\SafeDoseData;

covers(CalculateCaffeineSafeDose::class);

it('exposes the base mg per kg and sensitivity multiplier constants', function (): void {
    expect(CalculateCaffeineSafeDose::BASE_MG_PER_KG)->toBe(5.7)
        ->and(CalculateCaffeineSafeDose::SENSITIVITY_MULTIPLIERS)
        ->toBe([0.7, 0.85, 1.0, 1.15, 1.3]);
});

it('returns a deterministic SafeDoseData with safe_mg and cups', function (): void {
    $action = new CalculateCaffeineSafeDose;

    $result = $action->handle(weightKg: 70.0, sensitivityStep: 2, perCupMg: 95.0);

    $expectedSafeMg = 70.0 * 5.7 * 1.0;
    $expectedCups = (int) floor($expectedSafeMg / 95.0);

    expect($result)->toBeInstanceOf(SafeDoseData::class)
        ->and($result->safeMg)->toBe($expectedSafeMg)
        ->and($result->cups)->toBe($expectedCups);
});

it('floors the cups based on weight, multiplier, and per cup mg', function (int $step, float $multiplier): void {
    $action = new CalculateCaffeineSafeDose;
    $weightKg = 80.0;
    $perCupMg = 100.0;

    $result = $action->handle($weightKg, $step, $perCupMg);

    $expectedSafeMg = $weightKg * CalculateCaffeineSafeDose::BASE_MG_PER_KG * $multiplier;
    expect($result->safeMg)->toBe($expectedSafeMg)
        ->and($result->cups)->toBe((int) floor($expectedSafeMg / $perCupMg));
})->with([
    [0, 0.7],
    [1, 0.85],
    [2, 1.0],
    [3, 1.15],
    [4, 1.3],
]);

it('rejects an out-of-range sensitivity step', function (): void {
    $action = new CalculateCaffeineSafeDose;

    $action->handle(weightKg: 70.0, sensitivityStep: 5, perCupMg: 95.0);
})->throws(InvalidArgumentException::class);

it('rejects non-positive weight or per cup mg', function (float $weightKg, float $perCupMg): void {
    $action = new CalculateCaffeineSafeDose;

    $action->handle($weightKg, 2, $perCupMg);
})->with([
    [0.0, 95.0],
    [-1.0, 95.0],
    [70.0, 0.0],
    [70.0, -10.0],
])->throws(InvalidArgumentException::class);

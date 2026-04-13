<?php

declare(strict_types=1);

use App\Actions\CollectAffectedUtcDatesAction;
use Carbon\CarbonImmutable;

covers(CollectAffectedUtcDatesAction::class);

it('collects a single UTC date when start equals end', function (): void {
    $action = new CollectAffectedUtcDatesAction;
    $affected = [];

    $action->handle(
        CarbonImmutable::parse('2026-04-05 12:00:00 UTC'),
        CarbonImmutable::parse('2026-04-05 18:00:00 UTC'),
        $affected,
    );

    expect($affected)->toHaveCount(1)
        ->and($affected)->toHaveKey('2026-04-05');
});

it('collects multiple UTC dates for a range', function (): void {
    $action = new CollectAffectedUtcDatesAction;
    $affected = [];

    $action->handle(
        CarbonImmutable::parse('2026-04-05 UTC'),
        CarbonImmutable::parse('2026-04-07 UTC'),
        $affected,
    );

    expect($affected)->toHaveCount(3)
        ->and($affected)->toHaveKeys(['2026-04-05', '2026-04-06', '2026-04-07']);
});

it('uses start date when end is null', function (): void {
    $action = new CollectAffectedUtcDatesAction;
    $affected = [];

    $action->handle(
        CarbonImmutable::parse('2026-04-05 14:30:00 UTC'),
        null,
        $affected,
    );

    expect($affected)->toHaveCount(1)
        ->and($affected)->toHaveKey('2026-04-05');
});

it('clamps range end to range start when end precedes start in UTC', function (): void {
    $action = new CollectAffectedUtcDatesAction;
    $affected = [];

    $action->handle(
        CarbonImmutable::parse('2026-04-05 UTC'),
        CarbonImmutable::parse('2026-04-03 UTC'),
        $affected,
    );

    expect($affected)->toHaveCount(1)
        ->and($affected)->toHaveKey('2026-04-05');
});

it('accumulates into an existing map without overwriting', function (): void {
    $action = new CollectAffectedUtcDatesAction;
    $affected = ['2026-04-04' => true];

    $action->handle(
        CarbonImmutable::parse('2026-04-05 UTC'),
        CarbonImmutable::parse('2026-04-06 UTC'),
        $affected,
    );

    expect($affected)->toHaveCount(3)
        ->and($affected)->toHaveKeys(['2026-04-04', '2026-04-05', '2026-04-06']);
});

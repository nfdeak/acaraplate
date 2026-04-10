<?php

declare(strict_types=1);

use App\Console\Commands\RevalidateHealthAggregatesCommand;
use App\Models\HealthDailyAggregate;
use App\Models\HealthSyncSample;
use App\Models\User;
use App\Services\HealthMetricRegistry;

covers(RevalidateHealthAggregatesCommand::class);

it('outputs no stale aggregates found when none exist', function (): void {
    $this->artisan(RevalidateHealthAggregatesCommand::class)
        ->expectsOutput('No stale aggregates found.')
        ->assertSuccessful();
});

it('revalidates stale aggregates with version below current', function (): void {
    $user = User::factory()->create(['timezone' => 'UTC']);

    HealthDailyAggregate::factory()
        ->for($user)
        ->stepCount()
        ->create([
            'local_date' => '2026-04-01',
            'aggregation_version' => 0,
        ]);

    HealthSyncSample::factory()
        ->for($user)
        ->stepCount()
        ->create([
            'measured_at' => '2026-04-01 12:00:00',
            'value' => 5000,
        ]);

    $this->artisan(RevalidateHealthAggregatesCommand::class)
        ->expectsOutputToContain('Found 1 (user, date) tuples to revalidate.')
        ->expectsOutputToContain('Revalidated')
        ->assertSuccessful();
});

it('skips aggregates at current version', function (): void {
    $user = User::factory()->create();

    HealthDailyAggregate::factory()
        ->for($user)
        ->create([
            'local_date' => '2026-04-01',
            'aggregation_version' => HealthMetricRegistry::CURRENT_AGGREGATION_VERSION,
        ]);

    $this->artisan(RevalidateHealthAggregatesCommand::class)
        ->expectsOutput('No stale aggregates found.')
        ->assertSuccessful();
});

it('filters by user option', function (): void {
    $targetUser = User::factory()->create(['timezone' => 'UTC']);
    $otherUser = User::factory()->create(['timezone' => 'UTC']);

    HealthDailyAggregate::factory()
        ->for($targetUser)
        ->create(['local_date' => '2026-04-01', 'aggregation_version' => 0]);

    HealthDailyAggregate::factory()
        ->for($otherUser)
        ->create(['local_date' => '2026-04-01', 'aggregation_version' => 0]);

    $this->artisan(RevalidateHealthAggregatesCommand::class, [
        '--user' => $targetUser->id,
    ])->expectsOutputToContain('Found 1 (user, date) tuples to revalidate.')
        ->assertSuccessful();
});

it('filters by since option', function (): void {
    $user = User::factory()->create(['timezone' => 'UTC']);

    HealthDailyAggregate::factory()
        ->for($user)
        ->create(['local_date' => '2026-03-01', 'aggregation_version' => 0]);

    HealthDailyAggregate::factory()
        ->for($user)
        ->create(['local_date' => '2026-04-01', 'aggregation_version' => 0]);

    $this->artisan(RevalidateHealthAggregatesCommand::class, [
        '--since' => '2026-04-01',
    ])->expectsOutputToContain('Found 1 (user, date) tuples to revalidate.')
        ->assertSuccessful();
});

it('respects min-version option', function (): void {
    $user = User::factory()->create(['timezone' => 'UTC']);

    HealthDailyAggregate::factory()
        ->for($user)
        ->create([
            'local_date' => '2026-04-01',
            'aggregation_version' => HealthMetricRegistry::CURRENT_AGGREGATION_VERSION,
        ]);

    $this->artisan(RevalidateHealthAggregatesCommand::class, [
        '--min-version' => HealthMetricRegistry::CURRENT_AGGREGATION_VERSION + 1,
    ])->expectsOutputToContain('Found 1 (user, date) tuples to revalidate.')
        ->assertSuccessful();
});

<?php

declare(strict_types=1);

use App\Actions\DispatchAggregateUserUtcDatesAction;
use App\Jobs\AggregateUserDayJob;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

covers(DispatchAggregateUserUtcDatesAction::class);

it('dispatches aggregate jobs for each unique UTC date', function (): void {
    Queue::fake();
    $user = User::factory()->create();
    $action = new DispatchAggregateUserUtcDatesAction;

    $count = $action->handle($user, ['2026-04-05', '2026-04-06', '2026-04-05']);

    expect($count)->toBe(2);
    Queue::assertPushed(AggregateUserDayJob::class, 2);
});

it('filters out empty date strings', function (): void {
    Queue::fake();
    $user = User::factory()->create();
    $action = new DispatchAggregateUserUtcDatesAction;

    $count = $action->handle($user, ['2026-04-05', '', '']);

    expect($count)->toBe(1);
    Queue::assertPushed(AggregateUserDayJob::class, 1);
});

it('returns zero and dispatches nothing when all dates are empty', function (): void {
    Queue::fake();
    $user = User::factory()->create();
    $action = new DispatchAggregateUserUtcDatesAction;

    $count = $action->handle($user, ['', '']);

    expect($count)->toBe(0);
    Queue::assertNotPushed(AggregateUserDayJob::class);
});

<?php

declare(strict_types=1);

use App\Actions\GetAiUsageForBillingAction;
use App\Models\AiUsage;
use App\Models\User;

covers(GetAiUsageForBillingAction::class);

it('returns usage data for user with no usage', function (): void {
    $user = User::factory()->create();

    $action = new GetAiUsageForBillingAction();
    $result = $action->handle($user);

    expect($result)->toBeArray()
        ->toHaveKeys(['rolling', 'weekly', 'monthly'])
        ->and($result['rolling']['current'])->toBe(0)
        ->and($result['weekly']['current'])->toBe(0)
        ->and($result['monthly']['current'])->toBe(0)
        ->and($result['rolling']['percentage'])->toBe(0)
        ->and($result['rolling']['resets_in'])->toBeString()
        ->and($result['weekly']['resets_in'])->toBeString()
        ->and($result['monthly']['resets_in'])->toBeString();
});

it('calculates correct usage for user with AI usage', function (): void {
    $user = User::factory()->create();

    AiUsage::factory()->create([
        'user_id' => $user->id,
        'cost' => 0.50,
    ]);

    $action = new GetAiUsageForBillingAction();
    $result = $action->handle($user);

    expect($result['rolling']['current'])->toBe(500)
        ->and($result['rolling']['limit'])->toBe(500)
        ->and($result['rolling']['percentage'])->toBe(100);
});

it('calculates percentage correctly for different limits', function (): void {
    $user = User::factory()->create();

    AiUsage::factory()->create([
        'user_id' => $user->id,
        'cost' => 0.25,
    ]);

    $action = new GetAiUsageForBillingAction();
    $result = $action->handle($user);

    expect($result['rolling']['percentage'])->toBe(50)
        ->and($result['weekly']['percentage'])->toBe(10)
        ->and($result['monthly']['percentage'])->toBe(5);
});

it('caps percentage at 100', function (): void {
    $user = User::factory()->create();

    AiUsage::factory()->create([
        'user_id' => $user->id,
        'cost' => 1.50,
    ]);

    $action = new GetAiUsageForBillingAction();
    $result = $action->handle($user);

    expect($result['rolling']['percentage'])->toBe(100);
});

it('returns usage data for guest user', function (): void {
    $action = new GetAiUsageForBillingAction();
    $result = $action->handle(new User());

    expect($result)->toBeArray()
        ->toHaveKeys(['rolling', 'weekly', 'monthly'])
        ->and($result['rolling']['current'])->toBe(0);
});

it('returns credits as integers using configured multiplier', function (): void {
    $user = User::factory()->create();

    AiUsage::factory()->create([
        'user_id' => $user->id,
        'cost' => 0.007,
    ]);

    $action = new GetAiUsageForBillingAction();
    $result = $action->handle($user);

    expect($result['rolling']['current'])->toBe(7)
        ->and($result['rolling']['current'])->toBeInt()
        ->and($result['rolling']['limit'])->toBeInt()
        ->and($result['rolling']['limit'])->toBe(500)
        ->and($result['weekly']['limit'])->toBe(2500)
        ->and($result['monthly']['limit'])->toBe(5000);
});

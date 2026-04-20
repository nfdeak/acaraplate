<?php

declare(strict_types=1);

use App\Enums\DietType;
use App\Http\Requests\StoreMealPlanRequest;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;

covers(StoreMealPlanRequest::class);

$validate = function (array $data): ValidatorContract {
    $request = new StoreMealPlanRequest;

    return Validator::make($data, $request->rules());
};

it('authorizes when a user is bound to the request', function (): void {
    $request = new StoreMealPlanRequest;
    $request->setUserResolver(fn () => User::factory()->make());

    expect($request->authorize())->toBeTrue();
});

it('rejects an unauthenticated request', function (): void {
    $request = new StoreMealPlanRequest;
    $request->setUserResolver(fn (): null => null);

    expect($request->authorize())->toBeFalse();
});

it('passes validation with a minimal valid payload', function () use ($validate): void {
    expect($validate(['duration_days' => 3])->passes())->toBeTrue();
});

it('passes validation with all optional fields', function () use ($validate): void {
    expect(
        $validate([
            'duration_days' => 7,
            'diet_type' => DietType::Mediterranean->value,
            'prompt' => 'Focus on high-fiber meals',
        ])->passes()
    )->toBeTrue();
});

it('requires duration_days', function () use ($validate): void {
    $v = $validate([]);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('duration_days'))->toBeTrue();
});

it('rejects out-of-range duration_days', function (int $invalid) use ($validate): void {
    $v = $validate(['duration_days' => $invalid]);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('duration_days'))->toBeTrue();
})->with([0, -1, 8, 30]);

it('rejects non-integer duration_days', function () use ($validate): void {
    $v = $validate(['duration_days' => 'seven']);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('duration_days'))->toBeTrue();
});

it('accepts a null diet_type', function () use ($validate): void {
    expect($validate(['duration_days' => 3, 'diet_type' => null])->passes())->toBeTrue();
});

it('rejects an unknown diet_type', function () use ($validate): void {
    $v = $validate(['duration_days' => 3, 'diet_type' => 'carnivore-extreme']);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('diet_type'))->toBeTrue();
});

it('rejects a prompt longer than 2000 characters', function () use ($validate): void {
    $v = $validate([
        'duration_days' => 3,
        'prompt' => str_repeat('a', 2001),
    ]);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('prompt'))->toBeTrue();
});

<?php

declare(strict_types=1);

use App\Ai\Tools\GetHealthEntries;
use App\Enums\HealthEntrySource;
use App\Models\HealthSyncSample;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Ai\Tools\Request;
use Tests\Helpers\TestJsonSchema;

beforeEach(function (): void {
    $this->tool = new GetHealthEntries();
});

it('has correct name and description', function (): void {
    expect($this->tool->name())->toBe('get_health_entries')
        ->and($this->tool->description())->toContain('logged health entries');
});

it('has valid schema', function (): void {
    $schema = new TestJsonSchema;

    $result = $this->tool->schema($schema);

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['date', 'days', 'type']);
});

it('returns error if user is not authenticated', function (): void {
    $request = new Request;
    $result = $this->tool->handle($request);
    $json = json_decode((string) $result, true);

    expect($json)->toHaveKey('error', 'User not authenticated');
});

it('returns entries for today', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $groupId = (string) Str::uuid();

    HealthSyncSample::factory()->carbohydrates()->create([
        'user_id' => $user->id,
        'value' => 27,
        'measured_at' => now(),
        'entry_source' => HealthEntrySource::Telegram,
        'group_id' => $groupId,
    ]);

    $groupId2 = (string) Str::uuid();

    HealthSyncSample::factory()->carbohydrates()->create([
        'user_id' => $user->id,
        'value' => 21,
        'measured_at' => now(),
        'entry_source' => HealthEntrySource::Telegram,
        'group_id' => $groupId2,
    ]);

    $request = new Request;
    $result = $this->tool->handle($request);
    $json = json_decode((string) $result, true);

    expect($json)->toHaveKey('success', true)
        ->and($json['total_entries'])->toBe(2)
        ->and($json['entries'])->toHaveCount(2)
        ->and($json['entries'][0])->toHaveKey('carbs_grams');
});

it('returns entries filtered by food type', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    HealthSyncSample::factory()->carbohydrates()->create([
        'user_id' => $user->id,
        'value' => 27,
        'measured_at' => now(),
    ]);

    HealthSyncSample::factory()->bloodGlucose()->create([
        'user_id' => $user->id,
        'value' => 140.0,
        'measured_at' => now(),
    ]);

    $request = new Request(['type' => 'food']);
    $result = $this->tool->handle($request);
    $json = json_decode((string) $result, true);

    expect($json)->toHaveKey('success', true)
        ->and($json['total_entries'])->toBe(1)
        ->and($json['entries'][0])->toHaveKey('carbs_grams', 27.0);
});

it('returns entries for a date range', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    HealthSyncSample::factory()->carbohydrates()->create([
        'user_id' => $user->id,
        'value' => 50,
        'measured_at' => now()->subDays(2),
    ]);

    HealthSyncSample::factory()->carbohydrates()->create([
        'user_id' => $user->id,
        'value' => 30,
        'measured_at' => now(),
    ]);

    $request = new Request(['days' => 3]);
    $result = $this->tool->handle($request);
    $json = json_decode((string) $result, true);

    expect($json)->toHaveKey('success', true)
        ->and($json['total_entries'])->toBe(2);
});

it('returns empty array when no entries exist', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $request = new Request;
    $result = $this->tool->handle($request);
    $json = json_decode((string) $result, true);

    expect($json)->toHaveKey('success', true)
        ->and($json['total_entries'])->toBe(0)
        ->and($json['entries'])->toBeEmpty();
});

it('does not return entries from other users', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $this->actingAs($user);

    HealthSyncSample::factory()->carbohydrates()->create([
        'user_id' => $otherUser->id,
        'value' => 50,
        'measured_at' => now(),
    ]);

    $request = new Request;
    $result = $this->tool->handle($request);
    $json = json_decode((string) $result, true);

    expect($json)->toHaveKey('success', true)
        ->and($json['total_entries'])->toBe(0);
});

it('formats glucose entries correctly', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    HealthSyncSample::factory()->bloodGlucose()->create([
        'user_id' => $user->id,
        'value' => 140.5,
        'metadata' => ['glucose_reading_type' => 'post-meal'],
        'measured_at' => now(),
    ]);

    $request = new Request(['type' => 'glucose']);
    $result = $this->tool->handle($request);
    $json = json_decode((string) $result, true);

    expect($json['entries'][0])->toHaveKey('glucose_value', 140.5)
        ->and($json['entries'][0])->toHaveKey('glucose_reading_type', 'post-meal');
});

it('formats vitals entries correctly - weight', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    HealthSyncSample::factory()->weight()->create([
        'user_id' => $user->id,
        'value' => 84.5,
        'measured_at' => now(),
    ]);

    $request = new Request(['type' => 'vitals']);
    $result = $this->tool->handle($request);
    $json = json_decode((string) $result, true);

    expect($json['entries'][0])->toHaveKey('weight_kg', 84.5);
});

it('formats vitals entries correctly - blood pressure', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $groupId = (string) Str::uuid();

    HealthSyncSample::factory()->bloodPressure(120)->create([
        'user_id' => $user->id,
        'measured_at' => now(),
        'group_id' => $groupId,
    ]);

    HealthSyncSample::factory()->create([
        'user_id' => $user->id,
        'type_identifier' => 'bloodPressureDiastolic',
        'value' => 80,
        'unit' => 'mmHg',
        'measured_at' => now(),
        'group_id' => $groupId,
    ]);

    $request = new Request(['type' => 'vitals']);
    $result = $this->tool->handle($request);
    $json = json_decode((string) $result, true);

    expect($json['entries'][0])->toHaveKey('blood_pressure', '120/80');
});

it('formats vitals entries correctly - a1c', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    HealthSyncSample::factory()->a1c()->create([
        'user_id' => $user->id,
        'value' => 7.5,
        'measured_at' => now(),
    ]);

    $request = new Request(['type' => 'vitals']);
    $result = $this->tool->handle($request);
    $json = json_decode((string) $result, true);

    expect($json['entries'][0])->toHaveKey('a1c_value', 7.5);
});

it('formats exercise entries correctly', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    HealthSyncSample::factory()->exercise()->create([
        'user_id' => $user->id,
        'value' => 30,
        'metadata' => ['exercise_type' => 'running'],
        'measured_at' => now(),
    ]);

    $request = new Request(['type' => 'exercise']);
    $result = $this->tool->handle($request);
    $json = json_decode((string) $result, true);

    expect($json['entries'][0])->toHaveKey('exercise', 'running')
        ->and($json['entries'][0])->toHaveKey('exercise_duration_minutes', 30);
});

it('formats medication entries correctly - insulin', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    HealthSyncSample::factory()->insulin()->create([
        'user_id' => $user->id,
        'value' => 10.5,
        'metadata' => ['insulin_type' => 'bolus'],
        'measured_at' => now(),
    ]);

    $request = new Request(['type' => 'medication']);
    $result = $this->tool->handle($request);
    $json = json_decode((string) $result, true);

    expect($json['entries'][0])->toHaveKey('insulin_units', 10.5)
        ->and($json['entries'][0])->toHaveKey('insulin_type', 'bolus');
});

it('formats medication entries correctly - oral medication', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    HealthSyncSample::factory()->medication()->create([
        'user_id' => $user->id,
        'metadata' => [
            'medication_name' => 'Metformin',
            'medication_dosage' => '500mg',
        ],
        'measured_at' => now(),
    ]);

    $request = new Request(['type' => 'medication']);
    $result = $this->tool->handle($request);
    $json = json_decode((string) $result, true);

    expect($json['entries'][0])->toHaveKey('medication', 'Metformin 500mg');
});

it('formats notes correctly', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    HealthSyncSample::factory()->carbohydrates()->create([
        'user_id' => $user->id,
        'notes' => 'Felt dizzy after lunch',
        'measured_at' => now(),
    ]);

    $request = new Request;
    $result = $this->tool->handle($request);
    $json = json_decode((string) $result, true);

    expect($json['entries'][0])->toHaveKey('notes', 'Felt dizzy after lunch');
});

it('returns food entries with all macros', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $groupId = (string) Str::uuid();

    HealthSyncSample::factory()->carbohydrates()->create([
        'user_id' => $user->id,
        'value' => 50.0,
        'notes' => 'tsuivan',
        'measured_at' => now(),
        'entry_source' => HealthEntrySource::Telegram,
        'group_id' => $groupId,
    ]);

    HealthSyncSample::factory()->create([
        'user_id' => $user->id,
        'type_identifier' => 'protein',
        'value' => 20.0,
        'unit' => 'g',
        'measured_at' => now(),
        'entry_source' => HealthEntrySource::Telegram,
        'group_id' => $groupId,
    ]);

    HealthSyncSample::factory()->create([
        'user_id' => $user->id,
        'type_identifier' => 'totalFat',
        'value' => 15.0,
        'unit' => 'g',
        'measured_at' => now(),
        'entry_source' => HealthEntrySource::Telegram,
        'group_id' => $groupId,
    ]);

    HealthSyncSample::factory()->create([
        'user_id' => $user->id,
        'type_identifier' => 'dietaryEnergy',
        'value' => 400,
        'unit' => 'kcal',
        'measured_at' => now(),
        'entry_source' => HealthEntrySource::Telegram,
        'group_id' => $groupId,
    ]);

    $request = new Request(['type' => 'food']);
    $result = $this->tool->handle($request);
    $json = json_decode((string) $result, true);

    expect($json['entries'][0])->toHaveKey('carbs_grams', 50.0)
        ->and($json['entries'][0])->toHaveKey('protein_grams', 20.0)
        ->and($json['entries'][0])->toHaveKey('fat_grams', 15.0)
        ->and($json['entries'][0])->toHaveKey('calories', 400)
        ->and($json['entries'][0])->toHaveKey('food_name', 'tsuivan');
});

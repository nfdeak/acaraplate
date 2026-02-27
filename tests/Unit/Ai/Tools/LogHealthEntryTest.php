<?php

declare(strict_types=1);

use App\Ai\Tools\LogHealthEntry;
use App\Enums\HealthEntrySource;
use App\Models\HealthEntry;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Ai\Tools\Request;
use Tests\Helpers\TestJsonSchema;

it('has correct name and description', function (): void {
    $tool = new LogHealthEntry;

    expect($tool->name())->toBe('log_health_entry');
    expect($tool->description())->toContain('Log a health entry');
});

it('has valid schema with enum constraints', function (): void {
    $tool = new LogHealthEntry;
    $schema = $tool->schema(new TestJsonSchema);

    expect($schema)
        ->toHaveKey('log_type')
        ->toHaveKey('glucose_value')
        ->toHaveKey('carbs_grams')
        ->toHaveKey('notes')
        ->toHaveKey('weight')
        ->toHaveKey('bp_systolic')
        ->toHaveKey('bp_diastolic')
        ->toHaveKey('exercise_type')
        ->toHaveKey('exercise_duration_minutes')
        ->toHaveKey('measured_at');
});

it('returns error if user is not authenticated', function (): void {
    Auth::shouldReceive('user')->andReturn(null);

    $tool = new LogHealthEntry;
    $request = new Request(['log_type' => 'glucose', 'glucose_value' => 120]);

    $result = json_decode($tool->handle($request), true);

    expect($result)->toHaveKey('error', 'User not authenticated');
});

it('logs a glucose entry successfully', function (): void {
    $user = User::factory()->create();
    Auth::login($user);

    $tool = new LogHealthEntry;
    $request = new Request([
        'log_type' => 'glucose',
        'glucose_value' => 140,
        'glucose_reading_type' => 'fasting',
    ]);

    $result = json_decode($tool->handle($request), true);

    expect($result)
        ->toHaveKey('success', true)
        ->toHaveKey('entry_id')
        ->toHaveKey('message');

    $entry = HealthEntry::find($result['entry_id']);
    expect($entry)
        ->glucose_value->toBe(140.0)
        ->source->toBe(HealthEntrySource::Chat);
});

it('logs a food entry with notes', function (): void {
    $user = User::factory()->create();
    Auth::login($user);

    $tool = new LogHealthEntry;
    $request = new Request([
        'log_type' => 'food',
        'carbs_grams' => 45,
        'notes' => 'tsuivan',
    ]);

    $result = json_decode($tool->handle($request), true);

    expect($result)->toHaveKey('success', true);

    $entry = HealthEntry::find($result['entry_id']);
    expect($entry)
        ->carbs_grams->toBe(45)
        ->notes->toBe('tsuivan')
        ->source->toBe(HealthEntrySource::Chat);
});

it('logs a vitals entry with weight', function (): void {
    $user = User::factory()->create();
    Auth::login($user);

    $tool = new LogHealthEntry;
    $request = new Request([
        'log_type' => 'vitals',
        'weight' => 81.65,
    ]);

    $result = json_decode($tool->handle($request), true);

    expect($result)->toHaveKey('success', true);

    $entry = HealthEntry::find($result['entry_id']);
    expect($entry)
        ->weight->toBe(81.65)
        ->source->toBe(HealthEntrySource::Chat);
});

it('logs an exercise entry', function (): void {
    $user = User::factory()->create();
    Auth::login($user);

    $tool = new LogHealthEntry;
    $request = new Request([
        'log_type' => 'exercise',
        'exercise_type' => 'walking',
        'exercise_duration_minutes' => 30,
    ]);

    $result = json_decode($tool->handle($request), true);

    expect($result)->toHaveKey('success', true);

    $entry = HealthEntry::find($result['entry_id']);
    expect($entry)
        ->exercise_type->toBe('walking')
        ->exercise_duration_minutes->toBe(30)
        ->source->toBe(HealthEntrySource::Chat);
});

it('logs a medication entry', function (): void {
    $user = User::factory()->create();
    Auth::login($user);

    $tool = new LogHealthEntry;
    $request = new Request([
        'log_type' => 'meds',
        'medication_name' => 'metformin',
        'medication_dosage' => '500mg',
    ]);

    $result = json_decode($tool->handle($request), true);

    expect($result)->toHaveKey('success', true);

    $entry = HealthEntry::find($result['entry_id']);
    expect($entry)
        ->medication_name->toBe('metformin')
        ->medication_dosage->toBe('500mg')
        ->source->toBe(HealthEntrySource::Chat);
});

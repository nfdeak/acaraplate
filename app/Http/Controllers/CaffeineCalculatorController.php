<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CalculateCaffeineSafeDose;
use App\Actions\CalculateCaffeineSleepCutoff;
use App\Actions\LogToolEvent;
use App\Actions\SearchCaffeineDrinks;
use App\Models\CaffeineDrink;
use App\Utilities\WeightConverter;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final readonly class CaffeineCalculatorController
{
    public const string TOOL_NAME = 'caffeine-calculator';

    public const float MIN_WEIGHT_KG = 30.0;

    public const float MAX_WEIGHT_KG = 250.0;

    /**
     * @var array<int, string>
     */
    public const array CLIENT_EVENTS = [
        'unit_toggled',
        'sensitivity_changed',
        'weight_entered',
        'drink_picked',
        'search_result_selected',
        'sleep_disclosure_opened',
    ];

    public function index(Request $request): Response
    {
        $this->logger()->handle(self::TOOL_NAME, 'page_view');

        $unit = $request->query('unit') === 'lb' ? 'lb' : 'kg';

        return Inertia::render('caffeine-calculator', [
            'unit' => $unit,
            'hasDrinks' => CaffeineDrink::query()->exists(),
            'minWeightKg' => self::MIN_WEIGHT_KG,
            'maxWeightKg' => self::MAX_WEIGHT_KG,
            'registerUrl' => route('register').'?source=caffeine_calculator',
            'isGuest' => ! $request->user(),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = (string) $request->query('q', '');
        $normalized = mb_strtolower(mb_trim($query));

        if ($normalized === '') {
            return response()->json(['results' => []]);
        }

        $this->logger()->handle(self::TOOL_NAME, 'search_submitted', [
            'query_length' => mb_strlen($normalized),
        ]);

        $results = app(SearchCaffeineDrinks::class)->handle($normalized);

        $this->logger()->handle(self::TOOL_NAME, 'search_results_returned', [
            'result_count' => $results->count(),
            'query_length' => mb_strlen($normalized),
        ]);

        if ($results->isEmpty()) {
            $this->logger()->handle(self::TOOL_NAME, 'search_no_results', [
                'query_length' => mb_strlen($normalized),
            ]);
        }

        return response()->json(['results' => $results->values()->all()]);
    }

    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'weight' => ['required', 'numeric', 'gt:0'],
            'weight_unit' => ['required', 'in:kg,lb'],
            'sensitivity' => ['required', 'integer', 'between:1,5'],
            'drink_id' => ['required', 'integer', 'exists:caffeine_drinks,id'],
        ], [
            'weight.required' => 'Enter your weight to calculate.',
            'weight.numeric' => 'Weight must be a number.',
            'weight.gt' => 'Weight must be greater than 0.',
        ]);

        $drink = CaffeineDrink::query()->find($validated['drink_id']);

        if ($drink === null) {
            return response()->json(['lacks_caffeine_estimate' => true]);
        }

        if ($drink->caffeine_mg === null || (float) $drink->caffeine_mg <= 0.0) {
            return response()->json(['lacks_caffeine_estimate' => true]);
        }

        $weightKg = WeightConverter::convertToKg((float) $validated['weight'], $validated['weight_unit']);
        $clampedKg = max(self::MIN_WEIGHT_KG, min(self::MAX_WEIGHT_KG, $weightKg));

        $sensitivityStep = (int) $validated['sensitivity'];

        $result = app(CalculateCaffeineSafeDose::class)->handle(
            weightKg: $clampedKg,
            sensitivityStep: $sensitivityStep - 1,
            perCupMg: (float) $drink->caffeine_mg,
        );

        $this->logger()->handle(self::TOOL_NAME, 'calculation_completed', [
            'sensitivity_step' => $sensitivityStep,
            'safe_mg_bucket' => $this->logger()->bucketSafeMg($result->safeMg),
            'cups_bucket' => $this->logger()->bucketCups($result->cups),
        ]);

        return response()->json([
            'lacks_caffeine_estimate' => false,
            'safe_mg' => $result->safeMg,
            'safe_cups' => $result->cups,
            'per_cup_mg' => (float) $drink->caffeine_mg,
            'drink' => [
                'id' => $drink->id,
                'name' => $drink->name,
                'slug' => $drink->slug,
                'caffeine_mg' => (float) $drink->caffeine_mg,
                'source' => $drink->source,
                'license_url' => $drink->license_url,
                'attribution' => $drink->attribution,
            ],
        ]);
    }

    public function sleepCutoff(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bedtime' => ['required', 'string', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'per_cup_mg' => ['required', 'numeric', 'gt:0'],
            'safe_cups' => ['required', 'integer', 'gt:0'],
        ]);

        $bedtimeToday = CarbonImmutable::now()->setTimeFromTimeString($validated['bedtime']);

        if ($bedtimeToday->isPast()) {
            return response()->json(['state' => 'past']);
        }

        $cutoff = app(CalculateCaffeineSleepCutoff::class)->handle(
            $bedtimeToday,
            (float) $validated['per_cup_mg'],
            (int) $validated['safe_cups'],
        );

        if (! $cutoff instanceof CarbonImmutable) {
            return response()->json(['state' => 'unavailable']);
        }

        return response()->json([
            'state' => 'cutoff',
            'time' => $cutoff->format('g:i A'),
        ]);
    }

    public function event(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event' => ['required', 'string', 'in:'.implode(',', self::CLIENT_EVENTS)],
            'properties' => ['sometimes', 'array'],
        ]);

        $properties = $validated['properties'] ?? [];

        $properties = match ($validated['event']) {
            'unit_toggled' => $this->sanitizeUnitToggled($properties),
            'sensitivity_changed' => $this->sanitizeSensitivityChanged($properties),
            'weight_entered' => $this->sanitizeWeightEntered($properties),
            'drink_picked' => $this->sanitizeDrinkPicked($properties),
            'search_result_selected' => $this->sanitizeSearchResultSelected($properties),
            'sleep_disclosure_opened' => [],
            default => [],
        };

        if ($properties === null) {
            return response()->json(['logged' => false]);
        }

        $this->logger()->handle(self::TOOL_NAME, $validated['event'], $properties);

        return response()->json(['logged' => true]);
    }

    public function signupCta(): RedirectResponse
    {
        $this->logger()->handle(self::TOOL_NAME, 'signup_cta_clicked');

        return redirect()->away(route('register').'?source=caffeine_calculator');
    }

    private function logger(): LogToolEvent
    {
        return app(LogToolEvent::class);
    }

    /**
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>|null
     */
    private function sanitizeUnitToggled(array $properties): ?array
    {
        $unit = $properties['unit'] ?? null;

        if (! in_array($unit, ['kg', 'lb'], true)) {
            return null;
        }

        return ['unit' => $unit];
    }

    /**
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>|null
     */
    private function sanitizeSensitivityChanged(array $properties): ?array
    {
        $step = $properties['sensitivity_step'] ?? null;

        if (! is_numeric($step)) {
            return null;
        }

        $step = (int) $step;

        if ($step < 1 || $step > 5) {
            return null;
        }

        return ['sensitivity_step' => $step];
    }

    /**
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>|null
     */
    private function sanitizeWeightEntered(array $properties): ?array
    {
        $weight = $properties['weight'] ?? null;
        $unit = $properties['unit'] ?? 'kg';

        if (! is_numeric($weight) || (float) $weight <= 0.0) {
            return null;
        }

        if (! in_array($unit, ['kg', 'lb'], true)) {
            return null;
        }

        $weightKg = WeightConverter::convertToKg((float) $weight, $unit);

        return ['weight_kg' => $weightKg];
    }

    /**
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>|null
     */
    private function sanitizeDrinkPicked(array $properties): ?array
    {
        $id = $properties['drink_id'] ?? null;

        if (! is_numeric($id)) {
            return null;
        }

        $drink = CaffeineDrink::query()->find((int) $id);

        if ($drink === null) {
            return null;
        }

        return ['drink' => $drink->slug];
    }

    /**
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>|null
     */
    private function sanitizeSearchResultSelected(array $properties): ?array
    {
        $id = $properties['drink_id'] ?? null;

        if (! is_numeric($id)) {
            return null;
        }

        $drink = CaffeineDrink::query()->find((int) $id);

        if ($drink === null) {
            return null;
        }

        $rank = $properties['rank'] ?? null;
        $queryLength = $properties['query_length'] ?? 0;

        return [
            'drink' => $drink->slug,
            'rank' => is_numeric($rank) ? (int) $rank : null,
            'query_length' => is_numeric($queryLength) ? (int) $queryLength : 0,
        ];
    }
}

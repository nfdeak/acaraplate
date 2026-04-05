<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Enums\HealthSyncType;
use App\Models\HealthSyncSample;
use App\Models\User;
use App\Services\HealthEntryAssembler;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

final readonly class GetHealthEntries implements Tool
{
    public function name(): string
    {
        return 'get_health_entries';
    }

    public function description(): string
    {
        return "Retrieve the current user's logged health entries including food intake (carbs), glucose readings, weight, blood pressure, insulin, medications, and exercise. Use this when the user asks about their food log, health history, what they ate, or wants to compare their actual intake against a meal plan.";
    }

    public function handle(Request $request): string
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return (string) json_encode([
                'error' => 'User not authenticated',
                'entries' => null,
            ]);
        }

        /** @var string|null $date */
        $date = $request['date'] ?? null;
        $daysInput = $request['days'] ?? 1;
        $days = max(1, is_numeric($daysInput) ? (int) $daysInput : 1);
        /** @var string $type */
        $type = $request['type'] ?? 'all';

        $endDate = $date ? Date::parse($date)->endOfDay() : Date::now()->endOfDay();
        $startDate = $endDate->copy()->subDays($days - 1)->startOfDay();

        $query = HealthSyncSample::query()
            ->where('user_id', $user->id)
            ->whereBetween('measured_at', [$startDate, $endDate])
            ->latest('measured_at');

        $query = $this->applyTypeFilter($query, $type);

        $samples = $query->limit(200)->get();

        $assembler = resolve(HealthEntryAssembler::class);
        $assembled = $assembler->assemble($samples);

        /** @var Collection<int, array{id: int, group_id: string|null, glucose_value: float|null, glucose_reading_type: string|null, measured_at: string, notes: string|null, insulin_units: float|null, insulin_type: string|null, medication_name: string|null, medication_dosage: string|null, weight: float|null, blood_pressure_systolic: int|null, blood_pressure_diastolic: int|null, a1c_value: float|null, carbs_grams: float|null, protein_grams: float|null, fat_grams: float|null, calories: int|null, exercise_type: string|null, exercise_duration_minutes: int|null, source: string|null, created_at: string}> $assembled */
        $formatted = $assembled->take(100)->map(function (array $entry): array {
            $data = [
                'measured_at' => $entry['measured_at'],
                'source' => $entry['source'],
            ];

            if ($entry['glucose_value'] !== null) {
                $data['glucose_value'] = $entry['glucose_value'];
                $data['glucose_reading_type'] = $entry['glucose_reading_type'];
            }

            if ($entry['carbs_grams'] !== null) {
                $data['carbs_grams'] = $entry['carbs_grams'];
                if ($entry['protein_grams'] !== null) {
                    $data['protein_grams'] = $entry['protein_grams'];
                }

                if ($entry['fat_grams'] !== null) {
                    $data['fat_grams'] = $entry['fat_grams'];
                }

                if ($entry['calories'] !== null) {
                    $data['calories'] = $entry['calories'];
                }

                if ($entry['notes'] !== null) {
                    $data['food_name'] = $entry['notes'];
                }
            }

            if ($entry['weight'] !== null) {
                $data['weight_kg'] = $entry['weight'];
            }

            if ($entry['blood_pressure_systolic'] !== null) {
                $data['blood_pressure'] = ($entry['blood_pressure_systolic']).'/'.((int) $entry['blood_pressure_diastolic']);
            }

            if ($entry['insulin_units'] !== null) {
                $data['insulin_units'] = $entry['insulin_units'];
                $data['insulin_type'] = $entry['insulin_type'];
            }

            if ($entry['medication_name'] !== null) {
                $data['medication'] = ($entry['medication_name']).' '.($entry['medication_dosage']);
            }

            if ($entry['exercise_type'] !== null) {
                $data['exercise'] = $entry['exercise_type'];
                $data['exercise_duration_minutes'] = $entry['exercise_duration_minutes'];
            }

            if ($entry['a1c_value'] !== null) {
                $data['a1c_value'] = $entry['a1c_value'];
            }

            if ($entry['notes'] !== null) {
                $data['notes'] = $entry['notes'];
            }

            return $data;
        })->all();

        return (string) json_encode([
            'success' => true,
            'date_range' => [
                'from' => $startDate->toDateString(),
                'to' => $endDate->toDateString(),
            ],
            'total_entries' => count($formatted),
            'entries' => array_values($formatted),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'date' => $schema->string()->required()->nullable()
                ->description('The date to retrieve entries for in ISO format (e.g., "2026-02-27"). Defaults to today.'),
            'days' => $schema->integer()->required()->nullable()
                ->description('Number of days to look back from the specified date. Defaults to 1 (just that day).'),
            'type' => $schema->string()->required()->nullable()
                ->enum(['all', 'food', 'glucose', 'vitals', 'exercise', 'medication'])
                ->description('Filter entries by type. "all" returns everything, "food" returns carb entries, "glucose" returns glucose readings, "vitals" returns weight and blood pressure, "exercise" returns exercise logs, "medication" returns insulin and medication entries.'),
        ];
    }

    /**
     * @param  Builder<HealthSyncSample>  $query
     * @return Builder<HealthSyncSample>
     */
    private function applyTypeFilter(Builder $query, string $type): Builder
    {
        return match ($type) {
            'food' => $query->whereIn('type_identifier', [
                HealthSyncType::Carbohydrates->value,
                HealthSyncType::Protein->value,
                HealthSyncType::TotalFat->value,
                HealthSyncType::DietaryEnergy->value,
            ]),
            'glucose' => $query->where('type_identifier', HealthSyncType::BloodGlucose->value),
            'vitals' => $query->whereIn('type_identifier', [
                HealthSyncType::Weight->value,
                HealthSyncType::BloodPressureSystolic->value,
                HealthSyncType::BloodPressureDiastolic->value,
                HealthSyncType::A1c->value,
            ]),
            'exercise' => $query->whereIn('type_identifier', [
                HealthSyncType::ExerciseMinutes->value,
                HealthSyncType::Workouts->value,
            ]),
            'medication' => $query->whereIn('type_identifier', [
                HealthSyncType::Insulin->value,
                HealthSyncType::Medication->value,
            ]),
            default => $query->whereIn('type_identifier', HealthSyncType::entryTypeValues()),
        };
    }
}

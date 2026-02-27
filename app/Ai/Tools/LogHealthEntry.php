<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Actions\RecordHealthEntryAction;
use App\DataObjects\HealthLogData;
use App\Enums\GlucoseReadingType;
use App\Enums\HealthEntrySource;
use App\Enums\HealthEntryType;
use App\Enums\InsulinType;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

final readonly class LogHealthEntry implements Tool
{
    public function name(): string
    {
        return 'log_health_entry';
    }

    public function description(): string
    {
        return 'Log a health entry for the current user. Use this when the user reports a health measurement like food intake, glucose reading, weight, blood pressure, insulin dose, medication, or exercise. Extract the relevant data from the user message and call this tool to save it.';
    }

    public function handle(Request $request): string
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return (string) json_encode([
                'error' => 'User not authenticated',
            ]);
        }

        /** @var array<string, mixed> $requestData */
        $requestData = $request->toArray();
        $healthData = HealthLogData::fromParsedArray(array_merge(
            $requestData,
            ['is_health_data' => true],
        ));

        $recordData = array_merge(
            ['user_id' => $user->id, 'measured_at' => $healthData->measuredAt ?? now()],
            $healthData->toRecordArray(),
        );

        $action = resolve(RecordHealthEntryAction::class);
        $entry = $action->handle($recordData, HealthEntrySource::Chat);

        return (string) json_encode([
            'success' => true,
            'message' => $healthData->formatForDisplay(),
            'entry_id' => $entry->id,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'log_type' => $schema->string()->required()
                ->enum(HealthEntryType::class)
                ->description('Type of health entry to log.'),
            'glucose_value' => $schema->number()
                ->description('Glucose reading value in mg/dL. Convert mmol/L to mg/dL (× 18.018).'),
            'glucose_reading_type' => $schema->string()
                ->enum(GlucoseReadingType::class)
                ->description('When the glucose was measured.'),
            'carbs_grams' => $schema->number()
                ->description('Carbohydrate intake in grams (can be decimal like 12.5).'),
            'protein_grams' => $schema->number()
                ->description('Protein intake in grams (can be decimal like 12.5).'),
            'fat_grams' => $schema->number()
                ->description('Fat intake in grams (can be decimal like 12.5).'),
            'calories' => $schema->integer()
                ->description('Total calories.'),
            'notes' => $schema->string()
                ->description('Food name or additional notes.'),
            'insulin_units' => $schema->number()
                ->description('Insulin dose in units.'),
            'insulin_type' => $schema->string()
                ->enum(InsulinType::class)
                ->description('Type of insulin administered.'),
            'medication_name' => $schema->string()
                ->description('Name of the medication taken.'),
            'medication_dosage' => $schema->string()
                ->description('Dosage of the medication (e.g., "500mg").'),
            'weight' => $schema->number()
                ->description('Body weight in kg. Convert lbs to kg (÷ 2.205).'),
            'bp_systolic' => $schema->integer()
                ->description('Systolic blood pressure reading.'),
            'bp_diastolic' => $schema->integer()
                ->description('Diastolic blood pressure reading.'),
            'exercise_type' => $schema->string()
                ->description('Type of exercise performed (e.g., "walking", "running").'),
            'exercise_duration_minutes' => $schema->integer()
                ->description('Duration of exercise in minutes.'),
            'measured_at' => $schema->string()
                ->description('When the measurement was taken in ISO 8601 format. Only set if the user specifies a time. Leave empty for current time.'),
        ];
    }
}

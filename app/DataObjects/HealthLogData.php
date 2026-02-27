<?php

declare(strict_types=1);

namespace App\DataObjects;

use App\Enums\GlucoseReadingType;
use App\Enums\GlucoseUnit;
use App\Enums\HealthEntryType;
use App\Enums\InsulinType;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;
use Spatie\LaravelData\Data;

final class HealthLogData extends Data
{
    public function __construct(
        public bool $isHealthData,
        public HealthEntryType $logType,
        public ?float $glucoseValue = null,
        public ?GlucoseReadingType $glucoseReadingType = null,
        public ?GlucoseUnit $glucoseUnit = null,
        public ?int $carbsGrams = null,
        public ?float $insulinUnits = null,
        public ?InsulinType $insulinType = null,
        public ?string $medicationName = null,
        public ?string $medicationDosage = null,
        public ?float $weight = null,
        public ?int $bpSystolic = null,
        public ?int $bpDiastolic = null,
        public ?string $exerciseType = null,
        public ?int $exerciseDurationMinutes = null,
        public ?CarbonInterface $measuredAt = null,
        public ?string $notes = null,
    ) {}

    /**
     * Create a HealthLogData from a structured output array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromParsedArray(array $data): self
    {
        return new self(
            isHealthData: (bool) ($data['is_health_data'] ?? false),
            logType: HealthEntryType::tryFrom($data['log_type'] ?? '') ?? HealthEntryType::Glucose,
            glucoseValue: self::toFloat($data['glucose_value'] ?? null),
            glucoseReadingType: GlucoseReadingType::tryFrom($data['glucose_reading_type'] ?? ''),
            glucoseUnit: GlucoseUnit::tryFrom($data['glucose_unit'] ?? ''),
            carbsGrams: self::toInt($data['carbs_grams'] ?? null),
            insulinUnits: self::toFloat($data['insulin_units'] ?? null),
            insulinType: InsulinType::tryFrom($data['insulin_type'] ?? ''),
            medicationName: self::toNullableString($data['medication_name'] ?? null),
            medicationDosage: self::toNullableString($data['medication_dosage'] ?? null),
            weight: self::toFloat($data['weight'] ?? null),
            bpSystolic: self::toInt($data['bp_systolic'] ?? null),
            bpDiastolic: self::toInt($data['bp_diastolic'] ?? null),
            exerciseType: self::toNullableString($data['exercise_type'] ?? null),
            exerciseDurationMinutes: self::toInt($data['exercise_duration_minutes'] ?? null),
            measuredAt: self::toDateTime($data['measured_at'] ?? null),
            notes: self::toNullableString($data['notes'] ?? null),
        );
    }

    /**
     * Format the health log data for display confirmation.
     */
    public function formatForDisplay(): string
    {
        return match ($this->logType) {
            HealthEntryType::Glucose => $this->formatGlucoseLog(),
            HealthEntryType::Food => $this->formatFoodLog(),
            HealthEntryType::Insulin => $this->formatInsulinLog(),
            HealthEntryType::Meds => $this->formatMedsLog(),
            HealthEntryType::Vitals => $this->formatVitalsLog(),
            HealthEntryType::Exercise => $this->formatExerciseLog(),
        };
    }

    /**
     * Convert the health log data to a record array for database storage.
     *
     * @return array<string, mixed>
     */
    public function toRecordArray(): array
    {
        return match ($this->logType) {
            HealthEntryType::Glucose => $this->toGlucoseRecordArray(),
            HealthEntryType::Food => $this->toFoodRecordArray(),
            HealthEntryType::Insulin => $this->toInsulinRecordArray(),
            HealthEntryType::Meds => $this->toMedsRecordArray(),
            HealthEntryType::Vitals => $this->toVitalsRecordArray(),
            HealthEntryType::Exercise => $this->toExerciseRecordArray(),
        };
    }

    private static function toFloat(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private static function toInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private static function toNullableString(mixed $value): ?string
    {
        if (in_array($value, [null, 'null', ''], true)) {
            return null;
        }

        return is_string($value) ? $value : (is_scalar($value) ? (string) $value : null);
    }

    private static function toDateTime(mixed $value): ?CarbonInterface
    {
        $string = self::toNullableString($value);

        return $string !== null ? Date::parse($string) : null;
    }

    private function formatGlucoseLog(): string
    {
        $unit = $this->glucoseUnit ?? GlucoseUnit::MgDl;
        $readingType = $this->glucoseReadingType ?? GlucoseReadingType::Random;

        return sprintf('Glucose %s %s (%s)', $this->glucoseValue, $unit->value, $readingType->label());
    }

    private function formatFoodLog(): string
    {
        $foodName = $this->notes ?? 'Food';

        return sprintf('%s - %sg carbs', $foodName, $this->carbsGrams);
    }

    private function formatInsulinLog(): string
    {
        $typeLabel = $this->insulinType?->label() ?? 'Bolus';

        return sprintf('Insulin %s units (%s)', $this->insulinUnits, $typeLabel);
    }

    private function formatMedsLog(): string
    {
        $dosage = $this->medicationDosage ?? '';

        return 'Medication - '.$this->medicationName.($dosage !== '' && $dosage !== '0' ? ' '.$dosage : '');
    }

    private function formatVitalsLog(): string
    {
        if ($this->weight !== null) {
            return sprintf('Weight %s kg', $this->weight);
        }

        if ($this->bpSystolic !== null && $this->bpDiastolic !== null) {
            return sprintf('Blood Pressure %d/%d', $this->bpSystolic, $this->bpDiastolic);
        }

        return 'Vitals';
    }

    private function formatExerciseLog(): string
    {
        $type = $this->exerciseType ?? 'exercise';

        return sprintf('Exercise - %s min %s', $this->exerciseDurationMinutes, $type);
    }

    /**
     * @return array<string, mixed>
     */
    private function toGlucoseRecordArray(): array
    {
        return [
            'glucose_value' => $this->glucoseValue,
            'glucose_reading_type' => $this->glucoseReadingType?->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function toFoodRecordArray(): array
    {
        return [
            'carbs_grams' => $this->carbsGrams,
            'notes' => $this->notes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function toInsulinRecordArray(): array
    {
        return [
            'insulin_units' => $this->insulinUnits,
            'insulin_type' => $this->insulinType?->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function toMedsRecordArray(): array
    {
        return [
            'medication_name' => $this->medicationName,
            'medication_dosage' => $this->medicationDosage,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function toVitalsRecordArray(): array
    {
        return [
            'weight' => $this->weight,
            'blood_pressure_systolic' => $this->bpSystolic,
            'blood_pressure_diastolic' => $this->bpDiastolic,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function toExerciseRecordArray(): array
    {
        return [
            'exercise_type' => $this->exerciseType,
            'exercise_duration_minutes' => $this->exerciseDurationMinutes,
        ];
    }
}

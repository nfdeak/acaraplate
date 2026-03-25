<?php

declare(strict_types=1);

namespace App\Enums;

enum HealthSyncType: string
{
    case BloodGlucose = 'bloodGlucose';
    case BloodPressureSystolic = 'bloodPressureSystolic';
    case BloodPressureDiastolic = 'bloodPressureDiastolic';
    case Weight = 'weight';
    case Carbohydrates = 'carbohydrates';
    case Protein = 'protein';
    case TotalFat = 'totalFat';
    case DietaryEnergy = 'dietaryEnergy';
    case ExerciseMinutes = 'exerciseMinutes';
    case Workouts = 'workouts';

    case BloodPressure = 'bloodPressure';

    public function healthEntryColumn(): ?string
    {
        return match ($this) {
            self::Weight => 'weight',
            self::Carbohydrates => 'carbs_grams',
            self::Protein => 'protein_grams',
            self::TotalFat => 'fat_grams',
            self::DietaryEnergy => 'calories',
            self::ExerciseMinutes, self::Workouts => 'exercise_duration_minutes',
            default => null,
        };
    }

    public function isMappedToHealthEntry(): bool
    {
        return $this !== self::BloodPressure;
    }
}

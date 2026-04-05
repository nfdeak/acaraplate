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
    case A1c = 'a1c';
    case Insulin = 'insulin';
    case Medication = 'medication';

    case BiologicalSex = 'biologicalSex';
    case DateOfBirth = 'dateOfBirth';
    case BloodType = 'bloodType';

    case BloodPressure = 'bloodPressure';

    /**
     * @return array<int, string>
     */
    public static function entryTypeValues(): array
    {
        return [
            self::BloodGlucose->value,
            self::Weight->value,
            self::BloodPressureSystolic->value,
            self::BloodPressureDiastolic->value,
            self::Carbohydrates->value,
            self::Protein->value,
            self::TotalFat->value,
            self::DietaryEnergy->value,
            self::ExerciseMinutes->value,
            self::Workouts->value,
            self::A1c->value,
            self::Insulin->value,
            self::Medication->value,
        ];
    }

    public function isUserCharacteristic(): bool
    {
        return in_array($this, [
            self::BiologicalSex,
            self::DateOfBirth,
            self::BloodType,
        ], true);
    }

    public function unit(): string
    {
        return match ($this) {
            self::BloodGlucose => 'mg/dL',
            self::BloodPressureSystolic, self::BloodPressureDiastolic, self::BloodPressure => 'mmHg',
            self::Weight => 'kg',
            self::Carbohydrates, self::Protein, self::TotalFat => 'g',
            self::DietaryEnergy => 'kcal',
            self::ExerciseMinutes, self::Workouts => 'min',
            self::A1c => '%',
            self::Insulin => 'IU',
            self::Medication => 'dose',
            self::BiologicalSex, self::DateOfBirth, self::BloodType => '',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::BloodGlucose => 'Blood Glucose',
            self::BloodPressureSystolic => 'Blood Pressure (Systolic)',
            self::BloodPressureDiastolic => 'Blood Pressure (Diastolic)',
            self::BloodPressure => 'Blood Pressure',
            self::Weight => 'Weight',
            self::Carbohydrates => 'Carbohydrates',
            self::Protein => 'Protein',
            self::TotalFat => 'Total Fat',
            self::DietaryEnergy => 'Calories',
            self::ExerciseMinutes => 'Exercise',
            self::Workouts => 'Workouts',
            self::A1c => 'A1C',
            self::Insulin => 'Insulin',
            self::Medication => 'Medication',
            self::BiologicalSex => 'Biological Sex',
            self::DateOfBirth => 'Date of Birth',
            self::BloodType => 'Blood Type',
        };
    }
}

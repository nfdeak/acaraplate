<?php

declare(strict_types=1);

use App\Enums\HealthSyncType;

it('returns correct units for all types', function (HealthSyncType $type, string $expectedUnit): void {
    expect($type->unit())->toBe($expectedUnit);
})->with([
    [HealthSyncType::BloodGlucose, 'mg/dL'],
    [HealthSyncType::BloodPressureSystolic, 'mmHg'],
    [HealthSyncType::BloodPressureDiastolic, 'mmHg'],
    [HealthSyncType::BloodPressure, 'mmHg'],
    [HealthSyncType::Weight, 'kg'],
    [HealthSyncType::Carbohydrates, 'g'],
    [HealthSyncType::Protein, 'g'],
    [HealthSyncType::TotalFat, 'g'],
    [HealthSyncType::DietaryEnergy, 'kcal'],
    [HealthSyncType::ExerciseMinutes, 'min'],
    [HealthSyncType::Workouts, 'min'],
    [HealthSyncType::A1c, '%'],
    [HealthSyncType::Insulin, 'IU'],
    [HealthSyncType::Medication, 'dose'],
    [HealthSyncType::BiologicalSex, ''],
    [HealthSyncType::DateOfBirth, ''],
    [HealthSyncType::BloodType, ''],
]);

it('returns correct labels for all types', function (HealthSyncType $type, string $expectedLabel): void {
    expect($type->label())->toBe($expectedLabel);
})->with([
    [HealthSyncType::BloodGlucose, 'Blood Glucose'],
    [HealthSyncType::BloodPressureSystolic, 'Blood Pressure (Systolic)'],
    [HealthSyncType::BloodPressureDiastolic, 'Blood Pressure (Diastolic)'],
    [HealthSyncType::BloodPressure, 'Blood Pressure'],
    [HealthSyncType::Weight, 'Weight'],
    [HealthSyncType::Carbohydrates, 'Carbohydrates'],
    [HealthSyncType::Protein, 'Protein'],
    [HealthSyncType::TotalFat, 'Total Fat'],
    [HealthSyncType::DietaryEnergy, 'Calories'],
    [HealthSyncType::ExerciseMinutes, 'Exercise'],
    [HealthSyncType::Workouts, 'Workouts'],
    [HealthSyncType::A1c, 'A1C'],
    [HealthSyncType::Insulin, 'Insulin'],
    [HealthSyncType::Medication, 'Medication'],
    [HealthSyncType::BiologicalSex, 'Biological Sex'],
    [HealthSyncType::DateOfBirth, 'Date of Birth'],
    [HealthSyncType::BloodType, 'Blood Type'],
]);

it('returns entry type values with all health entry types', function (): void {
    $values = HealthSyncType::entryTypeValues();

    expect($values)->toHaveCount(13)
        ->toContain(HealthSyncType::BloodGlucose->value)
        ->toContain(HealthSyncType::Medication->value)
        ->not->toContain(HealthSyncType::BiologicalSex->value)
        ->not->toContain(HealthSyncType::DateOfBirth->value)
        ->not->toContain(HealthSyncType::BloodType->value);
});

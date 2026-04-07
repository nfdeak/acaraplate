<?php

declare(strict_types=1);

use App\DataObjects\MobileSync\BloodGlucoseMetadata;

it('normalizes null metadata to default glucose reading type', function (): void {
    expect(BloodGlucoseMetadata::normalize(null))->toBe([
        'glucose_reading_type' => 'random',
    ]);
});

it('normalizes empty array to default glucose reading type', function (): void {
    expect(BloodGlucoseMetadata::normalize([]))->toBe([
        'glucose_reading_type' => 'random',
    ]);
});

it('preserves extra keys from raw metadata', function (): void {
    $result = BloodGlucoseMetadata::normalize(['someKey' => 'someValue']);

    expect($result)
        ->toHaveKey('glucose_reading_type', 'random')
        ->toHaveKey('someKey', 'someValue');
});

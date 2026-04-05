<?php

declare(strict_types=1);

use App\Enums\HealthEntrySource;
use App\Models\HealthSyncSample;
use App\Models\MobileSyncDevice;
use App\Models\User;

it('belongs to a user', function (): void {
    $user = User::factory()->create();
    $sample = HealthSyncSample::factory()->for($user)->create();

    expect($sample->user)
        ->toBeInstanceOf(User::class)
        ->id->toBe($user->id);
});

it('filters by entry source scope', function (): void {
    $user = User::factory()->create();
    HealthSyncSample::factory()->for($user)->fromWeb()->create();
    HealthSyncSample::factory()->for($user)->fromMobileSync()->create();

    $webSamples = HealthSyncSample::query()->forEntrySource(HealthEntrySource::Web)->get();
    $mobileSamples = HealthSyncSample::query()->forEntrySource(HealthEntrySource::MobileSync)->get();

    expect($webSamples)->toHaveCount(1)
        ->and($mobileSamples)->toHaveCount(1);
});

it('belongs to a mobile sync device', function (): void {
    $user = User::factory()->create();
    $device = MobileSyncDevice::factory()->for($user)->paired()->create();
    $sample = HealthSyncSample::factory()->for($user)->create([
        'mobile_sync_device_id' => $device->id,
    ]);

    expect($sample->mobileSyncDevice)
        ->toBeInstanceOf(MobileSyncDevice::class)
        ->id->toBe($device->id);
});

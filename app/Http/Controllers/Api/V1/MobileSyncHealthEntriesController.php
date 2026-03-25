<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\SyncMobileHealthEntriesAction;
use App\Http\Requests\Api\V1\StoreMobileSyncHealthEntriesRequest;
use App\Models\MobileSyncDevice;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final readonly class MobileSyncHealthEntriesController
{
    public function __construct(
        private SyncMobileHealthEntriesAction $syncHealthEntriesAction,
    ) {}

    public function __invoke(StoreMobileSyncHealthEntriesRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var string $deviceIdentifier */
        $deviceIdentifier = $request->validated('device_identifier');

        $device = MobileSyncDevice::query()
            ->where('user_id', $user->id)
            ->where('device_identifier', $deviceIdentifier)
            ->where('is_active', true)
            ->firstOrFail();

        /** @var array<int, array{type: string, value: float|int|string, unit: string, date: string, source?: string|null}> $entries */
        $entries = $request->validated('entries');

        $result = $this->syncHealthEntriesAction->handle(
            user: $user,
            device: $device,
            entries: $entries,
        );

        return response()->json([
            'message' => 'Synced successfully.',
            'health_entries_created' => $result['health_entries_created'],
            'health_entries_updated' => $result['health_entries_updated'],
            'samples_created' => $result['samples_created'],
            'samples_updated' => $result['samples_updated'],
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Memory;

use App\Contracts\Ai\Memory\DispatchesMemoryExtraction;
use App\Jobs\Memory\ExtractUserMemoriesJob;
use App\Models\MemoryExtractionCheckpoint;
use App\Utilities\ConfigHelper;

final readonly class MemoryExtractionDispatcher implements DispatchesMemoryExtraction
{
    public function __construct(private MemoryExtractor $memoryExtractor) {}

    public function dispatchIfEligible(int $userId): void
    {
        $checkpoint = MemoryExtractionCheckpoint::query()->where('user_id', $userId)->first();
        $cooldownMinutes = ConfigHelper::int('memory.extraction.cooldown_minutes', 5);

        if ($checkpoint?->last_extracted_at?->isAfter(now()->subMinutes($cooldownMinutes))) {
            return;
        }

        if (! $this->memoryExtractor->shouldExtract($userId)) {
            return;
        }

        MemoryExtractionCheckpoint::query()->updateOrCreate(
            ['user_id' => $userId],
            ['last_extracted_at' => now()],
        );

        dispatch(new ExtractUserMemoriesJob($userId));
    }
}

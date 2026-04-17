<?php

declare(strict_types=1);

namespace App\Services\Memory;

use App\Contracts\Memory\DispatchesMemoryExtraction;

final readonly class NullMemoryExtractionDispatcher implements DispatchesMemoryExtraction
{
    public function dispatchIfEligible(int $userId): void {}
}

<?php

declare(strict_types=1);

namespace App\Contracts\Ai\Memory;

interface DispatchesMemoryExtraction
{
    public function dispatchIfEligible(int $userId): void;
}

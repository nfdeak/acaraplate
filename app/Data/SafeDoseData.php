<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class SafeDoseData extends Data
{
    public function __construct(
        public float $safeMg,
        public int $cups,
    ) {}
}

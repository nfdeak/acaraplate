<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\SafeDoseData;
use InvalidArgumentException;

final readonly class CalculateCaffeineSafeDose
{
    public const float BASE_MG_PER_KG = 5.7;

    /**
     * @var array<int, float>
     */
    public const array SENSITIVITY_MULTIPLIERS = [0.7, 0.85, 1.0, 1.15, 1.3];

    public function handle(float $weightKg, int $sensitivityStep, float $perCupMg): SafeDoseData
    {
        if ($weightKg <= 0.0) {
            throw new InvalidArgumentException('weightKg must be greater than zero.');
        }

        if ($perCupMg <= 0.0) {
            throw new InvalidArgumentException('perCupMg must be greater than zero.');
        }

        if (! array_key_exists($sensitivityStep, self::SENSITIVITY_MULTIPLIERS)) {
            throw new InvalidArgumentException('sensitivityStep is out of range.');
        }

        $multiplier = self::SENSITIVITY_MULTIPLIERS[$sensitivityStep];
        $safeMg = $weightKg * self::BASE_MG_PER_KG * $multiplier;
        $cups = (int) floor($safeMg / $perCupMg);

        return new SafeDoseData(
            safeMg: $safeMg,
            cups: $cups,
        );
    }
}

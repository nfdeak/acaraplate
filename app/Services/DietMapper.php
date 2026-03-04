<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AnimalProductChoice;
use App\Enums\DietType;
use App\Enums\GoalChoice;
use App\Enums\IntensityChoice;

final class DietMapper
{
    public static function map(
        GoalChoice $goalChoice,
        AnimalProductChoice $animalProductChoice,
        IntensityChoice $intensityChoice
    ): DietType {
        return match (true) {
            $goalChoice === GoalChoice::Spikes && $animalProductChoice === AnimalProductChoice::Omnivore && $intensityChoice === IntensityChoice::Balanced => DietType::Mediterranean,
            $goalChoice === GoalChoice::Spikes && $animalProductChoice === AnimalProductChoice::Omnivore && $intensityChoice === IntensityChoice::Aggressive => DietType::LowCarb,
            $goalChoice === GoalChoice::Spikes && $animalProductChoice === AnimalProductChoice::Pescatarian => DietType::Mediterranean,
            $goalChoice === GoalChoice::Spikes && $animalProductChoice === AnimalProductChoice::Vegan => DietType::Vegetarian,
            $goalChoice === GoalChoice::WeightLoss && $animalProductChoice === AnimalProductChoice::Omnivore && $intensityChoice === IntensityChoice::Aggressive => DietType::Keto,
            $goalChoice === GoalChoice::WeightLoss && $animalProductChoice === AnimalProductChoice::Omnivore && $intensityChoice === IntensityChoice::Balanced => DietType::Paleo,
            $goalChoice === GoalChoice::WeightLoss && $animalProductChoice === AnimalProductChoice::Vegan => DietType::Vegetarian,
            $goalChoice === GoalChoice::HeartHealth && $intensityChoice === IntensityChoice::Balanced => DietType::Mediterranean,
            $goalChoice === GoalChoice::BuildMuscle && $animalProductChoice === AnimalProductChoice::Omnivore => DietType::Balanced,
            $goalChoice === GoalChoice::BuildMuscle && $animalProductChoice === AnimalProductChoice::Pescatarian => DietType::Mediterranean,
            $goalChoice === GoalChoice::HealthyEating => DietType::Balanced,
            default => DietType::Balanced,
        };
    }

    public static function getActivityMultiplier(GoalChoice $goalChoice, IntensityChoice $intensityChoice): float
    {
        return match ($goalChoice) {
            GoalChoice::Spikes => match ($intensityChoice) {
                IntensityChoice::Balanced => 1.3,
                IntensityChoice::Aggressive => 1.55,
            },
            GoalChoice::WeightLoss => match ($intensityChoice) {
                IntensityChoice::Balanced => 1.375,
                IntensityChoice::Aggressive => 1.55,
            },
            GoalChoice::HeartHealth, GoalChoice::HealthyEating => 1.3,
            GoalChoice::BuildMuscle => 1.55,
        };
    }
}

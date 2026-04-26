<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CaffeineDrink;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CaffeineDrink>
 */
final class CaffeineDrinkFactory extends Factory
{
    private const array CATEGORIES = [
        'Coffee',
        'Tea',
        'Energy Drink',
        'Soda',
        'Other',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(asText: true).' '.fake()->randomElement(['Brew', 'Cold Brew', 'Latte', 'Espresso', 'Energy', 'Cola']);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 1_000_000),
            'category' => fake()->randomElement(self::CATEGORIES),
            'volume_oz' => fake()->randomFloat(2, 1, 32),
            'caffeine_mg' => fake()->randomFloat(2, 5, 400),
            'source' => fake()->company(),
            'license_url' => fake()->url(),
            'attribution' => fake()->name(),
            'verified_at' => fake()->dateTimeBetween('-1 year'),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'verified_at' => null,
        ]);
    }
}

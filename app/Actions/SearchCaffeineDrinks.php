<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\CaffeineDrink;
use Illuminate\Support\Collection;

final readonly class SearchCaffeineDrinks
{
    private const float MIN_SIMILARITY = 0.5;

    private const int LIMIT = 10;

    /**
     * @return Collection<int, array{id: int, name: string, category: ?string, caffeine_mg: float, rank: int}>
     */
    public function handle(string $query): Collection
    {
        $normalized = mb_strtolower(mb_trim($query));

        if ($normalized === '') {
            return collect();
        }

        return CaffeineDrink::query()
            ->whereNotNull('embedding')
            ->whereVectorSimilarTo('embedding', $normalized, minSimilarity: self::MIN_SIMILARITY)
            ->limit(self::LIMIT)
            ->get(['id', 'name', 'category', 'caffeine_mg'])
            ->values()
            ->map(fn (CaffeineDrink $drink, int $index): array => [
                'id' => $drink->id,
                'name' => $drink->name,
                'category' => $drink->category,
                'caffeine_mg' => (float) $drink->caffeine_mg,
                'rank' => $index,
            ]);
    }
}

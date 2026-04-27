<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\CaffeineDrinkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $slug
 * @property-read string|null $category
 * @property-read array<int, string>|null $aliases
 * @property-read string|null $search_text
 * @property-read array<int, float>|null $embedding
 * @property-read string|null $volume_oz
 * @property-read string $caffeine_mg
 * @property-read string|null $source
 * @property-read string|null $license_url
 * @property-read string|null $attribution
 * @property-read CarbonImmutable|null $verified_at
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class CaffeineDrink extends Model
{
    /** @use HasFactory<CaffeineDrinkFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'slug' => 'string',
            'category' => 'string',
            'aliases' => 'array',
            'search_text' => 'string',
            'embedding' => 'array',
            'volume_oz' => 'decimal:2',
            'caffeine_mg' => 'decimal:2',
            'source' => 'string',
            'license_url' => 'string',
            'attribution' => 'string',
            'verified_at' => 'immutable_datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}

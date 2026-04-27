<?php

declare(strict_types=1);

use App\Actions\SearchCaffeineDrinks;
use App\Models\CaffeineDrink;
use Illuminate\Support\Facades\DB;

it('returns an empty collection for an empty query', function (): void {
    $results = app(SearchCaffeineDrinks::class)->handle('');

    expect($results)->toBeEmpty();
});

it('returns an empty collection for a whitespace-only query', function (): void {
    $results = app(SearchCaffeineDrinks::class)->handle('   ');

    expect($results)->toBeEmpty();
});

it('does not throw when the database has no drinks', function (): void {
    if (DB::connection()->getDriverName() !== 'pgsql') {
        $this->markTestSkipped('Vector similarity queries require PostgreSQL with pgvector.');
    }

    $results = app(SearchCaffeineDrinks::class)->handle('coffee');

    expect($results)->toBeEmpty();
});

it('skips drinks that do not yet have an embedding', function (): void {
    if (DB::connection()->getDriverName() !== 'pgsql') {
        $this->markTestSkipped('Vector similarity queries require PostgreSQL with pgvector.');
    }

    CaffeineDrink::factory()->create([
        'name' => 'Americano',
        'slug' => 'americano',
        'embedding' => null,
    ]);

    $results = app(SearchCaffeineDrinks::class)->handle('americano');

    expect($results)->toBeEmpty();
});

<?php

declare(strict_types=1);

use App\Actions\SearchCaffeineDrinks;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Laravel\Ai\Embeddings;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(LazilyRefreshDatabase::class)
    ->beforeEach(function (): void {
        Http::preventStrayRequests();
        Sleep::fake();
        Embeddings::fake();

        $this->app->bind(SearchCaffeineDrinks::class, fn () => new class
        {
            public function handle(string $query): Collection
            {
                return collect();
            }
        });

        $this->freezeTime();
    })
    ->in('Browser', 'Feature', 'Unit');

expect()->extend('toBeOne', fn () => $this->toBe(1));

function something(): void {}

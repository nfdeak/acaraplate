<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\CaffeineDrink;
use Illuminate\Console\Command;
use Laravel\Ai\Embeddings;
use Throwable;

final class CaffeineEmbedDrinks extends Command
{
    protected $signature = 'caffeine:embed-drinks {--regenerate : Regenerate embeddings for all drinks}';

    protected $description = 'Generate or regenerate embeddings for caffeine drinks';

    public function handle(): int
    {
        $regenerate = $this->option('regenerate');

        $query = CaffeineDrink::query()
            ->when(! $regenerate, fn ($q) => $q->whereNull('embedding'));

        $count = $query->count();

        if ($count === 0) {
            $this->info('No drinks need embedding generation.');

            return self::SUCCESS;
        }

        $this->info("Generating embeddings for {$count} drink(s)...");

        $bar = $this->output->createProgressBar($count);

        foreach ($query->cursor() as $drink) {
            $this->generateForDrink($drink);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done.');

        return self::SUCCESS;
    }

    private function generateForDrink(CaffeineDrink $drink): void
    {
        if ($drink->search_text === null || $drink->search_text === '') {
            return;
        }

        try {
            $response = Embeddings::for([$drink->search_text])->generate();

            $drink->updateQuietly([
                'embedding' => $response->embeddings[0],
            ]);
        } catch (Throwable $throwable) {
            $this->error("Failed to generate embedding for {$drink->slug}: {$throwable->getMessage()}");
        }
    }
}

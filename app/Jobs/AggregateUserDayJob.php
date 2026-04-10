<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\AggregateHealthDailySamplesAction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/** @codeCoverageIgnore */
final class AggregateUserDayJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public int $maxExceptions = 3;

    public function __construct(
        private readonly int $userId,
        private readonly ?string $overrideDate = null,
    ) {
        $this->queue = 'default';
    }

    public function uniqueId(): string
    {
        return $this->userId.':'.($this->overrideDate ?? 'yesterday');
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [30, 60, 120];
    }

    public function handle(AggregateHealthDailySamplesAction $action): void
    {
        $user = User::query()->find($this->userId);

        if ($user === null) {
            return;
        }

        $localDate = $this->resolveLocalDate($user);

        $action->handle($user, $localDate);
    }

    private function resolveLocalDate(User $user): CarbonImmutable
    {
        if ($this->overrideDate !== null) {
            return CarbonImmutable::parse($this->overrideDate);
        }

        return CarbonImmutable::now($user->resolveTimezone())->subDay()->startOfDay();
    }
}

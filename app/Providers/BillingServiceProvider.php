<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Billing\GatesPremiumFeatures;
use App\Services\Billing\SubscriptionGate;
use Illuminate\Support\ServiceProvider;

final class BillingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GatesPremiumFeatures::class, SubscriptionGate::class);
    }
}

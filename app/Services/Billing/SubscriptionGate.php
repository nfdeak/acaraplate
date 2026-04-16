<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Contracts\Billing\GatesPremiumFeatures;
use App\Models\User;

final readonly class SubscriptionGate implements GatesPremiumFeatures
{
    public function isPremium(User $user, ?bool $storedIsVerified = null): bool
    {
        if (collect(config()->array('sponsors.admin_emails'))->contains($user->email)) {
            return true;
        }

        if ($user->hasActiveSubscription()) {
            return true;
        }

        if ($storedIsVerified === null) {
            return false;
        }

        return $storedIsVerified;
    }
}

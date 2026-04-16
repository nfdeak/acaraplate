<?php

declare(strict_types=1);

namespace App\Services\Null;

use App\Contracts\Billing\GatesPremiumFeatures;
use App\Models\User;

final readonly class NullPremiumGate implements GatesPremiumFeatures
{
    public function isPremium(User $user, ?bool $storedIsVerified = null): bool
    {
        return true;
    }
}

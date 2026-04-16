<?php

declare(strict_types=1);

namespace App\Contracts\Billing;

use App\Models\User;

interface GatesPremiumFeatures
{
    public function isPremium(User $user, ?bool $storedIsVerified = null): bool;
}

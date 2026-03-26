<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

final readonly class HealthSyncPageController
{
    public function setup(): View
    {
        return view('health-sync.setup');
    }

    public function index(): View
    {
        return view('health-sync.index');
    }
}

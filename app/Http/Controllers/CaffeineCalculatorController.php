<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\BuildCaffeineGuidanceSpec;
use App\Actions\ResolveCaffeineLimit;
use App\Ai\Agents\CaffeineGuidanceAgent;
use App\Http\Requests\CaffeineAssessmentRequest;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class CaffeineCalculatorController
{
    public function create(): Response
    {
        return Inertia::render('caffeine-calculator', [
            'seo' => [
                'appName' => config('app.name'),
                'appUrl' => url('/'),
                'canonicalUrl' => route('caffeine-calculator'),
            ],
        ]);
    }

    public function plan(CaffeineAssessmentRequest $request): JsonResponse
    {
        $context = $request->validated('context');
        $limit = resolve(ResolveCaffeineLimit::class)->handle(
            heightCm: (int) $request->validated('height_cm'),
            sensitivity: (string) $request->validated('sensitivity'),
            context: is_string($context) ? $context : null,
        );
        $guidance = resolve(CaffeineGuidanceAgent::class)->assess(
            limit: $limit,
            context: is_string($context) ? $context : null,
        );
        $spec = resolve(BuildCaffeineGuidanceSpec::class)->handle($guidance);

        return response()->json([
            'summary' => $guidance->summary,
            'limit' => $limit->toArray(),
            'spec' => $spec,
        ]);
    }
}

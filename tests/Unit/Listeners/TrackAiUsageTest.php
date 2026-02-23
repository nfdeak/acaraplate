<?php

declare(strict_types=1);

use App\DataObjects\AiUsageData;
use App\Services\AiUsageService;

it('calculates cost correctly via service', function (): void {
    $service = new AiUsageService;

    $usage = [
        'prompt_tokens' => 1000,
        'completion_tokens' => 500,
        'cache_read_input_tokens' => 0,
        'reasoning_tokens' => 0,
    ];

    $cost = $service->calculateCost('gemini-3-flash-preview', $usage);

    expect($cost)->toBeGreaterThan(0);
});

it('calculates total tokens in AiUsageData', function (): void {
    $data = new AiUsageData(
        userId: 1,
        agent: 'TestAgent',
        model: 'gemini-3-flash-preview',
        provider: 'gemini',
        promptTokens: 1000,
        completionTokens: 500,
        cacheReadInputTokens: 200,
        reasoningTokens: 100,
        cost: 0.01,
    );

    expect($data->totalTokens())->toBe(1800);
});

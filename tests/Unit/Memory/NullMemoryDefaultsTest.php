<?php

declare(strict_types=1);

use App\Contracts\Memory\DispatchesMemoryExtraction;
use App\Contracts\Memory\ManagesMemoryContext;
use App\Services\Memory\NullMemoryExtractionDispatcher;
use App\Services\Memory\NullMemoryPromptContext;

covers(NullMemoryExtractionDispatcher::class, NullMemoryPromptContext::class);

it('renders an empty memory context by default', function (): void {
    $context = new NullMemoryPromptContext;

    expect($context->render(
        userId: 1,
        userMessage: 'What should I eat?',
        conversationTail: [['role' => 'user', 'content' => 'I like tofu.']],
    ))->toBe('');
});

it('ignores memory extraction dispatches by default', function (): void {
    $dispatcher = new NullMemoryExtractionDispatcher;

    $dispatcher->dispatchIfEligible(1);

    expect($dispatcher)->toBeInstanceOf(DispatchesMemoryExtraction::class);
});

it('resolves public memory contracts from the container', function (): void {
    expect(app(ManagesMemoryContext::class))->toBeInstanceOf(ManagesMemoryContext::class)
        ->and(app(DispatchesMemoryExtraction::class))->toBeInstanceOf(DispatchesMemoryExtraction::class);
});

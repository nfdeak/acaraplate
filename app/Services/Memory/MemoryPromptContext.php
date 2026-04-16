<?php

declare(strict_types=1);

namespace App\Services\Memory;

use App\Ai\MemoryPrompt;
use App\Contracts\Ai\Memory\ManagesMemoryContext;

final readonly class MemoryPromptContext implements ManagesMemoryContext
{
    public function __construct(private MemoryPrompt $memoryPrompt) {}

    /**
     * @param  array<int, array{role: string, content: string}>  $conversationTail
     */
    public function render(int $userId, string $userMessage, array $conversationTail = []): string
    {
        return $this->memoryPrompt->for($userId, $userMessage, $conversationTail)->render();
    }
}

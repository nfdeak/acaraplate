<?php

declare(strict_types=1);

namespace App\Services\Memory;

use App\Contracts\Memory\ManagesMemoryContext;

final readonly class NullMemoryPromptContext implements ManagesMemoryContext
{
    /**
     * @param  array<int, array{role: string, content: string}>  $conversationTail
     */
    public function render(int $userId, string $userMessage, array $conversationTail = []): string
    {
        return '';
    }
}

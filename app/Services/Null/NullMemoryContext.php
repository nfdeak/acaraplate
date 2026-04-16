<?php

declare(strict_types=1);

namespace App\Services\Null;

use App\Contracts\Ai\Memory\ManagesMemoryContext;

final readonly class NullMemoryContext implements ManagesMemoryContext
{
    /**
     * @param  array<int, array{role: string, content: string}>  $conversationTail
     */
    public function render(int $userId, string $userMessage, array $conversationTail = []): string
    {
        return '';
    }
}

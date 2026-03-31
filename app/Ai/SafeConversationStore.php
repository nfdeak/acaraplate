<?php

declare(strict_types=1);

namespace App\Ai;

use Illuminate\Support\Collection;
use Laravel\Ai\Messages\AssistantMessage;
use Laravel\Ai\Messages\ToolResultMessage;
use Laravel\Ai\Storage\DatabaseConversationStore;

final class SafeConversationStore extends DatabaseConversationStore
{
    /**
     * @return Collection<int, \Laravel\Ai\Messages\Message>
     */
    public function getLatestConversationMessages(string $conversationId, int $limit): Collection
    {
        return parent::getLatestConversationMessages($conversationId, $limit)
            ->each(function ($message): void {
                if ($message instanceof AssistantMessage) {
                    foreach ($message->toolCalls as $toolCall) {
                        $toolCall->resultId ??= 'call_'.$toolCall->id;
                    }
                }

                if ($message instanceof ToolResultMessage) {
                    foreach ($message->toolResults as $toolResult) {
                        $toolResult->resultId ??= 'call_'.$toolResult->id;
                    }
                }
            });
    }
}

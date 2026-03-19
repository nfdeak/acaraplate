<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Ai\Agents\ConversationSummarizerAgent;
use App\Models\ConversationSummary;
use Illuminate\Container\Attributes\Bind;

#[Bind(ConversationSummarizerAgent::class)]
interface SummarizesConversation
{
    public function summarize(string $conversationText, ?ConversationSummary $previousSummary): string;
}
